<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Form\ItmSearchType;
use HelloDi\DiDistributorsBundle\Entity\ItmSearch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Form\ItemType;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function indexAction(Request $request)
    {

        $form = $this->createForm(new ItmSearchType());

        $em = $this->getDoctrine()->getManager();

        $items = $em->getRepository('HelloDiDiDistributorsBundle:Item');

        if ($request->isMethod('POST')) {
            $itemsearch="";
            $form->bind($request);
            $data = $form->getData();

            $qb = $items->createQueryBuilder('item');
            if($itemsearch->getName()!="")
                $qb =  $qb->andWhere($qb->expr()->like('item.itemName', $qb->expr()->literal($itemsearch->getName().'%')));

            if($itemsearch->getType()=='0')
                $qb =  $qb->andWhere($qb->expr()->eq('item.itemType',intval($itemsearch->getType() )));

            if($itemsearch->getType()=='1')
                $qb =  $qb->andWhere($qb->expr()->eq('item.itemType',intval($itemsearch->getType() )));

            if($itemsearch->getType()=='2')
                $qb =  $qb->andWhere($qb->expr()->eq('item.itemType',intval($itemsearch->getType() )));

            if($itemsearch->getCurrency()!=2)
                $qb =  $qb->andWhere($qb->expr()->eq('item.itemCurrency',intval($itemsearch->getCurrency() )));

            if($itemsearch->getOperator()!="")
                $qb =  $qb->andWhere($qb->expr()->like('item.operator', $qb->expr()->literal($itemsearch->getOperator().'%')));

            $qb = $qb->getQuery();
            $items = $qb->getResult();
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $items,
            $this->get('request')->query->get('page', 1),
            10
        );

        return $this->render('HelloDiDiDistributorsBundle:Item:index.html.twig', array(
            'pagination' => $pagination,
            'MU' => 'item',
            'form' => $form->createView()
        ));
    }

    public function newAction(Request $request)
    {
        $entity = new Item();
        $form   = $this->createForm(new ItemType(), $entity);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();
//                return $this->render('HelloDiDiDistributorsBundle:Item:index.html.twig', array(
//                 'entities' => $entities,
//                 'form'   => $form->createView(),'MU' => 'item' ,
//
//                ));
                return $this->redirect($this->generateUrl('item'));
//                return $this->redirect("HelloDiDiDistributorsBundle:Item:index");
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Item:new.html.twig', array(
            'form'   => $form->createView(),'MU' => 'item' ,
        ));
    }

    public function editAction(Request $request)
    {
        $id = $request->get('itemid');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $editForm = $this->createForm(new ItemType(), $entity);

        return $this->render('HelloDiDiDistributorsBundle:Item:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'MU' => 'item' ,
            'itemid' => $id
        ));
    }

    public function updateAction(Request $request)
    {
        $id = $request->get('itemid');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $editForm = $this->createForm(new ItemType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->forward("HelloDiDiDistributorsBundle:Item:edit");
        }

        return $this->forward("HelloDiDiDistributorsBundle:Item:edit");
    }

    public function tabsAction($id)
    {
        return $this->render("HelloDiDiDistributorsBundle:Item:tabs.html.twig", array(
            'itemid'=>$id
        ));
    }

}
