<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObservableTagType extends AbstractType
{

  public function __construct($scenarios) {
    $this->scenario = $scenarios;
  }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tag = $options['data']->getTag();

        $builder
          ->add('currentTag', 'choice', array(
              'choices' => $this->scenario,
              'expanded'  => false,
              'multiple'  => false,
              #'data' => $tag,
              'label' => 'Define tag system for Input',
              'attr'      => array('class' => 'form-control'),
            ))
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