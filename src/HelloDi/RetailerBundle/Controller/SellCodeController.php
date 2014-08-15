<?php
namespace HelloDi\RetailerBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AggregatorBundle\Entity\Code;
use HelloDi\AggregatorBundle\Form\SellCodeType;
use HelloDi\CoreBundle\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SellCodeController extends Controller
{
    public function DMTUAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $all_languages = $this->container->getParameter('languages');

        $form = $this->createForm(new SellCodeType($all_languages), null, array(
                'attr' => array('target'=>'_blank', 'class'=>'modal-body container_pop'),
            ))
            ->add('buy','submit', array(
                    'label'=>'Buy','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'PopupTopClose(event)','last-button')
                ))
        ;

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            $item = $em->getRepository('HelloDiCoreBundle:Item')->find($form->get('item_id')->getData());
            $description = $em->getRepository('HelloDiCoreBundle:ItemDesc')->findOneBy(array(
                    'item' => $item,
                    'language' => $form->get('language')->getData()
                ));
            if(!$description) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('No description found for this item and selected language.', array(), 'message'));
                return $this->forward('HelloDiRetailerBundle:SellCode:errorPrint');
            }
            else {
                try {
                    $pin = $this->get('aggregator')->sellCodes($this->getUser(), $item, $form->get('count')->getData());
                    return $this->redirect($this->generateUrl('hello_di_retailer_sell_code_print', array('pin_id'=> $pin->getId())));
                } catch (\Exception $e){
                    $this->get('session')->getFlashBag()->add('error', $e->getMessage());
                    return $this->forward('HelloDiRetailerBundle:SellCode:errorPrint');
                }
            }
        }

        $prices = $em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price', 'price')
            ->innerJoin('price.item', 'item')
            ->where('item.type = :type')->setParameter('type', Item::DMTU)
            ->andWhere('price.account = :account')->setParameter('account', $this->getUser()->getAccount())
            ->getQuery()->getResult();

        return $this->render('HelloDiRetailerBundle:sell_code:dmtu.html.twig', array(
                'prices' => $prices,
                'form' => $form->createView()
            ));
    }

    public function PrintCodeAction($pin_id)
    {
        $print = $this->getRequest()->get('print', 'web');
        $lang = $this->getRequest()->get('lang', $this->getUser()->getLanguage());

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $pin = $em->getRepository('HelloDiAggregatorBundle:Pin')->findOneBy(array('id'=>$pin_id, 'user' => $this->getUser()));
        if(!$pin) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
            return $this->forward('HelloDiRetailerBundle:SellCode:errorPrint');
        }

        $duplicate = $pin->getPrinted();
        $pin->setPrinted(true);
        $em->flush();

        $codes = $pin->getCodes();
        /** @var Code $first_code */
        $first_code = $codes[0];

        $description = $em->getRepository('HelloDiCoreBundle:ItemDesc')->findOneBy(array('language'=>$lang, 'item'=>$first_code->getItem()));

        $html = $this->render('HelloDiRetailerBundle:sell_code:codePrint.html.twig', array(
                'pin' => $pin,
                'description' => $description ? str_replace('{{duplicate}}','{{duplicate|raw}}', $description->getDescription()) : null,
                'duplicate' => $duplicate,
                'print' => $print
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

    public function errorPrintAction()
    {
        return $this->render("HelloDiRetailerBundle:sell_code:errorPrint.html.twig");
    }
}
