<?php

namespace CKM\AppBundle\Form\Analyse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;


class AnalysisStep1Type extends AbstractType
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
      count($this->scenario)>0 ? $empty_value=false : $empty_value='Sorry no scenario available. Please cancel your analysis and contact your administrator';

      $builder
        ->add('name', 'text', array(
              'attr' => array('class' => 'form-control'),
              ))
        ->add('model', 'entity', array(
             'class' => 'CKMAppBundle:Model',
             'mapped'    => false,
             'multiple'  => false,
             'property' => 'name',
             'label'  => 'Model',
             'attr'      => array('class' => 'form-control'),
             ))
        ->add('scenario', 'entity', array(
             'class' => 'CKMAppBundle:Scenario',
             'choices' => $this->scenario,
             'mapped'    => true,
             'multiple'  => false,
             'property' => 'name',
             'attr'      => array('class' => 'form-control'),
             'empty_value' => $empty_value,
             ))
        ->add('scanConstraint', 'choice', array(
             'choices' => array('1' => '1D', '2' => '2D'),
             'attr'    => array('class' => 'form-control'),
             ))
      ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\Analysis',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_analysis_step1';
    }

}