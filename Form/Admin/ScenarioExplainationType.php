<?php

namespace CKM\AppBundle\Form\Admin;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScenarioExplainationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
              'attr' => array('class' => 'form-control'),
              'read_only' => true,
              ))
            ->add('model', 'entity', array(
             'class' => 'CKMAppBundle:Model',
             'mapped'    => true,
             'multiple'  => false,
             'property' => 'name',
             'attr'      => array('class' => 'form-control'),
             ))
            ->add('documentation', 'textarea', array(
              'attr'    => array('class' => 'form-control', 'rows' => '10'),
              #'mapped'  => false,
              'label' => 'Explanations',
              'required' => true,
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\Scenario'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_scenario_explaination';
    }
}
