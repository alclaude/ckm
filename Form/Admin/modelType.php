<?php

namespace CKM\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class modelType extends AbstractType
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
          ))
          ->add('isEnable', 'checkbox', array(
              'required'  => false,
              'attr' => array('class' => 'form-control'),
          ))
          ->add('documentation', 'textarea', array(
                    'attr'    => array('class' => 'form-control', 'rows' => '10'),
                    #'mapped'  => false,
                    'label' => 'Explanations (possible HTML)',
                    'required' => false,
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
            'data_class' => 'CKM\AppBundle\Entity\Model',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_Model';
    }
}
