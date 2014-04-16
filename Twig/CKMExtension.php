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
            'targets'  => new \Twig_Function_Method($this, 'getTargets'),
            'scenario' => new \Twig_Function_Method($this, 'getScenario'),
        );
    }

    public function getTargets($analyse)
    {
      $liste_targetElement = $this->em->getRepository('CKMAppBundle:ElementTarget')
                          ->findByAnalyse($analyse);
      return $liste_targetElement;
    }

    public function getScenario($analyse)
    {
      $scenario = $this->em->getRepository('CKMAppBundle:Scenario')
                          ->findOneById( $analyse->getScenario() );

echo '<pre>';
      \Doctrine\Common\Util\Debug::dump($analyse);
      \Doctrine\Common\Util\Debug::dump($scenario);
echo '</pre>';
      die('debbug');
      return $scenario->getName();
    }

    public function getName()
    {
        return 'ckm_twig_extension';
    }
}