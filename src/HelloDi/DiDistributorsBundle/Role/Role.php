<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Fils du Soleil
 * Date: 26.06.13
 * Time: 18:21
 * To change this template use File | Settings | File Templates.
 */

namespace HelloDi\DiDistributorsBundle\Role;

use Doctrine\Common\Collections\ArrayCollection;
use HelloDi\DiDistributorsBundle\Entity\OgonePayment;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Exception\OgoneException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Constraints\DateTime;


class Role
{

    private $request;
    private $router;
    private $prefix;

    public   $resultUrl;
    public   $catalogUrl;
    public   $homeUrl;
    public   $ogoneTemplateUrl;


    public function __construct(Router $router,$request)
    {
        $this->request      =$request;
        $this->router      =$router;

        $this->prefix = substr($this->request->getPathInfo(), 0, 7);
        if ($this->prefix === "/app/r/")
        {
            $this->resultUrl    = $this->router->generate('retailer_transactions_result', array(), true);
            $this->catalogUrl   = $this->router->generate('retailer_transactions_new', array(), true);
            $this->homeUrl  = $this->router->generate('retailer_index', array(), true);
            $this->ogoneTemplateUrl   = $this->router->generate('retailer_transactions_ogone_template',array(), true);
        }
        elseif($this->prefix === "/app/d/")
        {
            $this->resultUrl    = $this->router->generate('distributor_transactions_result', array(), true);
            $this->catalogUrl   = $this->router->generate('distributor_transactions_new', array(), true);
            $this->homeUrl       = $this->router->generate('distributor_index', array(), true);
            $this->ogoneTemplateUrl   = $this->router->generate('distributor_transactions_ogone_template',array(), true);
        }
        else
        {
            throw new OgoneException('Invalid prefix!');
        }

    }

    public  function TransactionNew()
    {
        if($this->prefix === "/app/r/")
            $rout=$this->router->generate('retailer_transactions_new');
        elseif($this->prefix === "/app/d/")
           $rout=$this->router->generate('distributor_transactions_new');

        return $rout;
    }


    public  function Validate($id)
    {

         if($this->prefix === "/app/r/")
             $rout=$this->router->generate('retailer_OgoneTransactions_validate',array('id'=>$id));

        elseif($this->prefix === "/app/d/")
            $rout=$this->router->generate('distributor_OgoneTransactions_validate',array('id'=>$id));

        return $rout;

    }



    public  function IndexPage()
    {

        if ($this->prefix === "/app/r/")
            $rout=$this->router->generate('retailer_index');
        elseif($this->prefix === "/app/d/")
            $rout=$this->router->generate('distributor_index');

    return $rout;
    }



}