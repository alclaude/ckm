<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
 * ScenarioDocumentation
 *
 * @ORM\Table(name="ckm_scenario_documentation")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ScenarioDocumentationRepository")
 */
class ScenarioDocumentation
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
     * @var string
     *
     * @ORM\Column(name="scenario", type="string", length=255)
     */
    private $scenario;

    /**
     * @var string
     *
     * @ORM\Column(name="input", type="string", length=255)
     */
    private $input;

    /**
     * @var string
     *
     * @ORM\Column(name="explanation", type="text")
     */
    private $explanation;

    public function __construct()
    {

    }

    public function __clone() {
      if ($this->id) {
          $this->setId(null);
      }
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
     * Set scenario
     *
     * @param string $scenario
     * @return ScenarioDocumentation
     */
    public function setScenario($scenario)
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
     * Set input
     *
     * @param string $input
     * @return ScenarioDocumentation
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set explanation
     *
     * @param string $explanation
     * @return ScenarioDocumentation
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Get explanation
     *
     * @return string
     */
    public function getExplanation()
    {
        return $this->explanation;
    }
}
