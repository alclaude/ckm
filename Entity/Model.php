<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Model
 *
 * @ORM\Table(name="ckm_model")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ModelRepository")
 * @UniqueEntity("name")
 */
class Model
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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must specify one model", groups={"choice"}
     * )
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enable", type="boolean")
     */
    private $isEnable=true;

    /**
     * @var string
     *
     * @ORM\Column(name="documentation", type="text")
     */
    private $documentation='';

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
     * @return Model
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
     * Set isEnable
     *
     * @param boolean $isEnable
     * @return Model
     */
    public function setIsEnable($isEnable)
    {
        $this->isEnable = $isEnable;

        return $this;
    }

    /**
     * Get isEnable
     *
     * @return boolean
     */
    public function getIsEnable()
    {
        return $this->isEnable;
    }

    /**
     * Set documentation
     *
     * @param string $documentation
     * @return Model
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;

        return $this;
    }

    /**
     * Get documentation
     *
     * @return string 
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }
}
