<?php

namespace CKM\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class addLatexType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
              'attr' => array('class' => 'form-control', 'read_only' => true),
            ))
          ->add('latex', 'text', array(
              'attr' => array('class' => 'form-control'),
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
            'data_class' => 'CKM\AppBundle\Entity\lATEX',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_LATEX';
    }
}