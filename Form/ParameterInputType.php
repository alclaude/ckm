<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParameterInputType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
            ))
          ->add('value', 'text', array(
              'attr' => array('class' => 'form-control'),
            ))
          ->add('defaultValue', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
            ))
          ->add('expUncertity', 'text', array(
              'attr' => array('class' => 'form-control'),
            ))
          ->add('expUncertityDefault', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
            ))
          ->add('thUncertity', 'text', array(
              'attr' => array('class' => 'form-control'),
            ))
          ->add('thUncertityDefault', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\ParameterInput'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_parameterinput';
    }
}
