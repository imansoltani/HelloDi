<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Entity\Userprivilege;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiesSearchType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiType;
use HelloDi\DiDistributorsBundle\Form\User\UserRegistrationEntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EntitiController extends Controller
{
    public function ListEntitiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->findAll();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities,
            $this->get('request')->query->get('page', 1) /*page number*/,
            10/*limit per page*/
        );
        $formsearch = $this->createForm(new EntitiesSearchType());


        if ($request->isMethod('POST')) {

            $formsearch->bind($request);
            $data = $formsearch->getData();


            $qb = $em->createQueryBuilder();

            $qb->select('Ent')
                ->from('HelloDiDiDistributorsBundle:Entiti', 'Ent')
                ->innerJoin('Ent.Accounts', 'EntAcc')
                ->innerJoin('Ent.Country', 'EntCoun');
            if ($data['Country']->getName()!= 'All')
            {

                $qb->where('EntCoun.iso=:iso');
                $qb->setParameter('iso',$data['Country']->getIso());
            }
            if ($data['HaveAccount'] != 2)
                $qb->andwhere($qb->expr()->eq('EntAcc.accType', $data['HaveAccount']));
            if ($data['HaveAccount'] == 2)
            {
                $qb->andwhere($qb->expr()->neq('EntAcc.accType',2));
            }
            if ($data['entName'] != '')
            {
                $qb->andwhere($qb->expr()->like('Ent.entName', $qb->expr()->literal($data['entName'] . '%')));
            }


            $query = $qb->getQuery();

            $count = count($query->getResult());
            $query = $query->setHint('knp_paginator.count', $count);
            $pagination = $paginator->paginate(
                $query,
                $this->get('request')->query->get('page', 1) /*page number*/,
                10/*limit per page*/
            );


        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:main.html.twig', array(
            'pagination' => $pagination, 'formsearch' => $formsearch->createView()
        ));

    }

    public function indexAction($id)
    {
        return $this->render("HelloDiDiDistributorsBundle:Entiti:index.html.twig", array(
            'entityid' => $id
        ));
    }

    public function accountsAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $accounts = $entity->getAccounts();
$retail=1;
$dist=0;
$prov=0;
      foreach( $accounts as $acc )

      {
          if($acc->getAccType()==0 || $acc->getAccType()==1 )
          {
              $dist = 1 ; // 0 is not - 1 is
              $retail = 0 ; // 0 is not - 1 is
          }
          if($acc->getAccType()==1 )
          {
          $prov++;
          }

      }
if($prov==count($accounts))
{
    $prov='yes';
}
        else
        {
            $prov='no';
        }




        return $this->render('HelloDiDiDistributorsBundle:Entiti:accounts.html.twig', array(
            'pagination' => $accounts, 'entity' => $entity,'dist'=>$dist,'prov'=>$prov
        ));
    }

    public function usersAction(Request $request)
    {
        $id = $request->get('entityid');

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');
        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $qb = $em->createQueryBuilder();
        $qb->select('Ent')
            ->from('HelloDiDiDistributorsBundle:Entiti', 'Ent')
            ->innerJoin('Ent.Accounts','EntAcc')
            ->innerJoin('Ent.Users','EntUsr')
            ->where('Ent.id =:Entiti')
            ->orderBy('EntUsr.firstName', 'ASC')
            ->setParameter('Entiti',$entity->getId());
        $query = $qb->getQuery();
        $count = count($query->getResult());
      $query = $query->setHint('knp_paginator.count', $count);



        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );
       // die('sas'.count($pagination));
        return $this->render('HelloDiDiDistributorsBundle:Entiti:users.html.twig', array(
            'pagination' => $pagination, 'entity' => $entity
        ));
    }

    public function AddNewUserEntitiAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $user = new User();
        $listaccdist = $em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array('Entiti' => $entity, 'accProv' => 0));

        foreach ($listaccdist as $ch) {
            $userprivi = new Userprivilege();
            $user->addUserprivilege($userprivi);
            $userprivi->setUser($user);
            $userprivi->setAccount($ch);
        }

        $form = $this->createForm(new UserRegistrationEntityType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));
        if ($request->isMethod('POST')) {

            $form->bind($request);

            if ($form->isValid()) {

                $d = $user->getUserprivileges();
                $user->setEntiti($entity);

                foreach ($d as $up) {
                    if ($up->getPrivileges() == 2)
                        $d->removeElement($up);
                }


                $em->persist($user);
                $em->flush();

                return $this->forward("HelloDiDiDistributorsBundle:Entiti:users", array('entityid' => $id));

            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddNewUserEntiti.html.twig', array('acc' => $listaccdist, 'entity' => $entity, 'form' => $form->createView()));

    }

    public function infoAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $edit_form = $this->createForm(new EntitiType(), $entity);

        return $this->render('HelloDiDiDistributorsBundle:Entiti:info.html.twig', array(
            'edit_form' => $edit_form->createView(), 'entityid' => $id, 'entity' => $entity
        ));
    }

    public function infoSubmitAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getManager();

        $Entiti = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $edit_form = $this->createForm(new EntitiType(), $Entiti);

        if ($request->isMethod('POST')) {
            $edit_form->bind($request);
            if ($edit_form->isValid()) {
                $em->flush($Entiti);
                return $this->forward("HelloDiDiDistributorsBundle:Entiti:info");
            }
        }
        return $this->forward("HelloDiDiDistributorsBundle:Entiti:info");
    }

    public function addressesAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getManager();

        $addresses = $em->getRepository('HelloDiDiDistributorsBundle:Address')->findBy(array('Entiti' => $id, 'adrsEnd' => null));

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        return $this->render('HelloDiDiDistributorsBundle:Entiti:addresses.html.twig', array(
            'addresses' => $addresses, 'entity' => $entity
        ));
    }

    public function AddProvAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $acc = new Account();
        $acc->setEntiti($entity);
        $acc->setAccCreationDate(new \DateTime('now'));
        $acc->setAccCreditLimit(null);
        $acc->setAccBalance(0);
        $acc->setAccType(1);
        $acc->setAccCreditLimit(0);
        $acc->setAccTimeZone(null); /////=========*******

        $form = $this->createForm(new AccountProvType(), $acc);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($acc);
                $em->flush($acc);
                return $this->forward("HelloDiDiDistributorsBundle:Entiti:accounts", array('entityid' => $id));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddProv.html.twig', array('id' => $id, 'entity' => $entity, 'form' => $form->createView()));
    }

    public function AddDistAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $acc = new Account();
        $acc->setEntiti($entity);
        $acc->setAccCreationDate(new \DateTime('now'));
        $acc->setAccBalance(0);
        $acc->setAccType(0);
        $acc->setAccCreditLimit(0);
        $form = $this->createForm(new AccountDistMasterType(), $acc);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($acc);
                $em->flush($acc);
                return $this->forward("HelloDiDiDistributorsBundle:Entiti:accounts", array('entityid' => $id));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddDist.html.twig', array('id' => $id, 'entity' => $entity, 'form' => $form->createView()));
    }

}