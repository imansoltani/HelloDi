<?php

namespace HelloDi\DiDistributorsBundle\Controller;

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

                $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find(310);
                $user = $this->get('security.context')->getToken()->getUser();

                $transaction->setTranFees(0);
                $transaction->setTranCurrency('usd');
                $transaction->setTranDate(new \DateTime('now'));
                $transaction->setTranAction('paym');
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
                        $transaction1->setTranAction('paym');
                        $transaction1->setUser($user);
                        $transaction1->setCode($code);

                        $em->persist($transaction1);
                    }
                }

                $em->flush();
            }
        }

        return $this->render("HelloDiDiDistributorsBundle:Test:new.html.twig", array(
            'accounts' => $accounts,
            'form' => $form->createView()
        ));
    }
}
