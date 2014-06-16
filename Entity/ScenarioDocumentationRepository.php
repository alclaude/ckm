<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ScenarioDocumentationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ScenarioDocumentationRepository extends EntityRepository
{
    public function findDocByInputAndScenario($scenarioName='', $inputName='')
    {
      $target=true;
      return $this->getEntityManager()
          ->createQuery(
              'SELECT o FROM CKM\AppBundle\Entity\ScenarioDocumentation o WHERE o.scenario =:scenario and o.input=:input'
          )
          ->setParameters(array(
                'scenario'   => $scenarioName,
                'input'      => $inputName,
                )
           )
          ->getResult();
    }

    public function findDocByScenario($scenarioName='')
    {
      $target=true;
      return $this->getEntityManager()
          ->createQuery(
              'SELECT o FROM CKM\AppBundle\Entity\ScenarioDocumentation o WHERE o.scenario = :scenario'
          )
          ->setParameters(array(
                'scenario'   => $scenarioName,
                )
           )
          ->getResult();
    }
}
