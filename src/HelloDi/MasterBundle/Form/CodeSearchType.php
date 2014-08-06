<?php

namespace HelloDi\MasterBundle\Form;

use Doctrine\ORM\EntityRepository;
use HelloDi\CoreBundle\Entity\Code;
use HelloDi\CoreBundle\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CodeSearchType extends AbstractType
{
    private $count_per_page;

    public function __construct($count_per_page)
    {
        $this->count_per_page = $count_per_page;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('provider','entity',array(
                    'required' => false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiCoreBundle:Provider',
                    'property' => 'name',
                    'label' => 'Provider', 'translation_domain' => 'accounts'
            ))
            ->add('item','entity',array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiCoreBundle:Item',
                    'property' => 'name',
                    'label' => 'Item', 'translation_domain' => 'item',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')->where("u.type != :type")->setParameter('type', Item::IMTU);
                    }
            ))
            ->add('input', 'entity', array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiCoreBundle:Input',
                    'property' => 'fileName',
                    'label' => 'FileName', 'translation_domain' => 'code'
            ))
            ->add('fromSerialNumber', 'text', array(
                    'required' => false,
                    'attr'=> array('class' => 'integer_validation'),
                    'label' => 'FromSerialNumber', 'translation_domain' => 'code',
                    'constraints' => array(
                        new Assert\Regex(array('pattern' => '/^[1-9][0-9]*$/', 'match'=>true))
                    )
            ))
            ->add('toSerialNumber', 'text', array(
                    'required'=> false,
                    'attr'=> array('class' => 'integer_validation'),
                    'label' => 'ToSerialNumber','translation_domain' => 'code',
                    'constraints' => array(
                        new Assert\Regex(array('pattern' => '/^[1-9][0-9]*$/', 'match'=>true))
                    )
            ))
            ->add('status','choice', array(
                    'required' => false,
                    'empty_value'=> 'All',
                    'choices'=> array(Code::AVAILABLE => 'Available', Code::UNAVAILABLE => 'Unavailable'),
                    'label' => 'Status','translation_domain' => 'code'
            ))
            ->add('fromInsertDate', 'date', array(
                    'required'=> false,
                    'widget' => 'single_text','format' => 'yyyy-MM-dd',
                    'label' => 'FromInsertDate','translation_domain' => 'code'
            ))
            ->add('toInsertDate', 'date', array(
                    'required'=> false,
                    'widget' => 'single_text','format' => 'yyyy-MM-dd',
                    'label' => 'ToInsertDate','translation_domain' => 'code'
            ))
            ->add('fromExpireDate', 'date', array(
                    'required'=> false,
                    'widget' => 'single_text','format' => 'yyyy-MM-dd',
                    'label' => 'FromExpireDate','translation_domain' => 'code'
            ))
            ->add('toExpireDate', 'date', array(
                    'required'=> false,
                    'widget' => 'single_text','format' => 'yyyy-MM-dd',
                    'label' => 'ToExpireDate','translation_domain' => 'code'
            ))
            ->add('count_per_page', 'choice', array(
                    'required'=> false,
                    'choices' => array_unique(array(
                            $this->count_per_page => $this->count_per_page,
                            null => 20,
                            50 => 50,
                            100 => 100,
                            200 => 200,
                        )),
                    'preferred_choices' => array($this->count_per_page),
                    'empty_value' => false,
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
