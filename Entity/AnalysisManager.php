<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AnalysisManager
{
  protected $em;
  protected $securityContext;

  public function __construct(EntityManager $em, SecurityContextInterface $securityContext) {
    $this->em = $em;
    $this->securityContext = $securityContext;
  }

  public function hasFormatPlot($analysisID, $extensionFormat) {
    return $this->em
      ->getRepository('CKMAppBundle:Model')
      ->findByIsEnable($isEnabled);
  }

  public function getScenariosForInput($input) {
    $scenarios = $this->em
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByModelAndActivated( $input->getAnalyse()->getScenario()->getModel() );

    $scenariosWithInput = array();
    foreach($scenarios as $scenario) {
      $scenarioInputs=$scenario->getInput();
      foreach($scenarioInputs as $scenarioInput) {
        if($scenarioInput==$input->getName() ) {
          #$scenariosWithInput[$scenario->getName()]=$scenario->getName();
          $scenariosWithInput[$scenario->getName()]=$scenario->getTag();
          break;
        }
      }
    }

    if( ( $input->getIsTarget() and !$input->getIsInput() ) or $input->getTag()=='none' ) {
      $scenariosWithInput["none"]="no default input";
    }

    return $scenariosWithInput;
  }

  public function isInputsInScenario($inputsName, $scenario) {
    $presents=array();

    foreach($inputsName as $inputName) {
      $tmp_ar = explode(';',$inputName);
      $return='';
      $return=$this->isInputInScenario($tmp_ar['0'], $scenario) ;
      if($return) $presents[]=$return;
    }

    if(count($presents)>0)
      return $presents;
    else
      return false;
  }

  public function isInputInScenario($inputName, $scenario) {
    $scenarioInputs=$scenario->getInput();

    foreach($scenarioInputs as $scenarioInput) {
      if( $scenarioInput==$inputName ) {
        return $inputName;
      }
    }
    //return false;
  }

  public function getScenariosIsDocumented($isDocumented) {
    return $this->em
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation($isDocumented);
  }

  public function getModelEnabled($isEnabled) {
    return $this->em
      ->getRepository('CKMAppBundle:Model')
      ->findByIsEnable($isEnabled);
  }

  public function removeAnalysis(Analysis $analyse ) {
    if (!$this->securityContext->isGranted('ROLE_ANALYSIS')) {
        // Sinon on déclenche une exception « Accès interdit »
        throw new AccessDeniedHttpException('no credentials for this action');
    }

    $plots = $this->em
      ->getRepository('CKMAppBundle:Plotting')
      ->findPlottingsByAnalysis( $analyse->getId() );

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

    foreach($plots as $plot) {
      $this->em->remove($plot);
    }

    $this->em->remove($analyse);
    $this->em->flush();
  }

  public function checkTagInputInScenarioAdd($file, $upload) {
    if (!$this->securityContext->isGranted('ROLE_ANALYSIS')) {
      // Sinon on déclenche une exception « Accès interdit »
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $tagInputs = $this->em
      ->getRepository('CKMAppBundle:TagInput')
      ->findTagInputByActivated();

    $data = file_get_contents( $upload->getPathname() ) or die("fichier non trouv&eacute;");
    $lines = explode("\n", $data);

    $tagsName = array();
    foreach($tagInputs as $tagInput) {
      $tagsName[]=$tagInput->getName();
    }

    foreach($lines as $line) {
      if( ! preg_match("/^# /", $line) ) {
        $tmp_ar = explode(';',$line);
        $tag = $tmp_ar[count($tmp_ar)-1];

        if (!in_array($tag, $tagsName)) {
          return $line;
        }
      }
    }
    return true;
  }
  
    public function checkTagInputInScenarioEdit($observables, $parameters) {
    if (!$this->securityContext->isGranted('ROLE_ANALYSIS')) {
      // Sinon on déclenche une exception « Accès interdit »
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $tagInputs = $this->em
      ->getRepository('CKMAppBundle:TagInput')
      ->findTagInputByActivated();

    $tagsName = array();
    foreach($tagInputs as $tagInput) {
      $tagsName[]=$tagInput->getName();
    }

    $quantities = array_merge($observables, $parameters);

    foreach($quantities as $quantitie) {
        $tmp_ar = explode(';',$quantitie);
        $tag = $tmp_ar[count($tmp_ar)-1];

        if (!in_array($tag, $tagsName)) {
          return $quantitie;
        }
    }
    return true;
  }

  public function checkNumberEltInScenarioAdd($file, $upload, $nbObservable, $nbParameter) {
    if (!$this->securityContext->isGranted('ROLE_ANALYSIS')) {
      // Sinon on déclenche une exception « Accès interdit »
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $data = file_get_contents( $upload->getPathname() ) or die("fichier non trouv&eacute;");
    $lines = explode("\n", $data);

    $type='';
    $new_line = "^\n$" ;
    $observables = array();
    $parameters  = array();

    foreach($lines as $line) {
      if( ! preg_match("/$new_line/", $line) ) {
        if( preg_match('/observable/', $line) ) {
          $type='observable';
        }
        elseif( preg_match('/parameter/', $line) ) {
          $type='parameter';
        }
        else {
          if( $type==='observable' ) {
            $observables[] = $line;
          }
          if( $type==='parameter' ) {
            $parameters[] = $line;
          }
        }
      }
    }

    foreach($observables as $observable) {
        $tmp_ar = explode(';',$observable);
        if(count($tmp_ar)!=$nbObservable) return $observable;
    }
    foreach($parameters as $parameter) {
        $tmp_ar = explode(';',$parameter);
        if(count($tmp_ar)!=$nbParameter) return $parameter;
    }

    return true;
  }

  public function checkNumberEltInScenarioEdit($observables, $parameters, $nbObservable, $nbParameter) {
    if (!$this->securityContext->isGranted('ROLE_ANALYSIS')) {
      // Sinon on déclenche une exception « Accès interdit »
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    if(count($observables)>0){
      foreach($observables as $observable) {
          $tmp_ar = explode(';',$observable);
          if(count($tmp_ar)!=$nbObservable) return $observable;
      }
    }
    if(count($parameters)>0){
      foreach($parameters as $parameter) {
          $tmp_ar = explode(';',$parameter);
          if(count($tmp_ar)!=$nbParameter) return $parameter;
      }
    }



    return true;
  }
  public function checkTargetScanValueInScenario($path, $target ) {
    if (!$this->securityContext->isGranted('ROLE_ANALYSIS')) {
        // Sinon on déclenche une exception « Accès interdit »
        throw new AccessDeniedHttpException('no credentials for this action');
    }
    if ($path=='' or $target=='') {
      throw new \Exception('path or target not defined');
    }

    $data = file_get_contents($path) or die("fichier non trouv&eacute;");
    $lines = explode("\n", $data);
    $new_line = "^\n$" ;
    $observablePattern =  '/^'.preg_quote( $target, '/' ).'/';
    // info input
    $tmp_ar_obs = array();
    // recherche des elements de l input
    foreach($lines as $line) {
      if( ! preg_match("/$new_line/", $line) ) {
        if( preg_match($observablePattern, $line) ) {
          return explode(';',$line);
        }
      }
    }
    return false;
  }

}
