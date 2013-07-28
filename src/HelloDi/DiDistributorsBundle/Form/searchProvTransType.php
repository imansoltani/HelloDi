<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class searchProvTransType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('FromDate','date')
            ->add('ToDate','date')
            ->add('type','choice',array(
                'choices'=> array('All'=>'All','sale'=>'sale','Profit'=>'Profit')));
    }

    public function getName()
    {
        return 'searchProvTransType';
    }
}
