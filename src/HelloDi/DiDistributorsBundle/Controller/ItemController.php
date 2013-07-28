<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\ItemDesc;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Form\ItemDescType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Form\ItemType;

class ItemController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository('HelloDiDiDistributorsBundle:Item')->findAll();

        return $this->render('HelloDiDiDistributorsBundle:Item:index.html.twig', array(
            'items' => $items,
        ));
    }

    public function newAction(Request $request)
    {
        $langs = $this->container->getParameter('languages');
        $langs = array_combine($langs, $langs);

        $item  = new Item();
        $itemdesc = new ItemDesc();
        $itemdesc->setItem($item);
        $item->addItemDesc($itemdesc);

        $form   = $this->createForm(new ItemType($langs), $item, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $item->setItemDateInsert(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($item);
                $em->persist($itemdesc);
                $em->flush();
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
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:show.html.twig', array(
            'item'      => $item
        ));
    }

    public function editAction(Request $request,$id)
    {
        $langs = $this->container->getParameter('languages');
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$item) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $editForm = $this->createForm(new ItemType($langs), $item);
        if ($request->isMethod('POST'))
        {
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $em->persist($item);
                $em->flush();

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
        $em = $this->getDoctrine()->getEntityManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);
        $qb = $em->createQueryBuilder()
            ->select("prc")
            ->from("HelloDiDiDistributorsBundle:Price","prc")
            ->innerJoin("prc.Account","acc")
            ->where("acc.accType = 1")
            ->where("prc.priceStatus = 1")
            ->where("prc.Item = :itm")->setParameter("itm",$item)
            ->getQuery();
        $haspriceforprov = (count($qb->getResult())>0);

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
            ->add('NewPrice','text',array('required'=>true))
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
//                            die("--".count($RetAccs)."--");
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
                    else
                    {
                        if($price != null)
                        {
                            if($price->getPrice() != $newprice)
                            {
                                $price->setPrice($newprice);
                                $price->setPriceStatus(1);

                                $pricehistory = new PriceHistory();
                                $pricehistory->setPrice($newprice);
                                $pricehistory->setDate(new \DateTime('now'));
                                $pricehistory->setPrices($price);
                                $em->persist($pricehistory);
                            }
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
                return $this->redirect($this->generateUrl('item_price', array('id' => $id)));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:ItemsPerDistributors.html.twig', array(
                'form' => $form->createView(),
                'itemid' => $id,
                'item'      => $item,
                'haspriceforprov' => $haspriceforprov
            ));
    }
    //item desc

    public function descIndexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        $itemdescs = $item->getItemDescs();

        if (!$item) {
            throw $this->createNotFoundException('Unable to find ItemSesc entity.');
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:descindex.html.twig', array(
                'item'      => $item,
                'itemdescs' => $itemdescs,
                'pin' => '1234',
                'serial' => '4321'
            ));
    }

    public function descNewAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);
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
            if ($form->isValid()) {
                $finddesc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$item,'desclang'=>$desc->getDesclang()));
                if($finddesc)
                    $form ->addError(new FormError('language is duplicate.'));
                else
                {
                    $desc->setItem($item);
                    $em->persist($desc);
                    $em->flush();

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
        $langs = $this->container->getParameter('languages');
        $langs = array_combine($langs, $langs);
        $form = $this->createForm(new ItemDescType($langs),$desc);
        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $finddesc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$desc->getItem(),'desclang'=>$desc->getDesclang()));
                if($finddesc and $finddesc!=$desc)
                    $form ->addError(new FormError('language is duplicate.'));
                else
                {
                    $em->persist($desc);
                    $em->flush();

                    return $this->forward('HelloDiDiDistributorsBundle:Item:descIndex', array(
                            'id' => $desc->getItem()->getId()
                        ));
                }
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Item:descedit.html.twig', array(
                'form' => $form->createView(),
                'item' => $desc->getItem(),
                'itemdescid' => $desc->getId()
            ));
    }
}
