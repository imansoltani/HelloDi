<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchDistType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountType;
use HelloDi\DiDistributorsBundle\Form\Account\EditDistType;
use HelloDi\DiDistributorsBundle\Form\Account\EditProvType;
use HelloDi\DiDistributorsBundle\Form\Account\EditRetailerType;
use HelloDi\DiDistributorsBundle\Form\Account\EntitiAccountprovType;
use HelloDi\DiDistributorsBundle\Form\Account\MakeAccountIn2StepType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditAddressEntitiType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiMasterType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiRetailerType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use HelloDi\DiDistributorsBundle\Form\User\UserDistSearchType;
use HelloDi\DiDistributorsBundle\Form\searchProvRemovedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use HelloDi\DiDistributorsBundle\Form\searchProvTransType;
use Symfony\Component\Validator\Constraints\DateTime;


class ProfileController extends Controller
{
    public function  EntitiAction(Request $req)
    {
        $em=$this->getDoctrine()->getManager();
        $Entiti=$this->getUser()->getEntiti();

        $form=$this->createForm(new EditEntitiMasterType(),$Entiti);

        if($req->isMethod('post'))
        {
            $form->handleRequest($req);
            if($form->isValid())
            {

                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Profile:Entiti.html.twig', array(
            'edit_form'=>$form->createView(),
            'entity' => $Entiti,
        ));

    }


    public function AddressAction(Request $request)
    {

        $em=$this->getDoctrine()->getManager();
        $DetaHis=new DetailHistory();
        $entity=$this->getUser()->getEntiti();
        $form=$this->createForm(new EditAddressEntitiType(),$entity);

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);
            if($form->isValid())
            {

                $DetaHis->setAdrs1($entity->getEntAdrs1());
                $DetaHis->setAdrs2($entity->getEntAdrs2());
                $DetaHis->setAdrs3($entity->getEntAdrs3());
                $DetaHis->setAdrsNp($entity->getEntNp());
                $DetaHis->setAdrsCity($entity->getEntCity());
                $DetaHis->setCountry($entity->getCountry());
                $DetaHis->setEntiti($entity);
                $DetaHis->setAdrsDate(new \DateTime('now'));
                $em->persist($DetaHis);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');

            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Profile:Address.html.twig', array(
            'entity' => $entity,
            'form_edit'=>$form->createView()
        ));


    }

}
