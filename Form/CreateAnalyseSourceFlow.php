<?php

namespace CKM\AppBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\Form\FormTypeInterface;

class CreateAnalyseSourceFlow extends FormFlow {

    /**
     * @var FormTypeInterface
     */
    protected $formType;

    protected $allowDynamicStepNavigation = true;

    public function setFormType(FormTypeInterface $formType) {
        $this->formType = $formType;
    }

    public function getName() {
        return 'createAnalyseSource';
    }

    protected function loadStepsConfig() {
        return array(
            array(
                'label' => 'scanConstraint',
                'type' => $this->formType,
            ),
            array(
                'label' => 'source',
                'type' => $this->formType,
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {

                    return $estimatedCurrentStepNumber > 1 && !$flow->getFormData()->canHaveSourceElement();
                },
            ),
            array(
                'label' => 'confirmation',
            ),
        );
    }
}