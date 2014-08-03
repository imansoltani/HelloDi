<?php

namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\ItemDesc;
use HelloDi\MasterBundle\Form\ItemType;
use HelloDi\PricingBundle\Entity\Price;
use HelloDi\MasterBundle\Form\ItemDescType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\CoreBundle\Entity\Item;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $items = $em->createQueryBuilder()
            ->select("item",'code')
            ->from("HelloDiCoreBundle:Item","item")
            ->LeftJoin("item.codes","code",'WITH',"code.status = 1")
            ->getQuery()
            ->getResult();

        return $this->render('HelloDiMasterBundle:item:index.html.twig', array(
            'items' => $items,
        ));
    }

    public function newAction(Request $request)
    {
        $item = new Item();
        $item->setDateInsert(new \DateTime('now'));
        $itemDesc = new ItemDesc();
        $itemDesc->setItem($item);
        $item->addDescription($itemDesc);

        $form = $this->createForm(new ItemType($this->container->getParameter('languages'),$this->container->getParameter('Currencies.TopUp')), $item, array(
                'cascade_validation' => true,
            ))
            ->add('descriptions', 'collection', array('type' => new ItemDescType($this->container->getParameter('languages'))))
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_item_index').'")','last-button')
                ))
        ;

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($item);
                $em->persist($itemDesc);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_item_index'));
            }
        }

        return $this->render('HelloDiMasterBundle:item:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function generateItemCodeAction(Request $request)
    {
        try
        {
            $country_id = $request->get('country_id');
            $item_type = $request->get('item_type');
            $operator_id = $request->get('operator_id');
            $item_name = $request->get('item_name');

            $em = $this->getDoctrine()->getManager();

            $country_code = $em->getRepository('HelloDiCoreBundle:Country')->find($country_id)->getIso();
            $operator_name = $em->getRepository('HelloDiCoreBundle:Operator')->find($operator_id)->getName();
            $item_code = $country_code.'/'.$item_type.'/'.$operator_name.'/'.str_replace(' ','_',$item_name);
            return new Response($item_code);
        }
        catch(\Exception $e)
        {
            return new Response('');
        }
    }

    public function detailsAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiCoreBundle:Item')->find($id);
        if (!$item)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));

        return $this->render('HelloDiMasterBundle:item:details.html.twig', array(
            'item' => $item
        ));
    }

    public function editAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiCoreBundle:Item')->find($id);
        if (!$item)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));

        $form = $this->createForm(new ItemType($this->container->getParameter('languages'),$this->container->getParameter('Currencies.TopUp')), $item)
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_item_details',array('id'=>$id)).'")','last-button')
                ))
        ;

        if ($request->isMethod('post'))
        {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_item_details',array('id' => $id)));
            }
        }

        return $this->render('HelloDiMasterBundle:item:edit.html.twig', array(
            'item'      => $item,
            'form'   => $form->createView()
        ));
    }

    public function itemPerDistAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $item = $em->getRepository('HelloDiCoreBundle:Item')->find($id);
        if (!$item)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));

        $qb = $em->createQueryBuilder()
            ->select("prc")
            ->from("HelloDiPricingBundle:Price","prc")
            ->innerJoin("prc.account","acc")
            ->where("acc.type = :acc_type")->setParameter('acc_type',Account::PROVIDER)
            ->where("prc.item = :itm")->setParameter("itm",$item)
            ->getQuery();
        $haspriceforprov = (count($qb->getResult())>0);

        $prices = $em->createQueryBuilder()
            ->select('DISTINCT a.currency')
            ->from("HelloDiPricingBundle:Price","p")
            ->join('p.account','a')
            ->where('p.item = :item')->setParameter('item',$item)
            ->andWhere('a.type = :acc_type')->setParameter('acc_type',Account::DISTRIBUTOR)
            ->getQuery()
            ->getResult();

        $form = $this->createFormBuilder()
            ->add('checks', 'entity', array(
                    'class' => 'HelloDiAccountingBundle:Account',
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
                            $RetAccs = $accountdist->getChildren()->toArray();
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

        return $this->render('HelloDiMasterBundle:item:itemsPerDist.html.twig', array(
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
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiCoreBundle:Item')->find($id);
        if (!$item)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));

        $descriptions = $item->getDescriptions();

        return $this->render('HelloDiMasterBundle:item:descIndex.html.twig', array(
                'item'      => $item,
                'descriptions' => $descriptions
            ));
    }

    public function descNewAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiCoreBundle:Item')->find($id);
        if (!$item)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Item'),'message'));

        $all_languages = $this->container->getParameter('languages');
        $qb = $em->createQueryBuilder()
            ->select('DISTINCT u.language')
            ->from('HelloDiCoreBundle:ItemDesc','u')
            ->where('u.item = :item')->setParameter('item',$item)
            ->getQuery()->getResult();
        $descriptions_languages = array();
        foreach ($qb as $row)
            $descriptions_languages[] = $row["language"];
        $languages = array_diff($all_languages,$descriptions_languages);

        $desc = new ItemDesc();
        $desc->setItem($item);

        $form = $this->createForm(new ItemDescType($languages), $desc)
            ->add('add','submit', array(
                'label'=>'Add','translation_domain'=>'common',
                'attr'=>array('first-button')
            ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_item_desc_index',array('id' => $item->getId())).'")','last-button')
                ))
        ;

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($desc);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_item_desc_index',array('id' => $id)));
            }
        }

        return $this->render('HelloDiMasterBundle:item:descNew.html.twig', array(
                'form' => $form->createView(),
                'item' => $item
            ));
    }

    public function descEditAction(Request $request, $id, $desc_id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $desc = $em->getRepository('HelloDiCoreBundle:ItemDesc')->find($desc_id);
        if (!$desc)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Description',array(),'item')),'message'));

        $all_languages = $this->container->getParameter('languages');
        $qb = $em->createQueryBuilder()
            ->select('DISTINCT u.language')
            ->from('HelloDiCoreBundle:ItemDesc','u')
            ->where('u.item = :item')->setParameter('item',$desc->getItem())
            ->andWhere('u != :this')->setParameter('this', $desc)
            ->getQuery()->getResult();
        $descriptions_languages = array();
        foreach ($qb as $row)
            $descriptions_languages[] = $row["language"];
        $languages = array_diff($all_languages,$descriptions_languages);

        $form = $this->createForm(new ItemDescType($languages), $desc)
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_item_desc_index',array('id' => $id)).'")','last-button')
                ))
        ;

        if ($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_item_desc_index',array('id' => $id)));
            }
        }

        return $this->render('HelloDiMasterBundle:item:descEdit.html.twig', array(
                'form' => $form->createView(),
                'item' => $desc->getItem(),
                'itemdescid' => $desc->getId()
            ));
    }

    public function descPrintAction($print ,$id ,$desc_id)
    {
        $em = $this->getDoctrine()->getManager();
        $description = $em->getRepository('HelloDiCoreBundle:ItemDesc')->find($desc_id)->getDescription();

        $trans = array();
        for ($i = 1; $i <= 2; $i++) {
            $tran = array(
                "pin" => '12345678' . $i,
                "serial" => '87654321' . $i,
                "expire" => "2012/12/12",
                "print_date" => "2013/13/13",
                "entity_name" => 'Entity Name ' . $i,
                "operator" => 'Operator Name ' . $i,
                "entity_address1" => 'Address Line 1 ' . $i,
                "entity_address2" => 'Address Line 2 ' . $i,
                "entity_address3" => 'Address Line 3 ' . $i
            );
            $trans[] = $tran;
        }

        $html = $this->render('HelloDiMasterBundle:item:descPrint.html.twig',array(
                'trans'=>$trans,
                'description'=>str_replace('{{duplicate}}','{{duplicate|raw}}',$description),
                'duplicate'=> false,
                'print' => $print,
                'desc_id' => $desc_id,
                'item_id' => $id
            ));

        if($print == 'web')
            return $html;
        else
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html->getContent()),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="TestPrintCodes.pdf"'
                )
            );
    }
}
