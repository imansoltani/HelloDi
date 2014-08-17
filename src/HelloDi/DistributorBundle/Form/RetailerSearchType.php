<?php

namespace HelloDi\DistributorBundle\Form;

use Doctrine\ORM\EntityManager;
use HelloDi\DistributorBundle\Entity\Distributor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RetailerSearchType extends AbstractType
{
    private $distributor;
    private $em;

    public function __construct(Distributor $distributor, EntityManager $em)
    {
        $this->distributor = $distributor;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ArrayCities = $this->em->createQueryBuilder()
            ->select('entity.city')
            ->from('HelloDiDistributorBundle:Distributor', 'distributor')
            ->innerJoin('distributor.retailers', 'retailer')
            ->innerJoin('retailer.account', 'accountRetailer')
            ->innerJoin('accountRetailer.entity', 'entity')
            ->where('distributor = :distributor')->setParameter('distributor',$this->distributor)
            ->groupBy('entity.city')
            ->getQuery()->getArrayResult();

        $cities = array();
        foreach ($ArrayCities as $row)
            $cities [$row['city']] = $row['city'];

        $builder
            ->add('city','choice', array(
                    'label'=>'City', 'translation_domain'=>'entity',
                    'choices' => $cities,
                    'required'=>false,
                    'empty_value'=> 'All',
                ))
            ->add('balanceType', 'choice', array(
                    'label'=>'Balance', 'translation_domain'=>'accounts',
                    'choices'=> array('<'=>'<', '='=>'=', '>'=>'>'),
                ))
            ->add('balanceValue','money', array(
                    'currency' => $this->distributor->getCurrency(),
                    'required'=>false,
                    'attr'=> array('class'=>'float_validation'),
                ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'csrf_protection' => false,
            ));
    }

    public function getName()
    {
        return '';
    }
}
