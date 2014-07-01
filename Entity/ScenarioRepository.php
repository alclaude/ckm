<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ScenarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ScenarioRepository extends EntityRepository
{
    public function findScenarioByDocumentation($isDocumented='', $builder=0)
    {
      $query = $this->createQueryBuilder(
                  's'
              );
      if($isDocumented!='')     {
        $query->where("s.isDocumented = :isDocumented")
              ->setParameters(array(
                'isDocumented'   => $isDocumented,
                )
              );
      }
      if($builder!=0) return $query;

      $scenarios = $query->getQuery()->getResult();
      return $scenarios;
      #return count($scenarios)>0 ? $scenarios : array('Sorry no scenario available : contact your administrator');
    }
}
