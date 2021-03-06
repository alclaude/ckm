<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use CKM\AppBundle\Form\EventListener\AddScenarioFieldSubscriber;
use CKM\AppBundle\Form\EventListener\AddModelFieldSubscriber;

class DocumentationType extends AbstractType
{
    protected $em;

    public function __construct($models, $scenarioEnabled=0)
    {
        $this->models = $models;
        $this->scenarioEnabled = $scenarioEnabled;
    }

    /**
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

      $builder->addEventSubscriber(new AddModelFieldSubscriber($this->models) ) ;

      $builder->addEventSubscriber(new AddScenarioFieldSubscriber($this->scenarioEnabled) ) ;

      $builder->add('display', 'submit', array(
              'attr'      => array('class' => 'btn btn-primary btn-lg btn-block', 'style' => 'margin:4px 0;'),
              'label'     => 'Display selected Scenario'
              ) )
              ->add('explain', 'textarea', array(
                    'attr'    => array('class' => 'form-control', 'rows' => '10'),
                    'mapped'  => false,
                    'label' => 'Explanations',
                    'required' => false,
              ))
              ->add('document', 'submit', array(
              'attr'      => array('class' => 'btn btn-primary btn-lg btn-block'),
              'label'     => 'Document scenario'
              ) )
              ->add('export', 'submit', array(
              'attr'      => array('class' => 'btn btn-info btn-lg btn-block'),
              'label'     => 'Export...'
              ) )
              ->add('remove', 'submit', array(
              'attr'      => array('class' => 'btn btn-danger btn-lg btn-block'),
              'label'     => 'Remove'
              ) )
        ;
    }

    /**
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm0(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'choice', array(
                    'label' => 'Scenario Not documented',
                    'mapped'    => false,
                    'attr' => array('class' => 'form-control'),
                    'choices' => $this->choices,
              ))
            ->add('display', 'submit', array(
              'attr'      => array('class' => 'btn btn-primary btn-lg btn-block', 'style' => 'margin:4px 0;'),
              'label'     => 'Display selected Scenario'
              ) )
            ->add('explain', 'textarea', array(
                    'attr'    => array('class' => 'form-control', 'rows' => '10'),
                    'mapped'  => false,
                    'label' => 'Explanations',
                    'required' => false,
              ))
         ->add('document', 'submit', array(
              'attr'      => array('class' => 'btn btn-primary btn-lg btn-block'),
              'label'     => 'Document this scenario'
              ) )
        ;
    }

    /**
    * @param OptionsResolverInterface $resolver
    */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\ScenarioDocumentation'
        ));
    }

    /**
    * @return string
    */
    public function getName()
    {
        return 'ckm_appbundle_documentation';
    }
}
