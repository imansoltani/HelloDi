<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\ItemDesc;
use HelloDi\DiDistributorsBundle\Form\ItemDescType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Form\ItemType;

class ItemController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository('HelloDiDiDistributorsBundle:Item')->findAll();

        return $this->render('HelloDiDiDistributorsBundle:Item:index.html.twig', array(
            'items' => $items,
        ));
    }

    public function newAction(Request $request)
    {
        $item  = new Item();
        $form   = $this->createForm(new ItemType(), $item);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $item->setItemDateInsert(new \DateTime('now'));
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

    public function showAction(Request $request)
    {
        $id = $request->get('itemid');

        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        if (!$item) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:show.html.twig', array(
            'item'      => $item
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

            return $this->forward("HelloDiDiDistributorsBundle:Item:show");
        }

        return $this->forward("HelloDiDiDistributorsBundle:Item:edit");
    }

    //item desc

    public function descIndexAction(Request $request)
    {
        $id = $request->get('itemid');

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

    public function descNewAction(Request $request)
    {
        $id = $request->get('itemid');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);
        $desc = new ItemDesc();
        $form = $this->createForm(new ItemDescType(),$desc);

        return $this->render('HelloDiDiDistributorsBundle:Item:descnew.html.twig', array(
                'form' => $form->createView(),
                'item' => $item
            ));
    }

    public function descNewSubmitAction(Request $request)
    {
        $id = $request->get('itemid');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($id);

        $desc = new ItemDesc();
        $form = $this->createForm(new ItemDescType(),$desc);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $finddesc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$item,'desclang'=>$desc->getDesclang()));
            if($finddesc)
                $form ->addError(new FormError('lang is duplicate.'));
            else
            {
                $desc->setItem($item);
                $em->persist($desc);
                $em->flush();

                return $this->forward('HelloDiDiDistributorsBundle:Item:descIndex', array(
                        'itemid' => $item->getId()
                    ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:descnew.html.twig', array(
                'form' => $form->createView(),
                'item' => $item
            ));
    }

    public function descEditAction(Request $request)
    {
        $id = $request->get('itemdescid');
        $em = $this->getDoctrine()->getManager();
        $desc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->find($id);

        $form = $this->createForm(new ItemDescType(),$desc);

        return $this->render('HelloDiDiDistributorsBundle:Item:descedit.html.twig', array(
                'form' => $form->createView(),
                'item' => $desc->getItem(),
                'itemdescid' => $desc->getId()
            ));
    }

    public function descUpdateAction(Request $request)
    {
        $id = $request->get('itemdescid');
        $em = $this->getDoctrine()->getManager();
        $desc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->find($id);

        $form = $this->createForm(new ItemDescType(),$desc);

        $form->bind($request);

        if ($form->isValid()) {
            $finddesc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$desc->getItem(),'desclang'=>$desc->getDesclang()));
            if($finddesc and $finddesc!=$desc)
                $form ->addError(new FormError('lang is duplicate.'));
            else
            {
                $em->persist($desc);
                $em->flush();

                return $this->forward('HelloDiDiDistributorsBundle:Item:descIndex', array(
                        'itemid' => $desc->getItem()->getId()
                    ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Item:descedit.html.twig', array(
                'form' => $form->createView(),
                'item' => $desc->getItem(),
                'itemdescid' => $desc->getId()
            ));
    }
}
