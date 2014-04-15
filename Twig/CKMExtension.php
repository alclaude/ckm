<?php

// src/Acme/DemoBundle/Twig/AcmeExtension.php
namespace CKM\AppBundle\Twig;

class CKMExtension extends \Twig_Extension
{
    private $em;
    private $conn;

    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
        $this->conn = $em->getConnection();
    }

    public function getFunctions()
    {
        return array(
            'targets' => new \Twig_Function_Method($this, 'getTargets'),
        );
    }

    public function getTargets($analyse)
    {
      $liste_targetElement = $this->em->getRepository('CKMAppBundle:ElementTarget')
                          ->findByAnalyse($analyse);
      return $liste_targetElement;
    }

    public function getName()
    {
        return 'ckm_twig_extension';
    }
}