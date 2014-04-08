<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParameterInput
 *
 * @ORM\Table(name="parameter_input")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ParameterInputRepository")
 */
class ParameterInput
{
    /**
     * @ORM\ManyToMany(targetEntity="CKM\AppBundle\Entity\ObservableInput", mappedBy="parameterInputs", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $observableInputs;

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

    public function __toString() { return 'ParameterInput : '.$this->name; }

    public function __construct($observableInput, $name='', $defaultValue=0, $allowedRangeMin=0, $allowedRangeMax=0, $expUncertityDefault=0, $thUncertityDefault=0)
    {
      $this->name                = $name;
      $this->value               = $defaultValue;
      $this->defaultValue        = $defaultValue;
      $this->allowedRangeMax     = $allowedRangeMax;
      $this->allowedRangeMin     = $allowedRangeMin;
      $this->expUncertity        = $expUncertityDefault;
      $this->thUncertity         = $thUncertityDefault;
      $this->expUncertityDefault = $expUncertityDefault;
      $this->thUncertityDefault  = $thUncertityDefault;

      #$this->observableInput = $observableInput;
      $this->observableInputs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ParameterInput
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
     * @return ParameterInput
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
     * @return ParameterInput
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
     * @return ParameterInput
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
     * Set observableInput
     *
     * @param \CKM\AppBundle\Entity\ObservableInput $observableInput
     * @return ParameterInput
     */
    public function setObservableInput(\CKM\AppBundle\Entity\ObservableInput $observableInput)
    {
        #$this->observableInput = $observableInput;

        return $this;
    }

    /**
     * Get observableInput
     *
     * @return \CKM\AppBundle\Entity\ObservableInput
     */
    public function getObservableInput()
    {
        return $this->observableInput;
    }

    /**
     * Add observableInputs
     *
     * @param \CKM\AppBundle\Entity\ObservableInput $observableInputs
     * @return ParameterInput
     */
    public function addObservableInput(\CKM\AppBundle\Entity\ObservableInput $observableInputs)
    {
        $this->observableInputs[] = $observableInputs;

        return $this;
    }

    /**
     * Remove observableInputs
     *
     * @param \CKM\AppBundle\Entity\ObservableInput $observableInputs
     */
    public function removeObservableInput(\CKM\AppBundle\Entity\ObservableInput $observableInputs)
    {
        $this->observableInputs->removeElement($observableInputs);
    }

    /**
     * Get observableInputs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObservableInputs()
    {
        return $this->observableInputs;
    }

    /**
     * Set defaultValue
     *
     * @param float $defaultValue
     * @return ParameterInput
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
     * @return ParameterInput
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
     * Set expUncertity
     *
     * @param float $expUncertity
     * @return ParameterInput
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
     * Set thUncertity
     *
     * @param float $thUncertity
     * @return ParameterInput
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
     * @return ParameterInput
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
     * @return ParameterInput
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
}
