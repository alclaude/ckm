<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use CKM\AppBundle\Entity\Latex;

/**
 * Latex
 *
 * @ORM\Table(name="ckm_latex")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\LatexRepository")
 */
class Latex
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="latex", type="string", length=255)
     */
    private $latex;


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
     * @return Latex
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
     * Set latex
     *
     * @param string $latex
     * @return Latex
     */
    public function setLatex($latex)
    {
        $this->latex = $latex;

        return $this;
    }

    /**
     * Get latex
     *
     * @return string
     */
    public function getLatex()
    {
      return $this->latex;
    }
}
