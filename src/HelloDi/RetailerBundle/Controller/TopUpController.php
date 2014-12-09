<?php
namespace HelloDi\RetailerBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\CoreBundle\Entity\Operator;
use HelloDi\PricingBundle\Entity\Price;
use HelloDi\RetailerBundle\Form\BuyImtuType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TopUpController extends Controller
{
    public function IMTUAction(Request $request)
    {
        $form = $this->createForm(new BuyImtuType())
            ->add('buy','submit', array(
                    'label'=>'Buy','translation_domain'=>'common',
                    'attr'=>array('first-button', 'last-button')
                ))
            ;

        $B2B_ID = null;

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid()) {
                $data = $form->getData();

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                $item = $em->createQueryBuilder()
                    ->select('item')
                    ->from('HelloDiCoreBundle:Item', 'item')
                    ->where('item.id = :item_id')->setParameter('item_id', $data['denomination'])
                    ->andWhere('item.country = :country')->setParameter('country', $data['countryIso'])
                    ->innerJoin('item.operator', 'operator')
                    ->andWhere('operator.id = :operator_id')->setParameter('operator_id', $data['operator'])
                    ->innerJoin('item.prices', 'priceRet')
                    ->andWhere('priceRet.account = :accountRet')->setParameter('accountRet', $this->getUser()->getAccount())
                    ->getQuery()->getOneOrNullResult();

                if(!$item)
                    $this->get('session')->getFlashBag()->add('error', "Error in form fields.");
                else {
                    $result = $this->get('topup')->buyImtu(
                        $this->getUser(),
                        $item,
                        $data['receiverMobileNumber'],
                        $data['senderMobileNumber'],
                        $data['senderEmail']
                    );

                    switch($result[0]) {
                        case 1: $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                            $B2B_ID = $result[1];
                            break;

                        case 0:
                        case -1:
                        case -2: $this->get('session')->getFlashBag()->add('error', $result[2]);
                            $B2B_ID = null;
                            break;

                        case -3: $this->get('session')->getFlashBag()->add('error', $result[2]);
                            return $this->redirect($this->generateUrl('hello_di_retailer_topup_retry', array('id'=>$result[1])));
                    }
                }
            }
        }

        return $this->render('HelloDiRetailerBundle:topup:imtu.html.twig',array(
                'form' => $form->createView(),
                'B2B_ID' => $B2B_ID
            ));
    }

    public function readNumberAction(Request $request)
    {
        $number = $request->get("receiver");
        if(!$number || !is_numeric($number)) return new Response("  <option value=''>Invalid Mobile Number</option>");
        $number = ltrim($number,"0+-");
        if(strlen($number)<6) return new Response("  <option value=''>Invalid Mobile Number</option>");
        $old_operator = $request->get("old_operator", 0);

        $mobile_country_roles = $this->container->getParameter('mobile_country_roles');

        $role = null;
        foreach($mobile_country_roles as $mobile_country_role)
        {
            $number_country_code = (int)substr($number,0,strlen($mobile_country_role['country_code']));
            if($number_country_code == $mobile_country_role['country_code'])
                if($mobile_country_role['mobile_min_length'] <= strlen($number) && strlen($number) <= $mobile_country_role['mobile_max_length'])
                    $role = $mobile_country_role;
        }

        if (!$role) return new Response("  <option value=''>Not found Operator</option>");

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Operator[] $operators */
        $operators = $em->createQueryBuilder()
            ->select('operator')
            ->from("HelloDiCoreBundle:Operator",'operator')
            ->innerJoin('operator.item','item')
            ->innerJoin('item.prices','price')
            ->where('price.account = :account')->setParameter("account", $this->getUser()->getAccount())
            ->andWhere('item.country = :country')->setParameter('country',$role['country_iso'])
            ->getQuery()->getResult();

        $result = "";
        foreach($operators as $operator)
            $result .= "<option value='".$operator->getId()."' ".($old_operator==$operator->getId()?"selected":"").">".$operator->getName()."</option>";

//        $file = file("../app/Resources/phones_rules/phones_rules.csv");
//
//        $array = array();
//
//        foreach($file as $line)
//        {
//            $row = str_getcsv($line,",");
//            $length = strlen($row[2]);
//            if(!isset($array[$length])) $array[$length] = array();
//            $array[$length] []= array(
//                "country_iso"=>$row[1],
//                "number_min_length"=>(int)$row[3],
//                "number_max_length"=>(int)$row[4],
//                "operator_code"=>(int)$row[2],
//                "operator_name"=>$row[5],
//            );
//        }
//
//        $dumper = new Dumper();
//
//        $yaml = $dumper->dump($array,2);
//
//        file_put_contents('phones_rules.yml', $yaml);

        $countries = $this->container->getParameter('countries');

        return  new Response($result?$role['country_iso'].$countries[$role['country_iso']].$result:"  <option value=''>Not found Operator</option>");
    }

    public function getPricesAction(Request $request)
    {
        $operatorID = $request->get("operatorID");
        $country = $request->get("country");
        $old_denomination = $request->get("old_denomination");

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //provider
        $b2bAccountName = $this->container->getParameter('B2BServer')['AccountName'];
        $providerAccount = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('name'=>$b2bAccountName));
        if (!$providerAccount)
            return array(0, null, 'Unable to find Provider Account.');

        $currency = $this->get('account_type_finder')->getCurrency($providerAccount);

        /** @var Price[] $prices */
        $prices = $em->createQueryBuilder()
            ->select('price', 'item')
            ->from("HelloDiPricingBundle:Price","price")
            ->where('price.account = :account')->setParameter("account", $providerAccount)
            ->innerJoin("price.item","item")
            ->innerJoin("item.operator","operator")
            ->andWhere("operator.id = :operator_id")->setParameter("operator_id",$operatorID)
            ->andWhere('item.country = :country')->setParameter('country',$country)
            ->getQuery()->getResult();

        $result = "";
        foreach($prices as $price)
            $result .= "<option value='".$price->getItem()->getId()."' ".($old_denomination==$price->getItem()->getId()?"selected":"").">"
                .$price->getDenomination()." ".$currency
                ." (".$price->getItem()->getFaceValue()." ".$price->getItem()->getCurrency().")"
                ."</option>";

        return  new Response($result?:"<option value=''>Not found Denomination.</option>");
    }

    public function retryAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $topup = $em->createQueryBuilder()
            ->select('topup', 'item', 'operator')
            ->from('HelloDiAggregatorBundle:TopUp', 'topup')
            ->where('topup.id = :id')->setParameter('id', $id)
            ->innerJoin('topup.item', 'item')
            ->innerJoin('item.operator', 'operator')
            ->getQuery()->getOneOrNullResult();
        if(!$topup)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'TopUp'),'message'));

        $countries = $this->container->getParameter('countries');

        //provider
        $b2bAccountName = $this->container->getParameter('B2BServer')['AccountName'];
        $providerAccount = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('name'=>$b2bAccountName));
        if (!$providerAccount)
            return array(0, null, 'Unable to find Provider Account.');

        $currency = $this->get('account_type_finder')->getCurrency($providerAccount);

        $priceProvider = $em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array(
                'account'=>$providerAccount,
                'item'=>$topup->getItem()
            ));

        $form = $this->createForm(new BuyImtuType(), array(
                'receiverMobileNumber' => $topup->getMobileNumber(),
                'country' => $countries[$topup->getItem()->getCountry()],
                'operator' => $topup->getItem()->getOperator()->getName(),
                'denomination' => $priceProvider->getDenomination()." ".$currency
                    ." (".$topup->getItem()->getFaceValue()." ".$topup->getItem()->getCurrency().")",
                'senderMobileNumber' => $topup->getSenderMobileNumber()?:"--",
                'senderEmail' => $topup->getSenderEmail()?:"--",
            ), array(
                'disabled' => true
            ));

        return $this->render('HelloDiRetailerBundle:topup:retry.html.twig',array(
                'form' => $form->createView(),
                'topup_id' => $topup->getId()
            ));
    }
}
