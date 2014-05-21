<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParameterInput
 *
 * @ORM\Table(name="parameter")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ParameterRepository")
 */
class Parameter extends Input
{
    /**
     * @ORM\ManyToMany(targetEntity="CKM\AppBundle\Entity\Observable", mappedBy="parameters", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $observables;

    public function __construct($analyse, $name='', $path='',  $defaultValue=0, $allowedRangeMin=0, $allowedRangeMax=0, $expUncertityDefault=0, $thUncertityDefault=0) {
        $this->observables = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct($analyse, $name, $path, $defaultValue, $allowedRangeMin, $allowedRangeMax, $expUncertityDefault, $thUncertityDefault);
    }


    public function __clone() {
      if ( parent::getId() ) {
          parent::setId(null);

      }
#die('debbug clone Param');
    }


    /**
     * Add observables
     *
     * @param \CKM\AppBundle\Entity\Observable $observables
     * @return Parameter
     */
    public function addObservable(\CKM\AppBundle\Entity\Observable $observables)
    {
        $this->observables[] = $observables;

        return $this;
    }

    /**
     * Remove observables
     *
     * @param \CKM\AppBundle\Entity\Observable $observables
     */
    public function removeObservable(\CKM\AppBundle\Entity\Observable $observables)
    {
        $this->observables->removeElement($observables);
    }

    /**
     * Get observables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObservables()
    {
        return $this->observables;
    }

        /**
     * Get observables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setObservables($observable)
    {
        $this->observables=$observable;
        return $this;
    }
}
