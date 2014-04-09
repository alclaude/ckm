<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElementTarget
 *
 * @ORM\Table(name="element_target")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ElementTargetRepository")
 */
class ElementTarget
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
     * @ORM\Column(name="default_input", type="float")
     *
     */
    private $defaultInput;

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
     * @ORM\Column(name="scan_max", type="float")
     * @Assert\Type(type="float")
     */
    private $scanMax;

    /**
     * @var float
     *
     * @ORM\Column(name="scan_min", type="float")
     */
    private $scanMin;

    public function __construct($analyse, $name='', $defaultInput=0, $allowedRangeMax=0, $allowedRangeMin=0)
    {
      $this->name            = $name;
      $this->defaultInput    = $defaultInput;
      $this->allowedRangeMax = $allowedRangeMax;
      $this->allowedRangeMin = $allowedRangeMin;
      $this->analyse         = $analyse;
      $this->scanMax         = 0.0;
      $this->scanMin         = 0.0;
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
     * @return ElementTarget
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
     * @return ElementTarget
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
     * @return ElementTarget
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
     * @return ElementTarget
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
     * @return ElementTarget
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
     * Set scanMax
     *
     * @param float $scanMax
     * @return ElementTarget
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
     * @return ElementTarget
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
}
