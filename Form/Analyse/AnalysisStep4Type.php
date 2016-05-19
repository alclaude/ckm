<?php

namespace CKM\AppBundle\Form\Analyse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class AnalysisStep4Type extends AbstractType
{
    protected $em;

    public function __construct( \Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        /*
         * ->add('plotting', 'entity', array(
             'class' => 'CKMAppBundle:Plotting',
             'mapped'    => false,
             'multiple'  => false,
             'property' => 'name',
             'label'  => 'Plotting',
             'attr'      => array('class' => 'form-control'),
             ))
             */
       ->add('nickname', 'text', array(
          'attr' => array('class' => 'form-control', 'maxlength'=>8),
          'label'  => 'Please enter a nickname to appear as CKMlive by nickname',
          
        ))
       ->add('title', 'text', array(
          'attr' => array('class' => 'form-control'),
          'label'  => 'Please enter a title for the plot of the result',
        ))
      ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\Plotting',
            #'data_class' => null,
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_analysis_step4';
    }

}
