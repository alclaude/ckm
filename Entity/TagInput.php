<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TagInput
 *
 * @ORM\Table(name="ckm_tag_input")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\TagInputRepository")
 * @UniqueEntity("name")
 */
class TagInput
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
     *      minMessage = "You must specify one tag", groups={"choice"}
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
     * @return Tag
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
     * @return Tag
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
}
