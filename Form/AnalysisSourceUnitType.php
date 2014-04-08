<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;


class AnalysisSourceUnitType extends AbstractType
{

    public function __construct($options = null) {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('name', 'text')
          ->add('defaultInput', 'text')
          ->add('step_'.$this->options['step'], 'hidden', array(
              'data' => 'abcdef',
              'mapped'    => false,
          ))
          ->add('submit', 'submit')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\Observable',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
          return 'ckm_appbundle_analysis_source_unit'.$this->options;
    }
}