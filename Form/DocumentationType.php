<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentationType extends AbstractType
{
    protected $em;

    public function __construct($choices)
    {
        $this->choices = $choices;
    }

    /**
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
