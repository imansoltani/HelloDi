<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Denomination;
use HelloDi\DiDistributorsBundle\Entity\ItemDesc;
use HelloDi\DiDistributorsBundle\Entity\Operator;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Form\DenominationType;
use HelloDi\DiDistributorsBundle\Form\ItemDescType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Form\ItemType;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $items = $em->createQueryBuilder()
            ->select("item",'code')
            ->from("HelloDiDiDistributorsBundle:Item","item")
            ->LeftJoin("item.Codes","code",'WITH',"code.status = 1")
            ->getQuery()
            ->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Item:index.html.twig', array(
            'items' => $items,
        ));
    }

    public function newAction(Request $request)
    {
        $item  = new Item();
        $itemdesc = new ItemDesc();
        $itemdesc->setItem($item);
        $item->addItemDesc($itemdesc);

        $form   = $this->createForm(new ItemType($this->container->getParameter('languages'),$this->container->getParameter('Currencies.TopUp')), $item, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $check = $this->checkDescription($itemdesc->getDescdesc(),$item->getItemType());
            if($check !== true)
            {
                if($check == "invalid")
                    $form->get('ItemDescs')->get(0)->get('descdesc')->addError(new FormError($this->get('translator')->trans('You_entered_an_invalid',array(),'message')));
                else
                    $form->get('ItemDescs')->get(0)->get('descdesc')->addError(new FormError($this->get('translator')->trans($check,array(),'validators')));
            }

            if ($form->isValid()) {
                $item->setItemDateInsert(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($item);
                $em->persist($itemdesc);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('item'));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Item:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$item) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:show.html.twig', array(
            'item'      => $item
        ));
    }

    public function editAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$item) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));
        }

        $editForm = $this->createForm(new ItemType($this->container->getParameter('languages'),$this->container->getParameter('Currencies.TopUp')), $item);
        if ($request->isMethod('POST'))
        {
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $em->persist($item);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->forward("HelloDiDiDistributorsBundle:Item:show", array('id'=>$id));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Item:edit.html.twig', array(
            'item'      => $item,
            'edit_form'   => $editForm->createView()
        ));
    }

    public function ItemPerDistAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$item) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));
        }

        $qb = $em->createQueryBuilder()
            ->select("prc")
            ->from("HelloDiDiDistributorsBundle:Price","prc")
            ->innerJoin("prc.Account","acc")
            ->where("acc.accType = 1")
            ->where("prc.priceStatus = 1")
            ->where("prc.Item = :itm")->setParameter("itm",$item)
            ->getQuery();
        $haspriceforprov = (count($qb->getResult())>0);

        $prices = $em->createQueryBuilder()
            ->select('DISTINCT p.priceCurrency')
            ->from("HelloDiDiDistributorsBundle:Price","p")
            ->join('p.Account','a')
            ->where('p.Item = :item')->setParameter('item',$item)
            ->andWhere('a.accType = 0')
            ->getQuery()
            ->getResult();

        $form = $this->createFormBuilder()
            ->add('checks', 'entity', array(
                    'class' => 'HelloDiDiDistributorsBundle:Account',
                    'expanded' => 'true',
                    'multiple' => 'true',
                    'query_builder' => function(EntityRepository $er) use ($item) {
                        return $er->createQueryBuilder('u')
                            ->leftJoin('u.Prices','prices','WITH','prices.Item = :item')
                            ->andWhere('u.accType = 0')
                            ->setParameter('item',$item)
                            ;
                    }
                ))
            ->add('NewPrice','number',array('required'=>true,'label' => 'NewPrice','translation_domain' => 'price'))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $actiontype = $request->get("actiontype");
                $newprice = $data['NewPrice'];
                foreach ($data['checks'] as $accountdist)
                {
                    $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountdist));
                    if($actiontype == "1")
                    {
                        if($price != null)
                        {
                            $price->setPriceStatus(0);
                            $RetAccs = $accountdist->getChildrens()->toArray();
                            if (count($RetAccs)> 0)
                            {
                                $em ->createQueryBuilder()
                                    ->update('HelloDiDiDistributorsBundle:Price','pr')
                                    ->where('pr.Account IN (:retaccs)')->setParameter('retaccs',$RetAccs)
                                    ->andWhere('pr.Item = :item')->setParameter('item',$item)
                                    ->set("pr.priceStatus",0)
                                    ->getQuery()
                                    ->execute()
                                ;
                            }
                        }
                    }
                    else
                    {
                        if($price != null)
                        {
                            if($price->getPrice() != $newprice)
                            {
                                $price->setPrice($newprice);

                                $pricehistory = new PriceHistory();
                                $pricehistory->setPrice($newprice);
                                $pricehistory->setDate(new \DateTime('now'));
                                $pricehistory->setPrices($price);
                                $em->persist($pricehistory);
                            }
                            $price->setPriceStatus(1);
                        }
                        else
                        {
                            $price = new Price();
                            $price->setPrice($newprice);
                            $price->setPriceCurrency($accountdist->getAccCurrency());
                            $price->setPriceStatus(true);
                            $price->setIsFavourite(true);
                            $price->setItem($item);
                            $price->setAccount($accountdist);
                            $em->persist($price);

                            $pricehistory = new PriceHistory();
                            $pricehistory->setPrice($newprice);
                            $pricehistory->setDate(new \DateTime('now'));
                            $pricehistory->setPrices($price);
                            $em->persist($pricehistory);
                        }
                    }
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('item_price', array('id' => $id)));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:ItemsPerDistributors.html.twig', array(
                'form' => $form->createView(),
                'itemid' => $id,
                'item'      => $item,
                'haspriceforprov' => $haspriceforprov,
                'prices' => $prices
            ));
    }
    //item desc

    public function descIndexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$item) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));
        }

        $itemdescs = $item->getItemDescs();

        return $this->render('HelloDiDiDistributorsBundle:Item:descindex.html.twig', array(
                'item'      => $item,
                'itemdescs' => $itemdescs
            ));
    }

    public function descNewAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);
        if (!$item) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));
        }
        $desc = new ItemDesc();
        $langs = $this->container->getParameter('languages');
        $qb = $em->createQueryBuilder()
            ->select('DISTINCT u.desclang')
            ->from('HelloDiDiDistributorsBundle:ItemDesc','u')
            ->where('u.Item = :item')
            ->setParameter('item',$item)
            ->getQuery();

        $tt = $qb->getResult();
        $mylang = array();
        foreach ($tt as $t)
            $mylang[] = $t["desclang"];
        $selectlangs = array_diff($langs,$mylang);
        $selectlangs = array_combine($selectlangs, $selectlangs);
        $form = $this->createForm(new ItemDescType($selectlangs),$desc);
        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            $check = $this->checkDescription($desc->getDescdesc(),$item->getItemType());
            if($check !== true)
            {
                if($check == "invalid")
                    $form->get('descdesc')->addError(new FormError($this->get('translator')->trans('You_entered_an_invalid',array(),'message')));
                else
                    $form->get('descdesc')->addError(new FormError($this->get('translator')->trans($check,array(),'validators')));
            }
            if ($form->isValid()) {
                $finddesc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$item,'desclang'=>$desc->getDesclang()));
                if($finddesc)
                    $form->get('desclang')->addError(new FormError('language is duplicate.'));
                else
                {
                    $desc->setItem($item);
                    $em->persist($desc);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                    return $this->forward('HelloDiDiDistributorsBundle:Item:descIndex', array(
                            'id' => $item->getId()
                        ));
                }
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Item:descnew.html.twig', array(
                'form' => $form->createView(),
                'item' => $item
            ));
    }

    public function descEditAction(Request $request,$descid)
    {
        $em = $this->getDoctrine()->getManager();
        $desc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->find($descid);

        if (!$desc) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Description',array(),'item')),'message'));
        }

        $desclang = $desc->getDesclang();
        $langs = $this->container->getParameter('languages');
        $langs = array_combine($langs, $langs);
        $form = $this->createForm(new ItemDescType($langs),$desc);
        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            $desc->setDesclang($desclang);
            $check = $this->checkDescription($desc->getDescdesc(),$desc->getItem()->getItemType());
            if($check !== true)
            {
                if($check == "invalid")
                    $form->get('descdesc')->addError(new FormError($this->get('translator')->trans('You_entered_an_invalid',array(),'message')));
                else
                    $form->get('descdesc')->addError(new FormError($this->get('translator')->trans($check,array(),'validators')));
            }
            if ($form->isValid()) {
                $em->persist($desc);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->forward('HelloDiDiDistributorsBundle:Item:descIndex', array(
                        'id' => $desc->getItem()->getId()
                    ));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Item:descedit.html.twig', array(
                'form' => $form->createView(),
                'item' => $desc->getItem(),
                'itemdescid' => $desc->getId()
            ));
    }

    private function checkDescription($desc,$itemType)
    {
        $tags = $itemType == "imtu" ?
            array(
                "tranid"=>'tranid_not_exist',
                "recievernumber"=>'recievernumber_not_exist',
                "printdate"=>'printdate_not_exist'
            ) :
            array(
                "pin"=>'pin_not_exist',
                "serial"=>'sn_not_exist',
                "expire"=>'expiry_not_exist',
                "duplicate"=>'duplicate_not_exist',
                "printdate"=>'printdate_not_exist'
            );

        foreach ($tags as $tag=>$message)
        {
            $find = strpos($desc,"{{".$tag."}}");
            if(!$find) return $message;
        }

        $twig = new \Twig_Environment(new \Twig_Loader_String());
        try{
            if($itemType == "imtu")
                $twig->render($desc,array(
                    "printdate"=>"2013/13/13",
                    "entityname"=>'Entity Name',
                    "operator"=>'Operator Name',
                    "entityadrs1"=>'Address Line 1',
                    "entityadrs2"=>'Address Line 2',
                    "entityadrs3"=>'Address Line 3',
                    "tranid"=>'1234',
                    "recievernumber"=>'+12345678',
                    "valuesent"=>'1 CHF',
                    "valuepaid"=>'2 USD',
                ));
            else
                $twig->render($desc,array(
                    "pin"=>1234,
                    "serial"=>4321,
                    "expire"=>"2012/12/12",
                    "printdate"=>"2013/13/13",
                    "duplicate"=>"duplicate",
                    "entityname"=>'Entity Name',
                    "operator"=>'Operator Name',
                    "entityadrs1"=>'Address Line 1',
                    "entityadrs2"=>'Address Line 2',
                    "entityadrs3"=>'Address Line 3'
                ));
        }catch (\Exception $e){
            return "invalid";
        }
        return true;
    }

    //denomination
    public function denominationIndexAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);
        if (!$item) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));
        }

        $denominations = $item->getDenominations();

        $form = $this->createFormBuilder(array("Denominations"=>$denominations),array('cascade_validation' => true))
            ->add("Denominations", 'collection', array('type'=> new DenominationType(),'allow_add'=>true,'allow_delete'=>false))
            ->getForm();

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            $currencies = array();
            $data = $form->getData();
            foreach($data["Denominations"] as $key=>$denomination)
            {
                if(in_array($denomination->getCurrency(),$currencies))
                    $form->get("Denominations")[$key]->get("denomination")->addError(new FormError("Denomination for this currency already exist."));
                else
                    $currencies[] = $denomination->getCurrency();
            }

            if($form->isValid())
            {
                foreach($data["Denominations"] as $denomination)
                {
                    $denomination->setItem($item);
                    $em->persist($denomination);
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:denominationindex.html.twig', array(
                'item'      => $item,
                'form'      =>$form->createView()
            ));
    }

    //-------
    public function PrintAction($print,$descid,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $description = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->find($descid)->getDescdesc();

        $trans = array();
        for($i = 1;$i<=2;$i++)
        {
            $tran = array(
                "pin" => '12345678'.$i,
                "serial" => '87654321'.$i,
                "expire" => "2012/12/12",
                "printdate" => "2013/13/13",
                "entityname" => 'Entity Name '.$i,
                "operator" => 'Operator Name '.$i,
                "entityadrs1" => 'Address Line 1 '.$i,
                "entityadrs2" => 'Address Line 2 '.$i,
                "entityadrs3" => 'Address Line 3 '.$i
            );
            $trans[] = $tran;
        }

        $html = $this->render('HelloDiDiDistributorsBundle:Item:Print.html.twig',array(
            'trans'=>$trans,
            'description'=>str_replace('{{duplicate}}','{{duplicate|raw}}',$description),
            'duplicate'=> false,
            'print' => $print,
            'descid' => $descid,
            'itemid' => $id
        ));

        if($print == 'web')
            return $html;
        else
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html->getContent()),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="Codes.pdf"'
                )
            );
    }

    public function CreateItemCodeAction(Request $request)
    {
        try
        {
            $countryid = $request->get('countryid');
            $itemtype = $request->get('itemtype');
            $operatorid = $request->get('operatorid');
            $itemname = $request->get('itemname');

            $em = $this->getDoctrine()->getManager();

            $countrycode = $em->getRepository('HelloDiDiDistributorsBundle:Country')->find($countryid)->getIso();
            $operatorname = $em->getRepository('HelloDiDiDistributorsBundle:Operator')->find($operatorid)->getName();
            $itemcode = $countrycode.'/'.$itemtype.'/'.$operatorname.'/'.str_replace(' ','_',$itemname);
            return new Response($itemcode);
        }
        catch(\Exception $e)
        {
            return new Response('');
        }
    }

    public function CreateTranId()
    {
        $userid = $this->getUser()->getId();
        return "HD-".sprintf("%05s",$userid).'-'.(new \DateTime())->getTimestamp();
    }
}
