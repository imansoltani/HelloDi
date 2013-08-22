<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomePageController extends Controller
{
    public function languageAction($locale)
    {

        $this->get('session')->set('_locale',$locale);

       return $this->redirect($this->generateUrl($this->get('session')->get('MyRout')));
    }

    public function aboutAction(Request $req)
    {

        $this->SetMyRouteAction($req);

        return $this->render('HelloDiDiDistributorsBundle:HomePage:About.html.twig');
    }

    public function contactAction(Request $req)
    {
        $this->SetMyRouteAction($req);

        return $this->render('HelloDiDiDistributorsBundle:HomePage:Contact.html.twig');
    }

    public function NewsAction(Request $req)
    {
        $this->SetMyRouteAction($req);

        return $this->render('HelloDiDiDistributorsBundle:HomePage:News.html.twig');
    }

    public function NetworkAction(Request $req)
    {
        $this->SetMyRouteAction($req);

        return $this->render('HelloDiDiDistributorsBundle:HomePage:Network.html.twig');
    }


    public function ProductAction(Request $req)
    {
        $this->SetMyRouteAction($req);

        return $this->render('HelloDiDiDistributorsBundle:HomePage:Product.html.twig');
    }


    public function ServicesAction(Request $req)
    {
        $this->SetMyRouteAction($req);

        return $this->render('HelloDiDiDistributorsBundle:HomePage:Services.html.twig');
    }


  public function  SetMyRouteAction($req)
    {
    $MyRout=$req->attributes->get('_route');
    $this->get('session')->set('MyRout',$MyRout);
    return;
    }

}