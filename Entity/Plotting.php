<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

use CKM\AppBundle\Validator\DimensionRules;

/**
 * Analysis
 *
 * @ORM\Table(name="ckm_plotting")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\PlottingRepository")
 *
 */
class Plotting
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
     * @ORM\ManyToOne(targetEntity="CKM\AppBundle\Entity\Analysis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $analysis;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=8)
     */
    private $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="path_eps", type="string", length=255)
     */
    private $pathEps;
    
    /**
     * @var string
     *
     * @ORM\Column(name="path_png", type="string", length=255)
     */
    private $pathPng;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_plot", type="integer")
     */
    private $numberOfPlot;
    
    public function __construct()
    {
      $this->nickname = '';
      $this->title    = '';
      $this->pathEps  = '';
      $this->pathPng  = '';
      $this->numberOfPlot = -1;
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
     * Set nickname
     *
     * @param string $nickname
     * @return Plotting
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get nickname
     *
     * @return string 
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Plotting
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set pathEps
     *
     * @param string $pathEps
     * @return Plotting
     */
    public function setPathEps($pathEps)
    {
        $this->pathEps = $pathEps;

        return $this;
    }

    /**
     * Get pathEps
     *
     * @return string 
     */
    public function getPathEps()
    {
        return $this->pathEps;
    }

    /**
     * Set pathPng
     *
     * @param string $pathPng
     * @return Plotting
     */
    public function setPathPng($pathPng)
    {
        $this->pathPng = $pathPng;

        return $this;
    }

    /**
     * Get pathPng
     *
     * @return string 
     */
    public function getPathPng()
    {
        return $this->pathPng;
    }

    /**
     * Set NumberOfPlot
     *
     * @param integer $numberOfPlot
     * @return Plotting
     */
    public function setNumberOfPlot($numberOfPlot)
    {
        $this->numberOfPlot = $numberOfPlot;

        return $this;
    }

    /**
     * Get NumberOfPlot
     *
     * @return integer 
     */
    public function getNumberOfPlot()
    {
        return $this->numberOfPlot;
    }

    /**
     * Set analysis
     *
     * @param \CKM\AppBundle\Entity\Analysis $analysis
     * @return Plotting
     */
    public function setAnalysis(\CKM\AppBundle\Entity\Analysis $analysis)
    {
        $this->analysis = $analysis;

        return $this;
    }

    /**
     * Get analysis
     *
     * @return \CKM\AppBundle\Entity\Analysis 
     */
    public function getAnalysis()
    {
        return $this->analysis;
    }
}
