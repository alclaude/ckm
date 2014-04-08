<?php
// src/Blogger/AppBundle/Controller/AdministrationController.php
namespace CKM\AppBundle\Controller;

use CKM\AppBundle\Entity\Analysis;
use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;
use CKM\AppBundle\Entity\ObservableInput;
use CKM\AppBundle\Entity\ParameterInput;
use CKM\AppBundle\Entity\ElementTarget;
use CKM\AppBundle\Entity\Scenario;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use CKM\AppBundle\Form\ScenarioType;
use CKM\AppBundle\Form\ScenarioListType;

class administrationController extends Controller
{

  public function datacardAction(Request $request, $error=false) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }
    $datacard = new Scenario();
    $form = $this->createForm(new ScenarioListType, $datacard);

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {

        if( $form->get('display')->isClicked() ) {
          echo '<pre>';
          #\Doctrine\Common\Util\Debug::dump($request->request->get($form->getName())) ;
          print_r( $form->getData()->getName()->first() );
          #echo $form->getData()->getName()->first()->getWebPath();
          echo '<br />';


          $tmp = $request->request->get($form->getName()) ;

          if( isset( $tmp['name'] ) ) {
            $text = file_get_contents( $form->getData()->getName()->first()->getWebPath() );
            $name = $form->getData()->getName()->first()->getName();
          }
          else {
            $text = 'Please select a datacard';
            $name = 'Error';
          }

          $this->container->get('request')->getSession()->set( 'text', $text );
          $this->container->get('request')->getSession()->set( 'name', $name );
        }

        if( $form->get('delete')->isClicked() ) {
          $em = $this->getDoctrine()->getEntityManager();
          #print_r( $form->getData()->getName()->first() );
          $em->remove($form->getData()->getName()->first());
          $em->flush();

          $this->container->get('request')->getSession()->remove( 'text' );
          $this->container->get('request')->getSession()->remove( 'name' );

          $this->get('session')->getFlashBag()->add(
              'suppress',
              'datacard File removed
              '
          );
          #return new Response("btn delete cliqué" );
        }

        return $this->redirect(
                $this->generateUrl('CKMAppBundle_administration_datacard',
                    array()
                )
        );
      }
    }
    return $this->render('CKMAppBundle:Administration:datacard.html.twig', array(
      'form' => $form->createView(),
    ));
  }

  public function addDatacardAction(Request $request) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $datacard = new Scenario();
    $form = $this->createForm(new ScenarioType, $datacard);

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {

      $em = $this->getDoctrine()->getManager();

      $em->persist($datacard);
      $em->flush();

      $this->get('session')->getFlashBag()->add(
        'notice',
        'File upload '
      );

      return $this->redirect(
              $this->generateUrl('CKMAppBundle_administration_datacard',
                                  array()
              )
      );
      }
      else{
          $this->get('session')->getFlashBag()->add(
              'error',
              'Please correct and fill in the form
              '
          );
          return $this->render('CKMAppBundle:Administration:addDatacardError.html.twig', array(
            'form1' => $form->createView(),
          ));
      }
    }
    return $this->render('CKMAppBundle:Administration:createDatacard.html.twig', array(
      'form1' => $form->createView(),
    ));

    #return $this->render('CKMAppBundle:Administration:datacard.html.twig', array(
    #));
  }
}