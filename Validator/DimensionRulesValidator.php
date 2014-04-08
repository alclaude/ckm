<?php

// src/CKM/AppBundle/Validator/DimensionRulesValidator.php
namespace CKM\AppBundle\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\HttpFoundation\RequestStack;

class DimensionRulesValidator extends ConstraintValidator
{
    private $em;
    protected $requestStack;

    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function validate($analyse, Constraint $constraint)
    {
        $request = $this->requestStack->getCurrentRequest();

\Doctrine\Common\Util\Debug::dump($request->getSession()->get( 'targetElement' ));

        $count = count( $request->getSession()->get( 'targetElement' ) ) ;
        $request->getSession()->set( 'targetElement2', $request->getSession()->get( 'targetElement' ) );
        #$request->getSession()->remove( 'targetElement' );

        #\Doctrine\Common\Util\Debug::dump($request->getSession()->get( 'targetElement' ));
        #\Doctrine\Common\Util\Debug::dump($analyse);
        #\Doctrine\Common\Util\Debug::dump($this->context);
        #\Doctrine\Common\Util\Debug::dump($request);
        /*
        # on ne peut pas utiliser le em car les target ne sont pas persistés a cet instant. On est obligé d utiliser la session... pour retriever les target. D ou l importation
        # de la Request dans le service
        $query = $this->em->createQuery('SELECT COUNT(e.id) FROM CKM\AppBundle\Entity\ElementTarget e WHERE e.analyse = :analyse');
        $query->setParameter('analyse', $analyse->getId() );
        $count = $query->getSingleScalarResult();
        */

        if ($analyse->getScanConstraint()==1 && $count!==1) {
            $this->context->addViolationAt('scanConstraint', 'Error specific AC scan=1', array(), null);
        }
        if ($analyse->getScanConstraint()==2 && $count!==2) {
            $this->context->addViolationAt('scanConstraint', 'Error specific AC scan=2', array(), null);
        }
        echo 'count : '.$count.' scan : '.$analyse->getScanConstraint();
    }
}