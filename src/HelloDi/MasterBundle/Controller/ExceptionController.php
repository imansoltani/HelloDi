<?php

namespace HelloDi\MasterBundle\Controller;

use HelloDi\CoreBundle\Entity\Exceptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExceptionController extends Controller
{
    public function ExceptionsAction($flag)
    {
        $em = $this->getDoctrine()->getManager();

        switch ($flag) {
            case 'Log':
                return new Response(file_get_contents(
                    $this->container->getParameter('kernel.logs_dir') . '/' . $this->container->getParameter(
                        'kernel.environment'
                    ) . '.log'
                ), 200, array(
                    'Content-Type' => 'text/txt',
                    'Content-Disposition' => 'attachment; filename="Log(info).txt"'
                ));

            case 'Export':
                $Exceptions = $em->getRepository('HelloDiCoreBundle:Exceptions')->findAll();
                $result = '';
                foreach ($Exceptions as $Ex) {
                    /** @var Exceptions $Ex */
                    $result .= $Ex->getId() . ' ; ' . $Ex->getDate()->format('YY/m/d') . ' ; ' . $Ex->getUsername() .
                        ' ; ' . $Ex->getDescription() . "\r\n";
                }

                return new Response($result, 200, array(
                    'Content-Type' => 'text/txt',
                    'Content-Disposition' => 'attachment; filename="Exceptions.txt"'
                ));

            case 'DeleteAll':
                $em->createQuery('delete from HelloDiCoreBundle:Exceptions')->execute();
                break;
        }

        $Exceptions = $em->getRepository('HelloDiCoreBundle:Exceptions')->findAll();

        return $this->render('HelloDiDiDistributorsBundle:Exceptions:Exceptions.html.twig', array(
            'Exceptions' => $Exceptions
        ));
    }

    public function DeleteExceptionsAction(Request $req)
    {
        $em = $this->getDoctrine()->getManager();
        $Exception = $em->getRepository('HelloDiDiDistributorsBundle:Exceptions')->find($req->get('id'));
        $em->remove($Exception);
        $em->flush();

        $Exceptions = $em->getRepository('HelloDiDiDistributorsBundle:Exceptions')->findAll();

        return new Response(count($Exceptions));
    }
}