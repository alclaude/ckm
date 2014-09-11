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
use CKM\AppBundle\Entity\Model;
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
use CKM\AppBundle\Form\Admin\modelType;
use CKM\AppBundle\Form\Admin\ScenarioExplainationType;

use \Doctrine\ORM\NoResultException;

class administrationController extends Controller
{

  public function datacardAction(Request $request, $error=false, $tab='') {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $scenarios = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Scenario')
          ->findAll();


    return $this->render('CKMAppBundle:Administration:datacard.html.twig', array(
      'tab'=>$tab,
      'error'=>$error,
      'scenarios'=>$scenarios,
    ));
  }

  public function showScenarioAction($scenario=0) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $scenario = $this->getDoctrine()
            ->getRepository('CKMAppBundle:Scenario')
            ->findOneById($scenario);

    if (!$scenario) {
      throw $this->createNotFoundException('scenario not exist');
    }

    $text = file_get_contents( $scenario->getWebPath() );

    return $this->render('CKMAppBundle:Administration:showScenario.html.twig', array(
      'scenario' => $scenario,
      'text' => $text,
    ));
  }

  public function showModelAction($model=0) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $model = $this->getDoctrine()
            ->getRepository('CKMAppBundle:Model')
            ->findOneById($model);

    if (!$model) {
      throw $this->createNotFoundException('model not exist');
    }

    return $this->render('CKMAppBundle:Administration:showModel.html.twig', array(
      'model' => $model,
    ));
  }

  public function editScenarioAction($scenario=0) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $request = $this->getRequest();

    $scenario = $this->getDoctrine()
            ->getRepository('CKMAppBundle:Scenario')
            ->findOneById($scenario);

    if (!$scenario) {
      throw $this->createNotFoundException('scenario not exist');
    }

    $form = $this->createForm(new ScenarioExplainationType, $scenario);

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->persist( $scenario );
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Scenario '.$scenario->getName().' edited'
        );

        return $this->redirect(
                $this->generateUrl('CKMAppBundle_administration_datacard',
                    array('tab'=>'')
                )
        );
      }
    }

    return $this->render('CKMAppBundle:Administration:editScenario.html.twig', array(
      'form' => $form->createView(),
    ));
  }

  public function deleteScenarioAction($scenario=0) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }
    $request = $this->getRequest();

    $scenario = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Scenario')
          ->findOneById($scenario);

    if (!$scenario) {
      throw $this->createNotFoundException('scenario not exist');
    }

    $tmp=$scenario->getName();
    $em = $this->getDoctrine()->getEntityManager();

    try {
        $em->remove($scenario);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
        'success',
        'scenario '.$tmp.' deleted with success'
        );
    } catch (\Exception $e) {
    #} catch (\Doctrine\ORM\ORMException $e) {
        $this->get('session')->getFlashBag()->add(
            'danger',
            'Impossible to delete '.$tmp.' cause it is still in use in one analysis : '.$e->getMessage()
        );
    }

    return $this->redirect(
          $this->generateUrl('CKMAppBundle_administration_datacard',
                              array('tab' => '')
          )
    );
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
          'success',
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

  public function modelDocumentationAction(Request $request) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    return $this->render('CKMAppBundle:Administration:modelDocumentation.html.twig',
      array(
      )
    );

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

  public function addDatacardDocumentationAction($tab='') {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $em = $this->getDoctrine()->getManager();

    $scenarioDocumented = $this->getDoctrine()
      ->getRepository('CKMAppBundle:Scenario')
      ->findScenarioByDocumentation();

    return $this->render('CKMAppBundle:Administration:addDatacardDocumentation.html.twig',
      array(
        'scenarioDocumented' => $scenarioDocumented,
        'tab' => $tab,
      )
    );
  }

  public function datacardDocumentationAction(Request $request, $display='', $tab='') {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $datacardDocumentation = new ScenarioDocumentation();
    $defaultModel='';$defaultScenario='';

    $em = $this->getDoctrine()->getManager();

    /*
    if($this->container->get('request')->getSession()->get( 'scenarioName') ) {
      $scenarioSelect = $this->getDoctrine()
              ->getRepository('CKMAppBundle:Scenario')
              ->findOneByName($this->container->get('request')->getSession()->get( 'scenarioName'));
      $defaultScenario=$scenarioSelect;
    }
    if($this->container->get('request')->getSession()->get( 'modelName') ) {
      $modelSelect = $this->getDoctrine()
              ->getRepository('CKMAppBundle:Model')
              ->findOneByName($this->container->get('request')->getSession()->get( 'modelName'));
      $defaultModel=$modelSelect;
    }
    */

    $form = $this->createForm(
                  new DocumentationType($this->get('CKM.services.analysisManager')->getModelEnabled(true)
                  //,$defaultModel,
                  //$defaultScenario
                ),
                $datacardDocumentation
            );
    $explainText='';

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);

      if ($form->isValid()) {
        $tmp = $request->request->get($form->getName()) ;

        $this->container->get('request')->getSession()->remove( 'scenarioName' );
        $this->container->get('request')->getSession()->remove( 'modelName' );

        $scenario = $this->getDoctrine()
              ->getRepository('CKMAppBundle:Scenario')
              ->findOneById($tmp['scenario']);

        if( !$scenario ) {
          return $this->errorForm('notice',
            'There is no scenario for this Model, please contact your administrator',
            'CKMAppBundle_administration_datacard_documentation',
            array()
            );
        }

        if( $form->get('display')->isClicked() ) {
            $doc = $this->getDoctrine()
              ->getRepository('CKMAppBundle:ScenarioDocumentation')
              ->findByScenarioCSV($scenario->getId(), $scenario->getName());

            $this->get('session')->getFlashBag()->add(
            'explainText',
            $doc
            );

          $errors = $this->missingInputDocumentation( $scenario->getInput(), $this->csvToArrayDocUser($doc) );

          if (count($errors)>0) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Note that input <strong>'.rtrim(join(", ",$errors), ', ').'</strong> inside '.$scenario->getName().' ('.$scenario->getModel()->getName().') are missing'
            );
          }

          $this->container->get('request')->getSession()->set( 'scenarioName', $scenario->getName() );
          $this->container->get('request')->getSession()->set( 'modelName', $scenario->getModel()->getName() );

          /*return $this->redirect(
                  $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                                      array()
                  )
          );*/
              return $this->render('CKMAppBundle:Administration:datacardDocumentation.html.twig', array(
              'form1' => $form->createView(),
            ));
        }

        if( $form->get('document')->isClicked() ) {
          $inputs = $scenario->getInput();

          $inputsFromUser_ar=$this->csvToArrayDocUser($tmp['explain']);

          $errors = $this->missingInputDocumentation( $inputs, $inputsFromUser_ar );

          if (count($errors)>0) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Note that input <strong>'.rtrim(join(", ",$errors), ', ').'</strong> inside '.$scenario->getName().' ('.$scenario->getModel()->getName().') are missing'
            );
          }

          ## suppression des anciennes docs
          foreach($inputs as $input) {
            $docsInput = $this->getDoctrine()
              ->getRepository('CKMAppBundle:ScenarioDocumentation')
              ->findDocByInputAndScenario($tmp['scenario'], $input);

            foreach ($docsInput as $docInput) {
                $em->remove($docInput);
            }
          }
          # effacer les quantite absentes du scenario
          $docsInput = $this->getDoctrine()
              ->getRepository('CKMAppBundle:ScenarioDocumentation')
              ->removeDocumentationByScenario($tmp['scenario']);
          ##

          $datacardDocumentation_ar = array();
          foreach($inputsFromUser_ar as $key => $inputFromUser) {

            $datacardDocumentation->setExplanation($inputFromUser);
            $datacardDocumentation->setInput($key);
            $datacardDocumentation->setScenario($scenario );

            $datacardDocumentation_ar[]= clone $datacardDocumentation;
            $em->persist(end($datacardDocumentation_ar));

          }

          $em->flush();

          /*return $this->redirect(
                  $this->generateUrl('CKMAppBundle_administration_datacard_documentation',
                                      array()
                  )
          );*/
            return $this->render('CKMAppBundle:Administration:datacardDocumentation.html.twig', array(
              'form1' => $form->createView(),
            ));
        }
      }
      else {
            return $this->errorForm('notice',
              'There is no scenario',
              'CKMAppBundle_administration_datacard_documentation',
              array()
            );
      }

    }
    return $this->render('CKMAppBundle:Administration:datacardDocumentation.html.twig', array(
      'form1' => $form->createView(),
      'explainText' => $explainText,
      'tab' => $tab,
    ));

  }

  private function csvToArrayDocUser($lines) {
    if(! is_array($lines) ) $lines = explode("\n", $lines);

    $new_line = "^\n$" ;

    $inputsFromUser_ar= array();
    $CSVerror= array();

    foreach($lines as $key => $line) {

      if( ! preg_match("/$new_line/", $line) ) {
        $tmp_ar= array();
        $tmp_ar = explode(';',$line);
        try {
          if(isset($tmp_ar['1']) && isset($tmp_ar['0']) ) {
            $inputsFromUser_ar[$tmp_ar['0']]=$tmp_ar['1'];
          }
          elseif($key>0) {
            $CSVerror[]=$key;
            #return array();
          }
        } catch (\Exception $e) {
          throw new \Exception('Something wrong with the CSV Format');
        }
      }
    }

    if(count($CSVerror)>0) {
      $this->get('session')->getFlashBag()->add(
      'notice',
      'Something wrong with the CSV Format at Carriage Return '.join(", ",$CSVerror)
      );
    }

    return $inputsFromUser_ar;
  }

  private function missingInputDocumentation($inputs, $inputsFromUser_ar) {
    $trouve=0;
    $errors = array();

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

    return $errors;
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
                    array('tab'=>'latex')
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
                    array('tab'=>'latex')
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

  public function modelAction(Request $request) {
    $models = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Model')
          ->findAll();

    return $this->render('CKMAppBundle:Administration:model.html.twig',
      array(
        'models'  => $models,
      )
    );
  }

  public function editModelAction($model=0) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }

    $request = $this->getRequest();

    if ($model==0) {
      $model = new Model();
    }
    else {
      $model = $this->getDoctrine()
            ->getRepository('CKMAppBundle:Model')
            ->findOneById($model);
    }
    if (!$model) {
      throw $this->createNotFoundException('model not exist');
    }

    $form = $this->createForm(new modelType, $model);

    if ($request->getMethod() == 'POST') {
      $form->handleRequest($request);
      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->persist( $model );
        $em->flush();

        return $this->redirect(
                $this->generateUrl('CKMAppBundle_administration_datacard',
                    array('tab'=>'model')
                )
        );
      }
    }

    return $this->render('CKMAppBundle:Administration:editModel.html.twig', array(
      'form' => $form->createView(),
    ));

  }

  public function deleteModelAction($model=0) {
    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
      throw new AccessDeniedHttpException('no credentials for this action');
    }
    $request = $this->getRequest();

    $model = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Model')
          ->findOneById($model);

    if (!$model) {
      throw $this->createNotFoundException('model not exist');
    }

    $tmp=$model->getName();
    $em = $this->getDoctrine()->getEntityManager();

    try {
        $em->remove($model);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
        'success',
        'model '.$tmp.' deleted with success'
        );
    } catch (\Exception $e) {
    #} catch (\Doctrine\ORM\ORMException $e) {
        $this->get('session')->getFlashBag()->add(
            'danger',
            'Impossible to delete '.$tmp.' cause it is still in use in one analysis : '.$e->getMessage()
        );
    }

    return $this->redirect(
          $this->generateUrl('CKMAppBundle_administration_datacard',
                              array('tab' => 'model')
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
