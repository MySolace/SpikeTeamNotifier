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
                'required' => true,
            ))
            ->add('lastName', 'text', array(
                'required' => true,
            ))
            ->add('email', 'email', array(
                'required' => true,
                'error_bubbling' => true
            ))
            ->add('password', 'password', array(
                'required' => true,
            ))
            ->add('phoneNumber', 'text', array(
                'required' => true,
            ))
            ->add('isEnabled', 'checkbox', array(
                'data'      => true,
                'required'  => false
            ))
            ->add('save', 'submit')
        ;

            $transformer = new StringToArrayTransformer();
            $builder->add(
                $builder->create('roles', 'choice',  array(
                    'choices' => array(
                        'ROLE_CAPTAIN'      => 'Captain',
                        'ROLE_ADMIN'        => 'Admin',
                        'ROLE_SUPER_ADMIN'  => 'Super Admin'
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
