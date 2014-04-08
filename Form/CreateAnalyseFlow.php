<?php

namespace CKM\AppBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\Form\FormTypeInterface;

class CreateAnalyseFlow extends FormFlow {

    /**
     * @var FormTypeInterface
     */
    protected $formType;

    protected $allowDynamicStepNavigation = true;

    public $sourceStep = 0;
    public $initiateSourceStep=false;

    public function setFormType(FormTypeInterface $formType) {
        $this->formType = $formType;
    }

    public function getName() {
        return 'createAnalyse';
    }

    public function setCurrentStepNumber($step) {
      if ($this->currentStepNumber === null) {
        throw new \RuntimeException('The current step has not been determined yet and thus cannot be accessed.');
      }

      $this->currentStepNumber=$step;
    }

    protected function loadStepsConfig() {
        return array(
            array(
                'label' => 'Select scenario',
                'type' => $this->formType,
            ),
            array(
                'label' => 'scanConstraint & target Elements',
                'type' => $this->formType,
            ),
            array(
                'label' => 'Input',
                'type' => $this->formType,
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {

                    return $estimatedCurrentStepNumber > 1 && !$flow->getFormData()->canHaveSourceElement();
                },
            ),
        );
    }


    public function getFormOptions($step, array $options = array()) {
    $options = parent::getFormOptions($step, $options);

    $formData = $this->getFormData();

    if ($step === 1) {
        #$formData->setTargetElement( array_merge( $formData["targetObservable"]->getData(), $formData["targetParameter"]->getData() ) ) ;
    }

    return $options;
}
}