<?php
namespace CKM\AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
/**
 * ModelRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TagInputRepository extends EntityRepository
{
    public function findTagInputByActivated()
    {
      $tagInputs=$this->getEntityManager()
          ->createQuery(
              'SELECT ti FROM CKM\AppBundle\Entity\TagInput ti WHERE ti.isEnable=:isEnable'
          )
          ->setParameters(array(
                          'isEnable'   => 1,
                          )
                        )
          ->getResult();
      return $tagInputs;
      #return count($scenarios)>0 ? $scenarios : array('Sorry no scenario available : contact your administrator');
    }
}
