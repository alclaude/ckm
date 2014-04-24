<?php

namespace CKM\AppBundle\Entity;

use CKM\AppBundle\Entity\ParameterInput;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObservableInput
 *
 * @ORM\Table(name="observable_input")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ObservableInputRepository")
 *
 */
class ObservableInput
{
    /**
     * @ORM\ManyToOne(targetEntity="CKM\AppBundle\Entity\Analysis")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $analyse;

   /**
    * @ORM\ManyToMany(targetEntity="CKM\AppBundle\Entity\ParameterInput", inversedBy="observableInputs", cascade={"persist", "remove"})
    * @ORM\JoinTable(name="observables_parameters_input",
    *      joinColumns={@ORM\JoinColumn(nullable=false, onDelete="CASCADE")},
    *      inverseJoinColumns={@ORM\JoinColumn(nullable=false, onDelete="CASCADE")}
    *      )
    */
    #* @ORM\JoinTable(name="observables_parameters_input")
    #* @ORM\JoinColumn(nullable=false, onDelete="CASCADE", onUpdate="CASCADE")
    private $parameterInputs;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="default_value", type="float")
     */
    private $defaultValue;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * @var float
     *
     * @ORM\Column(name="allowed_range_max", type="float")
     */
    private $allowedRangeMax;

    /**
     * @var float
     *
     * @ORM\Column(name="allowed_range_min", type="float")
     */
    private $allowedRangeMin;

    /**
     * @var float
     *
     * @ORM\Column(name="exp_uncertity_plus", type="float")
     */
    private $expUncertityPlus;

    /**
     * @var float
     *
     * @ORM\Column(name="exp_uncertity_minus", type="float")
     */
    private $expUncertityMinus;

    /**
     * @var float
     *
     * @ORM\Column(name="th_uncertity", type="float")
     */
    private $thUncertity;

    /**
     * @var float
     *
     * @ORM\Column(name="exp_uncertity_plus_default", type="float")
     */
    private $expUncertityPlusDefault;

    /**
     * @var float
     *
     * @ORM\Column(name="exp_uncertity_minus_default", type="float")
     */
    private $expUncertityMinusDefault;

    /**
     * @var float
     *
     * @ORM\Column(name="th_uncertity_default", type="float")
     */
    private $thUncertityDefault;


    public function __toString() { return 'ObservableInput : '.$this->name; }

    public function __construct($analyse, $name='', $path='',  $defaultValue=0, $allowedRangeMax=0, $allowedRangeMin=0, $expUncertityDefault=0, $thUncertityDefault=0)
    {
      $this->name                    = $name;
      $this->value                   = $defaultValue;
      $this->defaultValue            = $defaultValue;
      $this->allowedRangeMax         = $allowedRangeMax;
      $this->allowedRangeMin         = $allowedRangeMin;
      $this->expUncertityPlus        = 0;
      $this->expUncertityMinus       = 0;
      $this->thUncertity             = $thUncertityDefault;
      $this->expUncertityPlusDefault = 0;
      $this->expUncertityMinusDefault = 0;
      $this->thUncertityDefault       = $thUncertityDefault;
      $this->analyse                  = $analyse;

      $this->init($path);

      $this->parameterInputs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    private function init($path) {
      $ar_obs = $this->findObservableInputLine($path);
      #print_r($ar_obs);
      #die('debbug');

      if($ar_obs) {
        $this->value                    = $this->cleanData( $ar_obs['1'] );
        $this->defaultValue             = $this->cleanData( $ar_obs['1'] );
        $this->allowedRangeMax          = $this->cleanData( $ar_obs['3'] );
        $this->allowedRangeMin          = $this->cleanData( $ar_obs['2'] );
        $this->expUncertityPlus         = $this->cleanData( $ar_obs['4'] );
        $this->expUncertityMinus        = $this->cleanData( $ar_obs['5'] );
        $this->thUncertity              = $this->cleanData( $ar_obs['6'] );
        $this->expUncertityPlusDefault  = $this->cleanData( $ar_obs['4'] );
        $this->expUncertityMinusDefault = $this->cleanData( $ar_obs['5'] );
        $this->thUncertityDefault       = $this->cleanData( $ar_obs['6'] );
      }
      else {
        throw new \Exception('observable does not exist :: observableInput can not be initialized');
      }
    }

    private function cleanData($data) {
      if( $data = preg_replace('/pi/', pi(), $data) ) {
          return $data;
      }
    }

    private function findObservableInputLine($path) {
      if ($path=='') {
        throw new \Exception('path file not defined :: observableInput can not be initialized');
      }

      $data = file_get_contents($path) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);

      $new_line = "^\n$" ;
      $observablePattern =  '/'.preg_quote( $this->getName(), '/' ).'/';
      // info obs
      $tmp_ar_obs = array();

      // recherche des elements de l observable
      foreach($lines as $line) {
        if( ! preg_match("/$new_line/", $line) ) {
          if( preg_match($observablePattern, $line) ) {
            return explode(';',$line);
          }
        }
      }
      return false;
    }

    public function createAssociatedElement($datacard)
    {
      return $this->getParameterForOneObservable( $datacard );
    }

    private function getParameterForOneObservable($scenarioPath){
      $data = file_get_contents($scenarioPath) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);

      $new_line = "^\n$" ;
      $observablePattern =  '/'.preg_quote( $this->getName(), '/' ).'/';
      // info obs
      $tmp_ar_obs = array();
      // params de l obs
      $tmp_ar_obsParam = array();
      // info param
      $tmp_ar_param = array();
      // array des param a retourner
      $tmp_ar_params = array();

      // recherche des params de l observable
      foreach($lines as $line) {
        if( ! preg_match("/$new_line/", $line) ) {
          if( preg_match($observablePattern, $line) ) {
            $tmp_ar_obs = explode(';',$line);
            # les parametres associes a une observable sont le 6eme item dans le fichier :index=3
            $tmp_ar_obsParam = explode(',',$tmp_ar_obs['7']);
            break;
          }
        }
      }
      $type='';

      // construction des params de l observable
      if( count($tmp_ar_obsParam) > 0) {
        foreach($tmp_ar_obsParam as $param) {

          $paramPattern =  '/^'.preg_quote( $param, '/' ).'$/';

          foreach($lines as $line) {
            if( ! preg_match("/$new_line/", $line) ) {
              if( preg_match('/# parameter/', $line) ) {
                  $type='parameter';
              }
              elseif( $type==='parameter' ) {
                $tmp_ar_param = explode(';',$line);
                if( preg_match($paramPattern, preg_quote($tmp_ar_param['0']) ) ) {
                  $tmp_obj_param = new ParameterInput($this, $tmp_ar_param['0'], $tmp_ar_param['1'], $tmp_ar_param['2'], $tmp_ar_param['3'], $tmp_ar_param['4'], $tmp_ar_param['5'],$tmp_ar_param['6'] ) ;

                  array_push($tmp_ar_params, $tmp_obj_param );
                  break;
                }
              }
            }
          }
        }
      } else  {
        die("Pas de parametres pour l'observable : $this->getName()");
      }
      return $tmp_ar_params;
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
     * Set name
     *
     * @param string $name
     * @return ObservableInput
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

    /**
     * Set defaultInput
     *
     * @param float $defaultInput
     * @return ObservableInput
     */
    public function setDefaultInput($defaultInput)
    {
        $this->defaultInput = $defaultInput;

        return $this;
    }

    /**
     * Get defaultInput
     *
     * @return float
     */
    public function getDefaultInput()
    {
        return $this->defaultInput;
    }

    /**
     * Set allowedRangeMax
     *
     * @param float $allowedRangeMax
     * @return ObservableInput
     */
    public function setAllowedRangeMax($allowedRangeMax)
    {
        $this->allowedRangeMax = $allowedRangeMax;

        return $this;
    }

    /**
     * Get allowedRangeMax
     *
     * @return float
     */
    public function getAllowedRangeMax()
    {
        return $this->allowedRangeMax;
    }

    /**
     * Set allowedRangeMin
     *
     * @param float $allowedRangeMin
     * @return ObservableInput
     */
    public function setAllowedRangeMin($allowedRangeMin)
    {
        $this->allowedRangeMin = $allowedRangeMin;

        return $this;
    }

    /**
     * Get allowedRangeMin
     *
     * @return float
     */
    public function getAllowedRangeMin()
    {
        return $this->allowedRangeMin;
    }

    /**
     * Set analyse
     *
     * @param \CKM\AppBundle\Entity\Analysis $analyse
     * @return ObservableInput
     */
    public function setAnalyse(\CKM\AppBundle\Entity\Analysis $analyse)
    {
        $this->analyse = $analyse;

        return $this;
    }

    /**
     * Get analyse
     *
     * @return \CKM\AppBundle\Entity\Analysis
     */
    public function getAnalyse()
    {
        return $this->analyse;
    }

    /**
     * Add parameterInputs
     *
     * @param \CKM\AppBundle\Entity\ParameterInput $parameterInputs
     * @return ObservableInput
     */
    public function addParameterInput(\CKM\AppBundle\Entity\ParameterInput $parameterInputs)
    {
        $this->parameterInputs[] = $parameterInputs;
        $parameterInputs->addObservableInput($this);

        return $this;
    }

    /**
     * Remove parameterInputs
     *
     * @param \CKM\AppBundle\Entity\ParameterInput $parameterInputs
     */
    public function removeParameterInput(\CKM\AppBundle\Entity\ParameterInput $parameterInputs)
    {
        $this->parameterInputs->removeElement($parameterInputs);
    }

    /**
     * Get parameterInputs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParameterInputs()
    {
        return $this->parameterInputs;
    }

    /**
     * Set defaultValue
     *
     * @param float $defaultValue
     * @return ObservableInput
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get defaultValue
     *
     * @return float
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return ObservableInput
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set thUncertity
     *
     * @param float $thUncertity
     * @return ObservableInput
     */
    public function setThUncertity($thUncertity)
    {
        $this->thUncertity = $thUncertity;

        return $this;
    }

    /**
     * Get thUncertity
     *
     * @return float
     */
    public function getThUncertity()
    {
        return $this->thUncertity;
    }

    /**
     * Set expUncertityDefault
     *
     * @param float $expUncertityDefault
     * @return ObservableInput
     */
    public function setExpUncertityDefault($expUncertityDefault)
    {
        $this->expUncertityDefault = $expUncertityDefault;

        return $this;
    }

    /**
     * Get expUncertityDefault
     *
     * @return float
     */
    public function getExpUncertityDefault()
    {
        return $this->expUncertityDefault;
    }

    /**
     * Set thUncertityDefault
     *
     * @param float $thUncertityDefault
     * @return ObservableInput
     */
    public function setThUncertityDefault($thUncertityDefault)
    {
        $this->thUncertityDefault = $thUncertityDefault;

        return $this;
    }

    /**
     * Get thUncertityDefault
     *
     * @return float
     */
    public function getThUncertityDefault()
    {
        return $this->thUncertityDefault;
    }

    /**
     * Set expUncertityPlus
     *
     * @param float $expUncertityPlus
     * @return ObservableInput
     */
    public function setExpUncertityPlus($expUncertityPlus)
    {
        $this->expUncertityPlus = $expUncertityPlus;

        return $this;
    }

    /**
     * Get expUncertityPlus
     *
     * @return float
     */
    public function getExpUncertityPlus()
    {
        return $this->expUncertityPlus;
    }

    /**
     * Set expUncertityMinus
     *
     * @param float $expUncertityMinus
     * @return ObservableInput
     */
    public function setExpUncertityMinus($expUncertityMinus)
    {
        $this->expUncertityMinus = $expUncertityMinus;

        return $this;
    }

    /**
     * Get expUncertityMinus
     *
     * @return float
     */
    public function getExpUncertityMinus()
    {
        return $this->expUncertityMinus;
    }

    /**
     * Set expUncertityPlusDefault
     *
     * @param float $expUncertityPlusDefault
     * @return ObservableInput
     */
    public function setExpUncertityPlusDefault($expUncertityPlusDefault)
    {
        $this->expUncertityPlusDefault = $expUncertityPlusDefault;

        return $this;
    }

    /**
     * Get expUncertityPlusDefault
     *
     * @return float
     */
    public function getExpUncertityPlusDefault()
    {
        return $this->expUncertityPlusDefault;
    }

    /**
     * Set expUncertityMinusDefault
     *
     * @param float $expUncertityMinusDefault
     * @return ObservableInput
     */
    public function setExpUncertityMinusDefault($expUncertityMinusDefault)
    {
        $this->expUncertityMinusDefault = $expUncertityMinusDefault;

        return $this;
    }

    /**
     * Get expUncertityMinusDefault
     *
     * @return float
     */
    public function getExpUncertityMinusDefault()
    {
        return $this->expUncertityMinusDefault;
    }
}
