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
use CKM\AppBundle\Entity\Latex as Latex;
use CKM\AppBundle\Entity\ScenarioDocumentation;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use CKM\AppBundle\Form\ScenarioType;
use CKM\AppBundle\Form\ScenarioListType;
use CKM\AppBundle\Form\DocumentationType;
use CKM\AppBundle\Form\Admin\latexType;

use \Doctrine\ORM\NoResultException;

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
          #print_r( $form->getData()->getName()->first() );
          #echo $form->getData()->getName()->first()->getWebPath();
 #         \Doctrine\Common\Util\Debug::dump($form->getData()->getName()->first());
          echo '<br />';
#die("debug");

          $tmp = $request->request->get($form->getName()) ;

          if( isset( $tmp['name'] ) ) {
            $text = file_get_contents( $form->getData()->getName()->first()->getWebPath() );
            $name = $form->getData()->getName()->first()->getName();
            $nameModel = $form->getData()->getName()->first()->getModel()->getName();
          }
          else {
            $text = 'Please select a datacard';
            $name = 'Error';
          }

          $this->container->get('request')->getSession()->set( 'text', $text );
          $this->container->get('request')->getSession()->set( 'name', $name );
          $this->container->get('request')->getSession()->set( 'model', $nameModel );
        }

        if( $form->get('delete')->isClicked() ) {
          $em = $this->getDoctrine()->getEntityManager();
          #print_r( $form->getData()->getName()->first() );
          $em->remove($form->getData()->getName()->first());
          $em->flush();

          $this->container->get('request')->getSession()->remove( 'text' );
          $this->container->get('request')->getSession()->remove( 'name' );
          $this->container->get('request')->getSession()->remove( 'model' );

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
        $tmp = $request->request->get($form->getName()) ;

        $up = $form['file']->getData();

        $tag = $this->getFirstTag( $up->getPathname() );
        if ($tmp["name"] != $tag) {
          $this->get('session')->getFlashBag()->add(
              'error',
              'Please the tag '.$tag.' inside file must match with the file name '.$tmp['name']
          );
          return $this->render('CKMAppBundle:Administration:addDatacardError.html.twig', array(
            'form1' => $form->createView(),
          ));
        }
        $datacard->setTag($tag);
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

  public function latexDocumentationAction(Request $request) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $em = $this->getDoctrine()->getManager();

    $scenarios = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation();

    $inputWithLatex=array();
    $inputWithoutLatex=array();

    foreach($scenarios as $scenario) {
      # renvoie un tableau de chaine de caractere
      $inputsScenario=$scenario->getInput();
      foreach($inputsScenario as $input) {
        $latex = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Latex')
          ->findOneByName($input);

        if( $latex ) {
          $inputWithLatex[$latex->getName()]=$latex;
        } else {
          if($input!='')
            $inputWithoutLatex[$input]=$input;
        }
      }
    }


    return $this->render('CKMAppBundle:Administration:latexDocumentation.html.twig',
      array(
        'inputWithLatex' => $inputWithLatex,
        'inputWithoutLatex' => $inputWithoutLatex,
      )
    );
  }

  public function analysesAction($page) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $em = $this->getDoctrine()->getManager();

    $analysis = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Analysis')
      ->findAll();

    $max=$this->container->getParameter('max_analysis_per_page');

    $countAnalysis = $em->getRepository('CKMAppBundle:Analysis')
                          ->countAnalysis();

    $analyse = $em->getRepository('CKMAppBundle:Analysis')
                          ->getListAnalysisAdministration($page, $max);

    $pagination = array(
      'page' => $page,
      'route' => 'CKMAppBundle_administration_analyse',
      'pages_count' => ceil($countAnalysis / $max),
      'route_params' => array()
    );

    return $this->render('CKMAppBundle:Administration:analysis.html.twig',
      array(
        'analysis' => $analyse,
        'count'     =>  $countAnalysis,
        'page'=>$page,
        'pagination' => $pagination
      )
    );
  }

  public function datacardDocumentationAction($tab='') {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $em = $this->getDoctrine()->getManager();

    $scenarioDocumented = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation();

    $scenarioNotDocumented = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation(false);

    return $this->render('CKMAppBundle:Administration:datacardDocumentation.html.twig',
      array(
        'scenarioDocumented' => $scenarioDocumented,
        'scenarioNotDocumented' => $scenarioNotDocumented,
        'tab' => $tab,
      )
    );
  }

  public function addDatacardDocumentationAction(Request $request, $display='') {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $datacardDocumentation = new ScenarioDocumentation();
    $em = $this->getDoctrine()->getManager();
    $scenarioNotDocumented = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation(false);

    $listScenarioName = array();
    foreach( $scenarioNotDocumented as $scenario ) {
      $listScenarioName[$scenario->getName()]=$scenario->getName();
    }

    $form = $this->createForm(new DocumentationType($listScenarioName), $datacardDocumentation);
    $explainText='';

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {
        $tmp = $request->request->get($form->getName()) ;

        if( $form->get('display')->isClicked() ) {
            #$form['explain']->setData('totototototo');
            $doc = $this->getDoctrine()
              ->getRepository('CKMAppBundle:ScenarioDocumentation')
              ->findByScenarioCSV($tmp['name']);

            #print_r($doc);die('display');

            $this->get('session')->getFlashBag()->add(
            'explainText',
            $doc
            );

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                                      array()
                  )
          );
        }

        if( $form->get('document')->isClicked() ) {
          $scenario = $this->getDoctrine()
            ->getRepository('CKMAppBundle:Scenario')
            ->findOneByName($tmp['name']);

          #list($observables, $parameters) = $scenario->getInput();
          #$inputs=array_merge($observables, $parameters);
          $inputs = $scenario->getInput();

          $lines = explode("\n", $tmp['explain']);
          $new_line = "^\n$" ;
          $errors = array();
          $inputsFromUser_ar= array();

          $trouve=0;

          foreach($lines as $line) {
            if( ! preg_match("/$new_line/", $line) ) {
              $tmp_ar= array();
              $tmp_ar = explode(';',$line);
              $inputsFromUser_ar[$tmp_ar['0']]=$tmp_ar['1'];
            }
          }

          foreach($inputs as $input) {
            $trouve=0;
            $input = trim($input, ' ');
            if(!empty($input)) {
              foreach($inputsFromUser_ar as $key => $inputFromUser) {
                if($input==$key) {
                  $trouve=1;
                  break;
                }
              }
              if($trouve==0) {
                $errors[]=$input;
              }
            }
          }

          if (count($errors)>0) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Please the input '.rtrim(join(", ",$errors), ', ').' inside file are missing'
            );
            return $this->render('CKMAppBundle:Administration:addDatacardDocumentationError.html.twig', array(
              'form1' => $form->createView(),
            ));
          }

          # suppression des anciennes docs
          foreach($inputs as $input) {
            $docsInput = $this->getDoctrine()
              ->getRepository('CKMAppBundle:ScenarioDocumentation')
              ->findDocByInputAndScenario($tmp['name'], $input);

            foreach ($docsInput as $docInput) {
                $em->remove($docInput);
            }
          }

          $datacardDocumentation_ar = array();
          foreach($inputsFromUser_ar as $key => $inputFromUser) {

            $datacardDocumentation->setExplanation($inputFromUser);
            $datacardDocumentation->setInput($key);
            $datacardDocumentation->setScenario($scenario->getName() );

            $datacardDocumentation_ar[]= clone $datacardDocumentation;
            $em->persist(end($datacardDocumentation_ar));

          }

          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                                      array()
                  )
          );
        }
      }

    }
    return $this->render('CKMAppBundle:Administration:addDatacardDocumentation.html.twig', array(
      'form1' => $form->createView(),
      'explainText' => $explainText,
    ));

  }

  public function editLatexAction($latex) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $request = $this->getRequest();

    $latex = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Latex')
          ->findOneById($latex);

    if (!$latex) {
      throw $this->createNotFoundException('Latex not exist');
    }

    $form = $this->createForm(new latexType, $latex);

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->persist( $latex );
        $em->flush();

        return $this->redirect(
                $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                    array()
                )
        );
      }
    }

    return $this->render('CKMAppBundle:Administration:editLatex.html.twig', array(
      'form' => $form->createView(),
    ));
  }

  public function addLatexAction() {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $request = $this->getRequest();

    $latex = new Latex();

    $form = $this->createForm(new latexType, $latex);

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();

        $em->persist( $latex );
        $em->flush();

        return $this->redirect(
                $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                    array()
                )
        );
      }
    }

    return $this->render('CKMAppBundle:Administration:editLatex.html.twig', array(
      'form' => $form->createView(),
    ));
  }

  public function deleteAnalysisAction($analyse) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }
    $analyse = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Analysis')
      ->findOneById($analyse);

    if (!$analyse) {
      throw $this->createNotFoundException('analyse not exist');
    }

    $em = $this->getDoctrine()->getEntityManager();
    $tmp = $analyse->getId();

    try {
      if($analyse->getStatus()<2 ) {
        $this->get('CKM.services.analysisManager')->removeAnalysis($analyse);
        $this->get('session')->getFlashBag()->add(
          'information',
          'Analysis '.$tmp.' deleted with success'
        );
      } else {
        $this->get('session')->getFlashBag()->add(
          'information',
          'Analysis '.$tmp.' can not be deleted cause of it status'
        );
      }

      return $this->redirect(
            $this->generateUrl('CKMAppBundle_administration_analyse',
                                array('page' => 1 )
            )
      );
    } catch (\Exception $e) {
        throw new \Exception('Analysis can not be deleted');
    }

  }

  public function switchIsDocumentedAction($scenario) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }
    $scenario = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Scenario')
      ->findOneById($scenario);

    if (!$scenario) {
      throw $this->createNotFoundException('scenario not exist');
    }

    $request = $this->getRequest();
    $form = $this->createFormBuilder($scenario)
            ->add('token', 'hidden',array(
                'mapped'           => false,
            ))
            ->add('switch',
                  'submit',
                    array(
                        'attr' => array('class' => 'form-control btn btn-default'),
                        )
                  )
            ->getForm();

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();

        if($scenario->getIsDocumented()) {
          $scenario->setIsDocumented(false);
        } else {
          $scenario->setIsDocumented(true);
        }
        $em->persist( $scenario );
        $em->flush();
        return $this->redirect(
                $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                    array()
                )
        );
      }

    }
    return $this->render('CKMAppBundle:Administration:default.html.twig', array(
      'form' => $form->createView(),
      'route'=>'CKMAppBundle_administration_datacard_swith_documentation',
      'param'=>'scenario',
      'value'=>$scenario->getId(),
    ));
  }

  public function deleteLatexAction($latex) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }
    $request = $this->getRequest();

    $latex = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Latex')
          ->findOneById($latex);

    if (!$latex) {
      throw $this->createNotFoundException('Latex not exist');
    }

    $tmp=$latex->getName();
    $em = $this->getDoctrine()->getEntityManager();

    try {
        $em->remove($latex);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
        'success',
        'Latex '.$tmp.' deleted with success'
        );
    } catch (\Exception $e) {
    #} catch (\Doctrine\ORM\ORMException $e) {
        $this->get('session')->getFlashBag()->add(
            'danger',
            'Impossible to delete '.$tmp.' cause it is still in use in one analysis : '.$e->getMessage()
        );
    }

    return $this->redirect(
          $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                              array('tab' => 'latex')
          )
    );
  }

  private function getFirstTag($file)
  {
    $data = file_get_contents( $file ) or die("fichier non trouv&eacute;");
    $lines = explode("\n", $data);

    # stop reading in line 2
    $i=0;
    $tag='';
    foreach($lines as $line) {
      $tmp_ar = explode(';',$line);
      $tag = $tmp_ar[1];
      if ($i > 2) {
        break;
      }
      $i++;
    }

    return $tag;
  }
  private function errorForm($typeSession, $errorMsg, $template, $param ) {
      $this->get('session')->getFlashBag()->add(
          $typeSession,
          $errorMsg
      );

    return $this->redirect(
            $this->generateUrl($template,
                               $param
            )
    );
  }
}
