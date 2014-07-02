<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

class AnalysisManager
{
  protected $em;
  protected $securityContext;

  public function __construct(EntityManager $em, SecurityContextInterface $securityContext) {
    $this->em = $em;
    $this->securityContext = $securityContext;
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

    if( ( $input->getIsTarget() and !$input->getIsInput() ) or $input->getTag()=='none' ) {
      $scenariosWithInput["none"]="no default input";
    }

    return $scenariosWithInput;
  }

  public function getScenariosIsDocumented($isDocumented) {
    return $this->em
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation($isDocumented);
  }

  public function removeAnalysis(Analysis $analyse ) {
    if (!$this->securityContext->isGranted('ROLE_ANALYSIS')) {
        // Sinon on déclenche une exception « Accès interdit »
        throw new AccessDeniedHttpException('no credentials for this action');
    }
    $targets = $this->em
      ->getRepository('CKMAppBundle:Input')
      ->findTargetByAnalysis( $analyse->getId() );

    foreach($targets as $target) {
      if($analyse->isObservable($target) ) {
        $parameters = $target->getParameters();
        foreach($parameters as $parameter) {
          $this->em->remove($parameter);
        }
      }
    }

    $observables = $this->em
      ->getRepository('CKMAppBundle:Observable')
      ->findByInputAnalysis( $analyse->getId() );

    foreach($observables as $observable) {
      $parameters = $observable->getParameters();
      foreach($parameters as $parameter) {
        $this->em->remove($parameter);
      }
    }

    $this->em->remove($analyse);
    $this->em->flush();
  }

}