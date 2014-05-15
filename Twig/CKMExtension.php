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
            'targetsElement'  => new \Twig_Function_Method($this, 'getTargetsElement'),
            'targets'  => new \Twig_Function_Method($this, 'getTargets'),
            'scenario' => new \Twig_Function_Method($this, 'getScenario'),
            'latex' => new \Twig_Function_Method($this, 'getLatexLike'),
            'latexTransform' => new \Twig_Function_Method($this, 'getLatexTransform'),
            'getIsTargets' => new \Twig_Function_Method($this, 'getIsTargets'),
        );
    }

    public function getLatexTransform($name){
      return '\\'.$name;
    }

    public function getLatexLike($name){
      return '\('.$name.'\)';
    }

    public function getTargetsElement($analyse)
    {
      $liste_targetElement = $this->em->getRepository('CKMAppBundle:ElementTarget')
                          ->findByAnalyse($analyse);
      return $liste_targetElement;
    }

    public function getTargets($analyse)
    {
      $parameterElement = $this->em->getRepository('CKMAppBundle:ParameterTarget')
                          ->findByAnalyse($analyse);

      $observableElement = $this->em->getRepository('CKMAppBundle:ObservableTarget')
                          ->findByAnalyse($analyse);

      return array_merge($observableElement,$parameterElement);
    }

    public function getIsTargets($analyse)
    {
      $targets = $this->em->getRepository('CKMAppBundle:Input')
                          ->findTargetByAnalysis($analyse);

      return $targets;
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