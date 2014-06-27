<?php

namespace CKM\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class editLatexType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $value = $options['data']->getValue();

        if($value==0) {
          $placeholder ="set a specific value";
          $htmlValue = "";
          $htmlExpUncertity="";
          $htmlThUncertity="";
        }

        $builder
            ->add('name', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
            ))
          ->add('value', 'text', array(
              'attr' => array('class' => 'form-control', 'placeholder' => $placeholder, 'value'=>$htmlValue),
            ))
          ->add('expUncertity', 'number', array(
              'attr' => array('class' => 'form-control', 'placeholder' => $placeholder, 'value'=>$htmlExpUncertity),
              'label' => 'Experimental uncertainty',
            ))
          ->add('thUncertity', 'number', array(
              'attr' => array('class' => 'form-control', 'placeholder' => $placeholder, 'value'=>$htmlThUncertity),
              'label' => 'Theoretical uncertainty',
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
            'data_class' => 'CKM\AppBundle\Entity\Input',
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