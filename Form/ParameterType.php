<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParameterType extends AbstractType
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
          ->add('expUncertity', 'number', array(
              'attr' => array('class' => 'form-control'),
              'label' => 'Experimental uncertainty',
            ))
          ->add('thUncertity', 'number', array(
              'attr' => array('class' => 'form-control'),
              'label' => 'Theoretical uncertainty',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\Parameter'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_parameter';
    }
}
