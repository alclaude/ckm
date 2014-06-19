<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\EntityManager;

class AnalysisManager
{
  protected $em;

  public function __construct(EntityManager $em) {
    $this->em = $em;
  }

  public function getScenariosForInput($input) {
    $scenarios = $this->em
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation(true);

    $scenariosWithInput = array();
    foreach($scenarios as $scenario) {
      $scenarioInputs=$scenario->getInput();
      foreach($scenarioInputs as $scenarioInput) {
        if($scenarioInput==$input->getName() ) {
          $scenariosWithInput[$scenario->getName()]=$scenario->getName();
          break;
        }
      }
    }

    if( $input->getIsTarget() and !$input->getIsInput() ) {
      $scenariosWithInput["none"]="no default input";
    }

    return $scenariosWithInput;
  }

}