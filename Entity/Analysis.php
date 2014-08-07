<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

use CKM\AppBundle\Validator\DimensionRules;

/**
 * Analysis
 *
 * @ORM\Table(name="ckm_analysis")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\AnalysisRepository")
 *
 */
#  * @DimensionRules(groups={"flow_createAnalyse_step1"})
class Analysis
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CKM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="CKM\AppBundle\Entity\Scenario")
     * @ORM\JoinColumn(nullable=false)
     */
    private $scenario;

    /**
     * @var string
     *
     * @ORM\Column(name="datacard", type="text")
     */
    private $datacard;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text")
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var array
     *
     * @ORM\Column(name="target_element", type="array")
     */
    #private $targetElement;

    /**
     * @var integer
     *
     * @ORM\Column(name="scan_constraint", type="integer")
     */
    private $scanConstraint;

    /*
     * @var array
     *
     * @ORM\Column(name="source_element", type="array")
     */
    #private $sourceElement;

    /**
     * @var string
     *
     * @ORM\Column(name="config", type="string", length=255)
     */
    private $config;

    /**
     * @var integer
     *
     * @ORM\Column(name="granularity", type="integer")
     * @Assert\Range(
     *      min = 10,
     *      max = 1000,
     *      minMessage = "Granularity must be greater than 10",
     *      maxMessage = "Granularity must be lesser than 1000"
     * )
     */
    private $granularity;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status=-1;

    public function __construct()
    {
        #$this->sourceElement = new ArrayCollection();
        #$this->targetElement = new ArrayCollection();
        $this->datacard =  '' ; #"/home/alclaude/Documents/DEV_PHP/ckm-web2/src/CKM/globalCKMfit_scenario.txt";
        $this->config = "config";
        $this->granularity = 250;
        $this->scanMax = 0;
        $this->scanMin = 0;

        $this->date = new \DateTime();
    }



    public function isNumberOfTargetValid(ExecutionContextInterface $context)
    {


        /*$em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(e.id) FROM CKM\AppBundle\Entity\ElementTarget e WHERE e.analyse_id = :analyse');
        $query->setParameter('analyse', $this->getId() );
        $count = $query->getSingleScalarResult();


        if ($this->scanConstraint==1 && $count!==1) {
            $context->addViolationAt('scanConstraint', 'Error specific AC scan=1', array(), null);
        }
        if ($this->scanConstraint==2 && $count!==2) {
            $context->addViolationAt('scanConstraint', 'Error specific AC scan=2', array(), null);
        }
        die( 'toto' );*/
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Id
     *
     * @param integer $id
     * @return Analysis
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Analysis
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set targetElement
     *
     * @param array $targetElement
     * @return Analysis
     */
    public function setTargetElement($targetElement)
    {
        $this->targetElement = $targetElement;

        return $this;
    }

    /**
     * Get targetElement
     *
     * @return array
     */
    public function getTargetElement()
    {
        return $this->targetElement;
    }

    /**
     * Set scanConstraint
     *
     * @param string $scanConstraint
     * @return Analysis
     */
    public function setScanConstraint($scanConstraint)
    {
        $this->scanConstraint = $scanConstraint;

        return $this;
    }

    /**
     * Get scanConstraint
     *
     * @return string
     */
    public function getScanConstraint()
    {
        return $this->scanConstraint;
    }

    /**
     * Set sourceElement
     *
     * @param array $sourceElement
     * @return Analysis
     */
    public function setSourceElement($sourceElement)
    {
        $this->sourceElement = $sourceElement;

        return $this;
    }

    public function canHaveSourceElement() {return true ;}

    /**
     * Get sourceElement
     *
     * @return array
     */
    public function getSourceElement()
    {
        return $this->sourceElement;
    }

    /**
     * Set config
     *
     * @param string $config
     * @return Analysis
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get config
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set datacard
     *
     * @param string $datacard
     * @return Analysis
     */
    public function setDatacard($observables, $parameters, $targets)
    {
        #$this->datacard = $datacard;
        $this->datacard = $this->initDatacard($observables, $parameters, $targets);

        return $this;
    }

    private function initDatacard($observables, $parameters, $targets)
    {
      #$this->datacard = $datacard;

      # parameter not to print : A, lambda, rhobar, etabar
      $notToPrints=array('A', 'lambda', 'rhobar', 'etabar');

      $scenario  = $this->getScenario()->getWebPath();
      $rl = "\n";

      $datacard  = '';
      $datacard .= '{';
      $datacard .= $rl.$rl;

      # gestion des observables
    /*
      foreach( $observables as $observable ) {
        #print '<pre>';
        #print \Doctrine\Common\Util\Debug::dump($observable);
        #print '</pre>';
        switch ( $observable->getName() ) {
          case '|Vud|':
          case '|Vus|':
          case '|Vub|':
          case '|Vcb|':
          case 'sin2beta':
          case 'cos2beta':
              $datacard .= $this->writeElement($observable,$rl);
              break;
          case '|epsilonK|':
          case 'Deltamd':
          case 'Deltams':
              $datacard .= $this->writeParameterName($observable,$rl);
              $datacard .= $this->writeElement($observable,$rl);
              break;
          case 'alpha':
          case 'gamma':
              $datacard .= $this->writeObservableWithFile($observable,$rl);
              break;
          default:
            $datacard .= $rl.$rl.$observable->getName()." : observable not write".$rl;
        }
      }
    */

      # ecriture des observables
      foreach( $observables as $observable ) {
        $observableParameters = $observable->getParameters();
        $observableParametersToUse = array();

        #\Doctrine\Common\Util\Debug::dump($observableParameters);
        #die('debbug');

        # search if A, lambda, rhobar, etabar are presents
        foreach( $observableParameters as $key => $observableParameter ) {
          $trouve=0;
          foreach( $notToPrints as $notToPrint ) {
            if( $notToPrint === $observableParameter->getName() ) {
              $trouve=1;
              break;
            }
          }
          if($trouve==0) { $observableParametersToUse[]=$observableParameter; }
        }
        if( count($observableParametersToUse) > 0 ) {
          $datacard .= $this->writeParameterName($observable, $observableParametersToUse, $rl);
          $datacard .= $this->writeElement($observable,$rl);
        } else {
          $datacard .= $this->writeElement($observable,$rl);
        }
      }

      # gestion des parametres
      foreach( $parameters as $parameter ) {
        $datacard .= $this->writeElement($parameter,$rl);
      }

      $datacard .= $rl;

      $datacard .= $this->writeTargets($targets,$rl);

      $datacard .= $rl;
      $datacard .= '}';

      #print '<pre>';
      #echo $datacard;
      #print '</pre>';
      #die('debbug');

      return $datacard;
    }

    private function writeTargets($targets,$rl) {
      $target1=$targets[0];

      if(isset($targets[1]) ) {
        $target2=$targets[1];
        if($target2->getIsAbscissa() ) {
          $target2=$targets[0];
          $target1=$targets[1];
        }
      }

      $datacard  = '{';
      $datacard .= '"'.$target1->getName().'"';
      if(isset($targets[1]) ) {
        $datacard .= ', "'.$target2->getName().'"';
      }
      $datacard  .= '}';

      $datacard  .= ' {';
      $datacard .= '"'.$target1->getScanMin().'"';
      if(isset($targets[1]) ) {
        $datacard .= ', "'.$target2->getScanMin().'"';
      }
      $datacard  .= '}';

      $datacard  .= ' {';
      $datacard .= '"'.$target1->getScanMax().'"';
      if(isset($targets[1]) ) {
        $datacard .= ', "'.$target2->getScanMax().'"';
      }
      $datacard  .= '}';

      $datacard .= $rl.$rl;
      return $datacard;
    }

    private function writeObservableWithFile($observable,$rl) {
      $datacard  = '{';
      $datacard .= '"'.$observable->getName().'",';
      $datacard .= '"this is a file ???"';
      $datacard .= '}';
      $datacard .= ',';
      $datacard .= $rl.$rl;
      return $datacard;
    }

    private function writeParameterName($observable, $parameters=array(), $rl) {
      $datacard  = '{"All('.$observable->getName().')",';
      $datacard .= '"'.$observable->getName().'",';
      foreach ($parameters as $parameter) {
        $datacard .= '"';
        $datacard .= $parameter->getName();
        $datacard .= '",';
      }
      $datacard=rtrim($datacard, ",\s");
      $datacard .= '},';
      $datacard .= $rl;

      return $datacard;
    }

    private function writeElement($element,$rl) {
        $datacard  = '{';
        $datacard .= '"'.$element->getName().'"';
        $datacard .= ',';

        if( $element->getValue()== 0 and $element->getExpUncertity()==0 and $element->getThUncertity()==0) {
          #$datacard .= $element->getCurrentTag();
          $datacard .= '"'.$element->getCurrentTag().'"';
        } else {
          $datacard .= $element->getValue();
          $datacard .= ',';
          $datacard .= $element->getExpUncertity();
          $datacard .= ',';
          $datacard .= $element->getThUncertity();
        }
        $datacard .= '}';
        $datacard .= ',';
        $datacard .= $rl.$rl;
        return $datacard;
    }

    /**
     * Get datacard
     *
     * @return string
     */
    public function getDatacard()
    {
        return $this->datacard;
    }

    /**
     * Set granularity
     *
     * @param integer $granularity
     * @return Analysis
     */
    public function setGranularity($granularity)
    {
        $this->granularity = $granularity;

        return $this;
    }

    /**
     * Get granularity
     *
     * @return integer
     */
    public function getGranularity()
    {
        return $this->granularity;
    }

    /**
     * Set scanMax
     *
     * @param integer $scanMax
     * @return Analysis
     */
    public function setScanMax($scanMax)
    {
        $this->scanMax = $scanMax;

        return $this;
    }

    /**
     * Get scanMax
     *
     * @return integer
     */
    public function getScanMax()
    {
        return $this->scanMax;
    }

    /**
     * Set scanMin
     *
     * @param integer $scanMin
     * @return Analysis
     */
    public function setScanMin($scanMin)
    {
        $this->scanMin = $scanMin;

        return $this;
    }

    /**
     * Get scanMin
     *
     * @return integer
     */
    public function getScanMin()
    {
        return $this->scanMin;
    }

    /**
     * Set user
     *
     * @param \CKM\UserBundle\Entity\User $user
     * @return Analysis
     */
    public function setUser(\CKM\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \CKM\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set scenario
     *
     * @param string $scenario
     * @return Analysis
     */
    public function setScenario(\CKM\AppBundle\Entity\Scenario $scenario = null)
    {
        $this->scenario = $scenario;

        return $this;
    }

    /**
     * Get scenario
     *
     * @return string
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Analysis
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function isObservable($target)  {
      $scenario  = $this->getScenario()->getWebPath();
      $data = file_get_contents($scenario) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);

      $new_line = "^\n$" ;
      $targetPattern =  '/'.preg_quote( $target, '/' ).'/';

      // recherche des params de l observable
      foreach($lines as $line) {
        if( ! preg_match("/$new_line/", $line) ) {
          $name = explode(';',$line);
          if( preg_match($targetPattern, $name[0]) ) {
            return true;
          }
          if( preg_match('/# parameter/', $line) ) {
            return false;
          }
        }
      }
    }

    public function isParamOfObservableTarget($paramName, $observables) {
      foreach($observables as $observable) {
        if($this->isObservable($observable->getName() )) {
          foreach( ($observable->getParameters()) as $parameter) {
            if( $parameter->getName()=== $paramName) {
              return true;
            }
          }
        }
      }
      return false;
    }

    public function __clone() {
      if ($this->id) {
        $this->setId(null);
        $this->setDate(new \DateTime());
      }
    }
}
