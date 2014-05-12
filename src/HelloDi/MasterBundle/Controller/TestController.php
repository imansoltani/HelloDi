<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\TransactionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
            ->from('HelloDiAccountingBundle:Account', 'account')
            ->where('account.accType != 1');
        $accounts = $qb->getQuery()->getResult();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
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
            ->from('HelloDiAccountingBundle:Account', 'account')
            ->where('account.accType != 1');
        $accounts = $qb->getQuery()->getResult();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
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
            $form->handleRequest($request);
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
}
