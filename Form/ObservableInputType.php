<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObservableInputType extends AbstractType
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
              'label' => 'Experimental uncertity',
            ))
          ->add('expUncertityDefault', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
              'label' => 'Default experimental uncertity',
            ))
          ->add('thUncertity', 'text', array(
              'attr' => array('class' => 'form-control'),
              'label' => 'Theoretical uncertity',
            ))
          ->add('thUncertityDefault', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
              'label' => 'Default theoretical uncertity',
            ))
          //->add('associatedElement')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\ObservableInput',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_observable_input';
    }
}