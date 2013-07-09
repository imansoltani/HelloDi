<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\TransactionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function indexAction(Request $request)
    {
        $count = $request->get('count');
        $transaction = new Transaction();

        $form = $this->createForm(new TransactionType(), $transaction);

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder()
            ->select('account')
            ->from('HelloDiDiDistributorsBundle:Account', 'account')
            ->where('account.accType != 1');
        $accounts = $qb->getQuery()->getResult();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {

                $codes = $em->getRepository('HelloDiDiDistributorsBundle:Code')->findAll();
                $code = $codes[0];
                $user = $this->get('security.context')->getToken()->getUser();

                $transaction->setTranFees(0);
                $transaction->setTranCurrency('usd');
                $transaction->setTranDate(new \DateTime('now'));
                $transaction->setTranAction('peym');
                $transaction->setUser($user);
                $transaction->setCode($code);

                $em->persist($transaction);

                if ($count > 1) {
                    for ($i = 2; $i <= $count; $i++) {
                        $transaction1 = new Transaction();
                        $transaction1->setAccount($transaction->getAccount());
                        $transaction1->setTranCredit($transaction->getTranCredit());
                        $transaction1->setTranFees(0);
                        $transaction1->setTranCurrency('usd');
                        $transaction1->setTranDate(new \DateTime('now'));
                        $transaction1->setTranAction('peym');
                        $transaction1->setUser($user);
                        $transaction1->setCode($code);

                        $em->persist($transaction1);
                    }
                }

                $em->flush();
            }
        }

        return $this->render(
            "HelloDiDiDistributorsBundle:Test:new.html.twig",
            array(
                'accounts' => $accounts,
                'form' => $form->createView()
            )
        );
    }

    public function index1Action(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()
            ->select('account')
            ->from('HelloDiDiDistributorsBundle:Account', 'account')
            ->where('account.accType != 1');
        $accounts = $qb->getQuery()->getResult();

        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        if (!$account) {
            $account = $accounts[0];
        }
        $codes = array();
        $form = $this->createFormBuilder()
            ->add(
                'Price',
                'entity',
                array(
                    'required' => true,
                    'class' => 'HelloDiDiDistributorsBundle:Price',
                    'query_builder' => function (EntityRepository $er) use ($account) {
                        return $er->createQueryBuilder('u')
                            ->innerJoin('u.Account', 'a')
                            ->where('a = :aaid')
                            ->setParameter('aaid', $account);
                    }
                )
            )
            ->add('count','text',array('data'=>'1'))
            ->getForm();
        $errors = array();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            $data = $form->getData();
            $price = $data['Price'];
            $count = $data['count'];
            $codeselector = $this->get('hello_di_di_distributors.codeselector');

            try {
                $codes = $codeselector->lookForAvailableCode($account, $price, $price->getItem(),$count);
                foreach ($codes as $code) {
                    $user = $this->get('security.context')->getToken()->getUser();
                    $transaction = new Transaction();
                    $transaction->setAccount($account);
                    $transaction->setTranCredit($price->getPrice());
                    $transaction->setTranFees(0);
                    $transaction->setTranCurrency($price->getPriceCurrency());
                    $transaction->setTranDate(new \DateTime('now'));
                    $transaction->setTranAction('sale');
                    $transaction->setUser($user);
                    $transaction->setCode($code);

                    $em->persist($transaction);
                    $em->flush();

                }
                $errors[] = 'Sale Done.';
            } catch (\Exception $ex) {
                $errors[] = $ex->getMessage();
            }

        }

        return $this->render(
            "HelloDiDiDistributorsBundle:Test:new1.html.twig",
            array(
                'codes' => $codes,
                'errors' => $errors,
                'accounts' => $accounts,
                'myaccount' => $account,
                'form' => $form->createView()
            )
        );
    }

    public function OgoneTestAction(Request $request){
        $ogoneForm = $this->createFormBuilder()
            ->add('AMOUNT','text',array('label'=>'Amount'))
            ->add('CURRENCY','choice',array('label'=>'Curency','choices'=> array('CHF'=>'CHF','EUR'=>'EUR','USA'=>'USA')))
            ->add('LANGUAGE','choice',array('label'=>'Language','choices'=>array('en_US'=>'en_US')))
            ->getForm();

        if ($request->isMethod('POST')) {
            $ogoneForm->bind($request);
            $data = $ogoneForm->getData();
            $key = 'MySha-In@key765?';
            $nameMaster = 'hellodi';
            $OrderNimber = '3';
            $center[] = '';
            $center[0] = 'AMOUNT='.$data['AMOUNT'].$key;
            $center[1] = 'CURRENCY='.$data['CURRENCY'].$key;
            $center[2] = 'LANGUAGE='.$data['LANGUAGE'].$key;
            $center[3] = 'PSPID='.$nameMaster.$key;
            $center[4] = 'ORDERID='.$OrderNimber.$key;

            $value[] = '';
            $value[0] = $data['AMOUNT'];
            $value[1] = $data['CURRENCY'];
            $value[2] = $data['LANGUAGE'];
            $value[3] = $OrderNimber;
            $value[4] = $nameMaster;


            sort($center);
            $singer = sha1($center[0].$center[1].$center[2].$center[3].$center[4]) ;
            print $singer;
            return $this->render('HelloDiDiDistributorsBundle::OgoneTestSendPage.html.twig',array('center'=>$center,'sin'=>$singer,'val'=>$value));
        }
        return $this->render('HelloDiDiDistributorsBundle::OgoneTest.html.twig',array('form' => $ogoneForm->createView()));

    }

    public function OgoneTestAcceptAction(){
        $em = $this->getDoctrine()->getManager();
        $test = $this->get('hello_di_di_distributors.GetOrderNumber');
        $pri = $test->GetOrderNumber();
        return $this->render('HelloDiDiDistributorsBundle::OgoneTest.html.twig',array('yt'=>$pri));
    }
    
    //start mostafa
    public function listlangsAction()
    {
        $langs = $this->container->getParameter('languages');
        return $this->render('HelloDiDiDistributorsBundle:Test:listlang.html.twig',array('langs'=>$langs));
    }
    //end mostafa
}
