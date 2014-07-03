<?php

namespace HelloDi\DistributorBundle\Form;

use Doctrine\ORM\EntityRepository;
use HelloDi\DistributorBundle\Entity\Distributor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RetailerSearchType extends AbstractType
{
    private $distributor;

    public function __construct(Distributor $distributor)
    {
        $this->distributor = $distributor;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $cities = $em->createQueryBuilder()
//            ->select('entity.city')
//            ->from('HelloDiDistributorBundle:Distributor', 'distributor')
////            ->innerJoin('distributor.retailers', 'retailer')
////            ->innerJoin('retailer.account', 'accountRetailer')
////            ->innerJoin('accountRetailer.entity', 'entity')
//            ->innerJoin('distributor.account', 'account')
//            ->innerJoin('account.entity', 'entity')
//            ->groupBy('entity.city')
//            ->getQuery()->getResult();
//
//        die(var_dump($cities));

        $builder
            ->add('city','choice', array(
                    'label'=>'City', 'translation_domain'=>'entity',
                    'choices' => array(),
                    'required'=>false,
                    'empty_value'=> 'All',
                ))
            ->add('balanceType', 'choice', array(
                    'label'=>'Balance', 'translation_domain'=>'accounts',
                    'choices'=> array(1=>'<', 2=>'=', 3=>'>'),
                ))
            ->add('balanceValue','money', array(
                    'currency' => $this->distributor->getCurrency(),
                    'required'=>false,
                    'attr'=> array('class'=>'float_validation'),
                ));
    }

    public function getName()
    {
        return 'hellodi_distributor_bundle_retailer_search_type';
    }
}
