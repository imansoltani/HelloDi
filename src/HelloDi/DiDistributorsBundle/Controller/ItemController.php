<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Form\ItmSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Form\ItemType;

class ItemController extends Controller
{
    public function indexAction(Request $request)
    {

        $form = $this->createForm(new ItmSearchType());

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()
            ->select('item')
            ->from('HelloDiDiDistributorsBundle:Item','item');

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $data = $form->getData();

            if($data['name']!="")
                $qb->andWhere($qb->expr()->like('item.itemName', $qb->expr()->literal($data['name'].'%')));

            if($data['type']!= 3)
                $qb->andWhere($qb->expr()->eq('item.itemType',intval($data['type'])));

            if($data['currency']!='All')
                $qb->andWhere($qb->expr()->eq('item.itemCurrency',intval($data['currency'])));

            if($data['operator']!="")
                $qb->andWhere($qb->expr()->like('item.operator', $qb->expr()->literal($data['operator'].'%')));
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1),
            10
        );

        return $this->render('HelloDiDiDistributorsBundle:Item:index.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView()
        ));
    }

    public function newAction(Request $request)
    {
        $item  = new Item();
        $form   = $this->createForm(new ItemType(), $item);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($item);
                $em->flush();
                return $this->redirect($this->generateUrl('item'));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Item:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction(Request $request)
    {
        $id = $request->get('itemid');

        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$item) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $editForm = $this->createForm(new ItemType(), $item);

        return $this->render('HelloDiDiDistributorsBundle:Item:edit.html.twig', array(
            'item'      => $item,
            'edit_form'   => $editForm->createView()
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
}
