<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;


class TargetScanType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
          ->add('name', 'text', array(
              'attr' => array(
                'class' => 'form-control',
                'read_only' => true
              ),
            ))
          /*->add('scanMax', 'number',  array(
            'attr' => array('class' => 'form-control'),
            'invalid_message'            => 'the value must be a number',
          ))
          ->add('scanMin', 'number', array(
            'attr' => array('class' => 'form-control'),
            'invalid_message'            => 'the value must be a number',
          ))*/
          ->addEventListener(FormEvents::POST_SET_DATA, array($this, 'postBind'));
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

    public function postBind(FormEvent $e)
	{
	$data = $e->getForm()->getData();
        $form = $e->getForm();
        $precisionScanMax = $this->countDecimals($data->getScanMax());
        $precisionScanMin = $this->countDecimals($data->getScanMin());
        $scanMaxArray = array(
            'attr' => array('class' => 'form-control'),
            'invalid_message'            => 'the value must be a number',
            //'precision' => 20,
          );
        $scanMinArray = array(
            'attr' => array('class' => 'form-control'),
            'invalid_message'            => 'the value must be a number',
            //'precision' => 20,
        );
        $precisionScanMaxToPrint = '';
        if($precisionScanMax>6) $scanMaxArray['precision'] = $precisionScanMax;
        if($precisionScanMin>6) $scanMinArray['precision'] = $precisionScanMin;
        $form->add('scanMax', 'number', $scanMaxArray)
             ->add('scanMin', 'number', $scanMinArray);
	}

    function countDecimals($fNumber)
    {
        $fNumber = floatval($fNumber);
        for ( $iDecimals = 0; $fNumber != round($fNumber, $iDecimals); $iDecimals++ );
        return $iDecimals;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'ckm_appbundle_target_scan';
    }
}
