<?php

namespace SpikeTeam\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpikerType extends AbstractType
{
    private $options;
    public function __construct($options = array())
    {
        $this->options = array(
            'lastNameRequired'  => true,
            'cohortRequired'    => true
        );

        $this->options = array_merge($this->options, $options);
    }

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
                    'query_builder' => function($repository) {
                        $qb = $repository->createQueryBuilder('sg');
                        return $qb
                            ->where($qb->expr()->eq('sg.public', '1'));
                    },
                )
            )
            ->add('firstName', 'text', array('required' => true))
            ->add('lastName', 'text', array(
                    'required' => $this->options['lastNameRequired']
                )
            )
            ->add('phoneNumber', 'text', array(
                    'required'          => true,
                    'error_bubbling'    => true
                )
            )
            ->add('isSupervisor', 'checkbox', array(
                    'required'          => false
                )
            )
            ->add('isEnabled', 'checkbox', array(
                    'required'          => false
                )
            )
            ->add('cohort', 'text', array(
                    'required' => $this->options['cohortRequired'],
                    'attr'      => array('size' => '2'),
                )
            )
            ->add('isCaptain', 'checkbox', array(
                    'required' => false,
                )
            )
            ->add('email', 'text', array(
                    'required'          => true,
                    'error_bubbling'    => true
                )
            )
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
