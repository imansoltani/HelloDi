<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Form\RetailerAccountUserType;
use HelloDi\DistributorBundle\Form\RetailerSearchType;
use HelloDi\MasterBundle\Form\AccountUserType;
use HelloDi\MasterBundle\Form\EntityType;
use HelloDi\RetailerBundle\Entity\Retailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RetailerController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=> $this->getUser()->getAccount()));

        $qb = $em->createQueryBuilder()
            ->select('retailer')
            ->from('HelloDiRetailerBundle:Retailer', 'retailer')
            ->where('retailer.distributor = :distributor')->setParameter('distributor', $distributor);

        $form = $this->createForm(new RetailerSearchType($distributor),null,array('attr'=>array('class'=>'SearchForm')))
            ->add('search','submit');

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if($form->isValid()) {
                $data = $form->getData();

                $qb ->innerJoin('retailer.account', 'account')
                    ->innerJoin('account.entity', 'entity');

                if(isset($data['city']))
                    $qb->andWhere('entity.city = :city')->setParameter('city', $data['city']);

                if(isset($data['balanceValue']))
                    $qb->andWhere('account.balance :cmp :balance')
                        ->setParameter('cmp', $data['balanceType'])
                        ->setParameter('balance', $data['balanceValue']);
            }
        }

        $retailers = $qb->getQuery()->getResult();

        return $this->render('HelloDiDistributorBundle:retailer:index.html.twig', array(
                'retailers' => $retailers,
                'form' => $form->createView(),
        ));
    }

    public function addAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=>$this->getUser()->getAccount()));

        $retailer = new Retailer();
        $retailer->setDistributor($distributor);
        $distributor->addRetailer($retailer);

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::DISTRIBUTOR);
        $retailer->setAccount($account);

        $entity = new Entity();
        $account->setEntity($entity);
        $entity->addAccount($account);

        $user = new User();
        $user->setAccount($account);
        $user->setEntity($entity);
        $account->addUser($user);
        $entity->addUser($user);

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RetailerAccountUserType($languages), $retailer, array('cascade_validation' => true));
        $form->get('account')->add('entity',new EntityType());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($retailer);
                $em->persist($account);
                $em->persist($entity);
                $em->persist($user);
                $em->flush();

//                $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>13,'value'=>'   ('.$Account->getAccName().')'));
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_distributor_retailer_index'));
            }
        }

        return $this->render('HelloDiDistributorBundle:retailer:add.html.twig', array(
                'form' => $form->createView(),
                'account' => $account
            ));
    }
}
