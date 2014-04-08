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
     */
    private $granularity;

    /**
     * @var integer
     *
     * @ORM\Column(name="scan_max", type="integer")
     */
    private $scanMax;

    /**
     * @var integer
     *
     * @ORM\Column(name="scan_min", type="integer")
     */
    private $scanMin;

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
        $this->datacard = "/home/alclaude/Documents/DEV_PHP/ckm-web2/src/CKM/globalCKMfit_scenario.txt";
        $this->config = "config";
        $this->granularity = 0;
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
    public function setDatacard($datacard)
    {
        $this->datacard = $datacard;

        return $this;
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
}
