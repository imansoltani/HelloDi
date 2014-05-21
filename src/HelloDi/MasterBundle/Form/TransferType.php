<?php

namespace HelloDi\MasterBundle\Form;

use Doctrine\ORM\EntityRepository;
use HelloDi\CoreBundle\Entity\Provider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TransferType extends AbstractType
{
    private $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $provider = $this->provider;

        $builder
            ->add('amount','money', array(
                    'currency' => $provider->getCurrency(),
                    'label'=>'Amount','translation_domain'=>'transaction',
                    'attr'=> array('class'=>'float_neg_validation'),
                    'constraints' => array(
                        new Assert\NotNull(),
                        new Assert\Type(array('type'=>'double')),
                    )
                ))
            ->add('provider', 'entity', array(
                    'label'=>'Account', 'translation_domain'=>'accounts',
                    'empty_value' => 'select_a_account',
                    'empty_data' => '',
                    'class' => 'HelloDiCoreBundle:Provider',
                    'property' => 'NameWithCurrency',
                    'required' => true,
                    'query_builder' => function (EntityRepository $er) use ($provider) {
                            return $er->createQueryBuilder('provider')
                                ->innerJoin('provider.account','account')
                                ->where('account.entity = :entity')->setParameter('entity', $provider->getAccount()->getEntity())
                                ->andWhere('provider.currency= :currency')->setParameter('currency', $provider->getCurrency())
                                ;
                        },
                    'constraints' => array(
                        new Assert\NotNull(),
                    )
                ))
            ->add('descriptionForOrigin', 'textarea', array(
                    'label'=>'Description Origin','translation_domain'=>'transaction',
                    'required'=> false
                ))
            ->add('descriptionForDestination', 'textarea', array(
                    'label'=>'Description Destination','translation_domain'=>'transaction',
                    'required'=> false
                ))
            ;
    }

    public function getName()
    {
        return 'hellodi_master_bundle_transfer_type';
    }
}
