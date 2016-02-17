<?php

namespace SpikeTeam\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SpikeTeam\UserBundle\Form\StringToArrayTransformer;

class AdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', array(
                'required' => false,
            ))
            ->add('lastName', 'text', array(
                'required' => false,
            ))
            ->add('email', 'email', array(
                'required' => true,
            ))
            ->add('password', 'password', array(
                'required' => false,
            ))
            ->add('phoneNumber', 'text', array(
                'required' => false,
            ))
            ->add('isEnabled', 'checkbox', array(
                'label' => 'Opt-in to alert texts?',
                'required' => false,
            ));

            $transformer = new StringToArrayTransformer();
            $builder->add(
                $builder->create('roles', 'choice',  array(
                    'choices' => array(
                        'ROLE_CAPTAIN' => 'Captain',
                        'ROLE_ADMIN' => 'Admin',
                        'ROLE_SUPER_ADMIN' => 'Super Admin'
                    ),
                    'multiple' => false,
                ))->addModelTransformer($transformer)
            )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SpikeTeam\UserBundle\Entity\Admin'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'spiketeam_userbundle_admin';
    }
}
