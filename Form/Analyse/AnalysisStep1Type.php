<?php

namespace CKM\AppBundle\Form\Analyse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;


class AnalysisStep1Type extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('scenario', 'entity', array(
             'class' => 'CKMAppBundle:Scenario',
             'mapped'    => true,
             'multiple'  => false,
             'property' => 'name',
             'attr'      => array('class' => 'form-control'),
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