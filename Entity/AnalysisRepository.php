<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * AnalysisRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalysisRepository extends EntityRepository
{
    public function countTargetByAnalyse($id)
    {
      $em = $this->getEntityManager();
      $query = $em->createQuery('SELECT COUNT(e.id) FROM CKM\AppBundle\Entity\ElementTarget e WHERE e.analyse_id = :analyse');
      $query->setParameter('analyse', $id );
      $count = $query->getSingleScalarResult();
      return $count;
    }

    public function countAnalysisByUser($analyse, $status)
    {
      $em = $this->getEntityManager();
      $query = $em->createQuery('SELECT COUNT(a) FROM CKM\AppBundle\Entity\Analysis a WHERE a.user = :user and a.status > :status');
      $query->setParameters(array(
                                  'user'   => $analyse,
                                  'status' => $status,
                                 )
                            );
      $count = $query->getSingleScalarResult();
      return $count;
    }

    public function findAnalysisByUserAndStatus($analyse, $status)
    {
      return $this->getEntityManager()
          ->createQuery(
              'SELECT a FROM CKM\AppBundle\Entity\Analysis a WHERE a.user = :user and a.status > :status'
          )
          ->setParameters(array(
                'user'   => $analyse,
                'status' => $status,
                )
           )
          ->getResult();
    }

    public function findObservableByAnalysis($analyse)
    {
      return $this->getEntityManager()
          ->createQuery(
              'SELECT o FROM CKM\AppBundle\Entity\ObservableInput o WHERE o.analyse = :analyse'
          )
          ->setParameters(array(
                'analyse'   => $analyse,
                )
           )
          ->getResult();
    }

    /**
      * Get the paginated list of published articles
      *
      * @param int $page
      * @param int $maxperpage
      * @param string $sortby
      * @return Paginator
      */
    public function getList($page=1, $maxperpage=10, $analyse, $status)
    {
      $q = $this->getEntityManager()
          ->createQuery(
              'SELECT a FROM CKM\AppBundle\Entity\Analysis a WHERE a.user = :user and a.status > :status'
          )
          ->setParameters(array(
                'user'   => $analyse,
                'status' => $status,
                )
           );

      $q->setFirstResult(($page-1) * $maxperpage)
      ->setMaxResults($maxperpage);

      return new Paginator($q);
    }
}
