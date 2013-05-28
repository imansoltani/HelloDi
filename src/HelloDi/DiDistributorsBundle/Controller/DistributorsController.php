<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use HelloDi\DiDistributorsBundle\Entity\Address;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Userprivilege;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchDistType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchProvType;
use HelloDi\DiDistributorsBundle\Form\Account\EntitiAccountprovType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\PriceType;
use HelloDi\DiDistributorsBundle\Form\User\DistUserPrivilegeType;
use HelloDi\DiDistributorsBundle\Form\User\UserDistSearchType;
use HelloDi\DiDistributorsBundle\Form\User\UserRegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class DistributorsController extends Controller
{
  #Retailers#

public  function ListUsersInRetailersAction(Request $req,$id)
{

 $paginator = $this->get('knp_paginator');
$em=$this->getDoctrine()->getManager();
$AccountRetailer=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
$listusers=$AccountRetailer->getUsers();
    $pagination = $paginator->paginate(
        $listusers,
        $this->get('request')->query->get('page', 1) /*page number*/,
        5/*limit per page*/
    );
    return $this->render('HelloDiDiDistributorsBundle:Distributors:users.html.twig', array('pagination'=>$pagination,'Account'=>$AccountRetailer,'Entity'=>$$AccountRetailer->getEntiti()));
}



}

