<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScenarioListType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'entity', array(
              'class' => 'CKMAppBundle:Scenario',
              'property' => 'name',
              'multiple'  => true,
              'label'     => 'Scenario name',
              'attr'      => array('class' => 'form-control', 'size' => 7),
              'validation_groups' => array('choice'),
              ))
            ->add('display', 'submit', array(
              'attr'      => array('class' => 'btn btn-primary btn-lg btn-block', 'style' => 'margin:4px 0;'),
              'label'     => 'Display selected Scenario'
              ) )
            ->add('delete', 'submit', array(
              'attr'      => array('class' => 'btn btn-danger btn-lg btn-block', 'onclick' => 'return confirm(\'Are you really sure to delete this Scenario file ?\');'),
              'label'     => 'Delete selected Scenario'
              ) )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\Scenario',
            'validation_groups' => array('choice'),
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_scenario_list';
    }
}
