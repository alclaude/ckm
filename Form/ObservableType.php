<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObservableType extends AbstractType
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
          ->add('expUncertityPlus', 'number', array(
              'attr' => array('class' => 'form-control'),
              'label' => 'Experimental uncertainty +',
            ))
          ->add('expUncertityPlusDefault', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
              'label' => 'Default experimental uncertainty +',
            ))
          ->add('expUncertityMinus', 'number', array(
              'attr' => array('class' => 'form-control'),
              'label' => 'Experimental uncertainty -',
            ))
          ->add('expUncertityMinusDefault', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
              'label' => 'Default experimental uncertainty -',
            ))
          ->add('thUncertity', 'number', array(
              'attr' => array('class' => 'form-control'),
              'label' => 'Theoretical uncertainty',
            ))
          ->add('thUncertityDefault', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true,
              ),
              'label' => 'Default theoretical uncertainty',
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
            'data_class' => 'CKM\AppBundle\Entity\Observable',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_observable';
    }
}