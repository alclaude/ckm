<?php
// src/Blogger/AppBundle/Controller/DocumentationController.php
namespace CKM\AppBundle\Controller;

use CKM\AppBundle\Entity\Analysis;
use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;
use CKM\AppBundle\Entity\Input;
use CKM\AppBundle\Entity\Scenario;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DocumentationController extends Controller
{
    public function seeDatacardInputDocumentationAction(Request $request, $scenario=0, $input=0, $tab='') {
      $em = $this->getDoctrine()
                 ->getManager();

      $scenario = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Scenario')
        //->findOneByName($scenario);
        ->findOneById($scenario);

      if (!$scenario) {
        throw $this->createNotFoundException('scenario not exist');
      }

      if($input==0) {
        $docs = $this->getDoctrine()
          ->getRepository('CKMAppBundle:ScenarioDocumentation')
          ->findDocByScenario($scenario );
      }
      else {
        $input = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Input')
          ->findOneById($input);

        if (!$input) {
          throw $this->createNotFoundException('input not exist');
        }

        $docs = $this->getDoctrine()
          ->getRepository('CKMAppBundle:ScenarioDocumentation')
          ->findDocByInputAndScenario($scenario->getId(), $input->getName() );
      }

#\Doctrine\Common\Util\Debug::dump($scenario);die('debbug');

      return $this->render('CKMAppBundle:Documentation:index.html.twig', array(
        'model'    => $scenario->getModel(),
        'scenario' => $scenario,
        'docs'     => $docs,
        'tab'      => $tab,
      ));
    }
}
