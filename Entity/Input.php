<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * Input
 *
 * @ORM\Table(name="input")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\InputRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"parameter" = "Parameter", "observable" = "Observable"})
 */
class Input
{
    /**
     * @ORM\ManyToOne(targetEntity="CKM\AppBundle\Entity\Analysis")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $analyse;

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
     * @ORM\Column(name="exp_uncertity", type="float")
     */
    private $expUncertity;


    /**
     * @var float
     *
     * @ORM\Column(name="th_uncertity", type="float")
     */
    private $thUncertity;

    /**
     * @var float
     *
     * @ORM\Column(name="exp_uncertity_default", type="float")
     */
    private $expUncertityDefault;

    /**
     * @var float
     *
     * @ORM\Column(name="th_uncertity_default", type="float")
     */
    private $thUncertityDefault;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_target", type="boolean")
     */
    private $isTarget=false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_input", type="boolean")
     */
    private $isInput=true;

    /**
     * @var float
     *
     * @ORM\Column(name="scan_max", type="float")
     * @Assert\Type(type="float")
     */
    private $scanMax=0;

    /**
     * @var float
     *
     * @ORM\Column(name="scan_min", type="float")
     */
    private $scanMin=0;

    public function __toString() { return 'Input : '.$this->name; }

    public function __construct($analyse, $name='', $path='',  $defaultValue=0, $allowedRangeMin=0, $allowedRangeMax=0, $expUncertityDefault=0, $thUncertityDefault=0)
    {
      $this->name                    = $name;
      $this->analyse                 = $analyse;
      $this->value                   = $defaultValue;
      $this->defaultValue            = $defaultValue;
      $this->allowedRangeMax         = $allowedRangeMax;
      $this->allowedRangeMin         = $allowedRangeMin;
      $this->expUncertity            = $expUncertityDefault;
      $this->thUncertity             = $thUncertityDefault;
      $this->expUncertityDefault     = $expUncertityDefault;
      $this->thUncertityDefault       = $thUncertityDefault;

      if($path!=='') {
        # observable case
        $this->init($path);
      }
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
        $this->expUncertity        = $this->cleanData( $ar_obs['4'] );
        $this->thUncertity              = $this->cleanData( $ar_obs['5'] );
        $this->expUncertityDefault  = $this->cleanData( $ar_obs['4'] );
        $this->thUncertityDefault       = $this->cleanData( $ar_obs['5'] );
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

    public function __clone() {
      if ($this->id) {
          $this->setId(null);
      }
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

    /**
     * Set id
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
     * @return Input
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
     * Set defaultValue
     *
     * @param float $defaultValue
     * @return Input
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
     * @return Input
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
     * Set allowedRangeMax
     *
     * @param float $allowedRangeMax
     * @return Input
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
     * @return Input
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
     * Set thUncertity
     *
     * @param float $thUncertity
     * @return Input
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
     * Set thUncertityDefault
     *
     * @param float $thUncertityDefault
     * @return Input
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
     * Set isTarget
     *
     * @param boolean $isTarget
     * @return Input
     */
    public function setIsTarget($isTarget)
    {
        $this->isTarget = $isTarget;

        return $this;
    }

    /**
     * Get isTarget
     *
     * @return boolean
     */
    public function getIsTarget()
    {
        return $this->isTarget;
    }

    /**
     * Set scanMax
     *
     * @param float $scanMax
     * @return Input
     */
    public function setScanMax($scanMax)
    {
        $this->scanMax = $scanMax;

        return $this;
    }

    /**
     * Get scanMax
     *
     * @return float
     */
    public function getScanMax()
    {
        return $this->scanMax;
    }

    /**
     * Set scanMin
     *
     * @param float $scanMin
     * @return Input
     */
    public function setScanMin($scanMin)
    {
        $this->scanMin = $scanMin;

        return $this;
    }

    /**
     * Get scanMin
     *
     * @return float
     */
    public function getScanMin()
    {
        return $this->scanMin;
    }

    /**
     * Set analyse
     *
     * @param \CKM\AppBundle\Entity\Analysis $analyse
     * @return Input
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
     * Set isInput
     *
     * @param boolean $isInput
     * @return Input
     */
    public function setIsInput($isInput)
    {
        $this->isInput = $isInput;

        return $this;
    }

    /**
     * Get isInput
     *
     * @return boolean
     */
    public function getIsInput()
    {
        return $this->isInput;
    }

    /**
     * Set expUncertity
     *
     * @param float $expUncertity
     * @return Input
     */
    public function setExpUncertity($expUncertity)
    {
        $this->expUncertity = $expUncertity;

        return $this;
    }

    /**
     * Get expUncertity
     *
     * @return float
     */
    public function getExpUncertity()
    {
        return $this->expUncertity;
    }

    /**
     * Set expUncertityDefault
     *
     * @param float $expUncertityDefault
     * @return Input
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
}
