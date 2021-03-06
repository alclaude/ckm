<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * InputRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InputRepository extends EntityRepository
{
    public function findTargetByAnalysis($analyse)
    {
      $target=true;
      return $this->getEntityManager()
          ->createQuery(
              'SELECT o FROM CKM\AppBundle\Entity\Input o WHERE o.analyse = :analyse and o.isTarget=:target'
          )
          ->setParameters(array(
                'analyse'   => $analyse,
                'target'    => $target,
                )
           )
          ->getResult();
    }
    /**
     * select quantity that are both input and target)
     */
    public function findByInputTargetAnalysis($analyse)
    {
      $input  = true;
      $target = true;
      return $this->getEntityManager()
          ->createQuery(
              'SELECT o FROM CKM\AppBundle\Entity\Input o WHERE o.analyse = :analyse and o.isInput=:input and o.isTarget=:target'
          )
          ->setParameters(array(
                'analyse'   => $analyse,
                'input'     => $input,
                'target'    => $target,
                )
           )
          ->getResult();
    }
    /**
     * select all input
     */
    public function findByAnalysis($analyse)
    {
      return $this->getEntityManager()
          ->createQuery(
              'SELECT o FROM CKM\AppBundle\Entity\Input o WHERE o.analyse = :analyse'
          )
          ->setParameters(array(
                'analyse'   => $analyse,
                )
           )
          ->getResult();
    }
}
