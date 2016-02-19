<?php

namespace SpikeTeam\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpikerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', 'entity', array(
                'class'     => 'SpikeTeamUserBundle:SpikerGroup',
                'property'  => 'name',
            ))
            ->add('firstName', 'text', array('required' => true))
            ->add('lastName', 'text', array('required' => true))
            ->add('phoneNumber', 'text', array(
                'required'          => true,
                'error_bubbling'    => true
            ))
            ->add('isSupervisor', 'checkbox')
            ->add('isEnabled', 'checkbox')
            ->add('cohort', 'text', array(
                'required'  => true,
                'attr'      => array('size' => '2'),
            ))
            ->add('isCaptain', 'checkbox', array(
                'required' => false,
            ))
            ->add('email', 'text', array(
                'required'          => true,
                'error_bubbling'    => true
            ))
            ->add('notificationPreference', 'choice', array(
                'choices' => array(
                    0 => 'Text',
                    1 => 'Phone Call',
                    2 => 'Both'
                ),
                'required' => true,
                'multiple' => false,
            ))
            ->add('save', 'submit')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SpikeTeam\UserBundle\Entity\Spiker'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'spiketeam_userbundle_spiker';
    }
}
