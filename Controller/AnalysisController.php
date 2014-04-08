<?php
// src/Blogger/AppBundle/Controller/AnalysisController.php
namespace CKM\AppBundle\Controller;

use CKM\AppBundle\Entity\Analysis;
use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;
use CKM\AppBundle\Entity\ObservableInput;
use CKM\AppBundle\Entity\ParameterInput;
use CKM\AppBundle\Entity\ElementTarget;

use CKM\AppBundle\Form\AnalysisType;
use CKM\AppBundle\Form\ObservableType;
use CKM\AppBundle\Form\AnalysisSourceUnitType;
use CKM\AppBundle\Form\ObservableInputType;
use CKM\AppBundle\Form\ParameterInputType;
use CKM\AppBundle\Form\AnalysisPropertiesType;

use CKM\AppBundle\Form\Analyse\AnalysisStep1Type;
use CKM\AppBundle\Form\Analyse\AnalysisStep2Type;
use CKM\AppBundle\Form\Analyse\AnalysisStep3Type;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AnalysisController extends Controller
{
    public function createAnalyseStep1Action(Request $request) {
      $this->isGranted('ROLE_ANALYSIS');

      $analyse = new Analysis();
      $form = $this->createForm(new AnalysisStep1Type,  $analyse);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $analyse->setUser( $this->get('security.context')->getToken()->getUser() );

          $em->persist( $analyse );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_step_2',
                                      array('analyse' => $analyse->getId(), 'step' => 2 )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:createAnalysisStep.html.twig', array(
        'form' => $form->createView(),
      ));
    }

    public function createAnalyseStep2Action($analyse=0, $step=2 ) {
      $this->isGranted('ROLE_ANALYSIS');
      $analyse = $this->getAnalysis($analyse);
      $request = $this->getRequest();

      $form = $this->createForm(new AnalysisStep2Type,  $analyse);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);

        if ($form->isValid()) {
          $tmp = $request->request->get($form->getName()) ;
          # gestion des saisies des target
          if ( isset($tmp["targetParameter"]) && isset($tmp["targetObservable"]) ) {
            $element_ar = array_merge( $tmp["targetObservable"], $tmp["targetParameter"] );
          }
          elseif( isset($tmp["targetObservable"]) ) {
            $element_ar = $tmp["targetObservable"];
          }
          elseif( isset($tmp["targetParameter"]) ) {
            $element_ar = $tmp["targetParameter"];
          }
          else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Please fill in the form'
            );
            return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_step_2',
                                      array('analyse' => $analyse->getId(), 'step' => 2 )
                  )
            );
          }
          # validation des contraintes sur les scan et nb d elements target
          $count = count( $element_ar );
          if ( $analyse->getScanConstraint() ==1 && $count!==1) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Please, in a 1D scan you must choose one element'
            );
            return $this->redirect(
                $this->generateUrl('CKMAppBundle_analyse_create_step_2',
                                    array('analyse' => $analyse->getId(), 'step' => 2 )
                )
            );
          }
          if ($analyse->getScanConstraint() ==2 && $count!==2) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Please, in a 2D scan you must choose two items in the boxes'
            );
            return $this->redirect(
                $this->generateUrl('CKMAppBundle_analyse_create_step_2',
                                    array('analyse' => $analyse->getId(), 'step' => 2 )
                )
            );
          }

          $em = $this->getDoctrine()->getManager();
          foreach( $element_ar as $key => $target )
          {
            $targetPersist = new ElementTarget($analyse, $target);
            $em->persist($targetPersist);
          }

          $em->persist( $analyse );
          $em->flush();

          #$analyse = print_r($analyse,true);
          #die('debbug <pre>'.$analyse .'</pre>');

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_step_3',
                                      array('analyse' => $analyse->getId(), 'step' => 3 )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:createAnalysisStep.html.twig', array(
        'form' => $form->createView(),
      ));
    }

    public function createAnalyseStep3Action($analyse=0, $step=3 ) {
      $this->isGranted('ROLE_ANALYSIS');

      if($step==0) {
        $this->removeInput($analyse);
      }

      $analyse = $this->getAnalysis($analyse);
      $request = $this->getRequest();

      $form = $this->createForm(new AnalysisStep3Type,  $analyse);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);

        if ($form->isValid()) {
          $tmp = $request->request->get($form->getName()) ;

          if(!isset($tmp["sourceElement"])) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Please fill in the form'
            );
            return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_step_3',
                                      array('analyse' => $analyse->getId(), 'step' => $step )
                  )
            );
          }

          $em = $this->getDoctrine()->getManager();
          # tous les parametres regroupes des observables
          $all_ar_parameters=array();

          foreach( $tmp["sourceElement"] as $key => $input )
          {
            $inputPersist = new ObservableInput( $analyse, $input, $analyse->getScenario()->getWebPath() );
            $em->persist($inputPersist);

            $parameters = $inputPersist->createAssociatedElement( $analyse->getScenario()->getWebPath() );

            foreach ( $parameters as $parameter ) {
              if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                $all_ar_parameters[$parameter->getName()] = $parameter;
              }
            }

            foreach( $parameters as $key => $parameter ) {
              if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                $inputPersist->addParameterInput($parameters[$key]);
                $em->persist($parameters[$key]);
              }
              else {
                $inputPersist->addParameterInput( $all_ar_parameters[$parameter->getName()] );
              }
            }

          }

          #$analyse = print_r($analyse,true);
          #die('debbug <pre>'.$analyse .'</pre>');

          $em->persist( $analyse );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $analyse->getId(), 'step' => 4 )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:createAnalysisStep.html.twig', array(
        'form' => $form->createView(),
      ));
    }

    public function createAnalyseAction(Request $request) {

        if (!$this->get('security.context')->isGranted('ROLE_ANALYSIS')) {
          // Sinon on déclenche une exception « Accès interdit »
          throw new AccessDeniedHttpException('no credentials for this action');
        }

        $formData = new Analysis(); // Your form data class. Has to be an object, won't work properly with an array.

        $flow = $this->get('CKM.form.flow.createAnalyse'); // must match the flow's service id
        $flow->bind($formData);

        // form of the current step
        $form = $flow->createForm();

        #var_dump( $request->request->get($form->getName()) );
        if ($flow->isValid($form)) {

          if ( $flow->getCurrentStepNumber() === 1 ) {
            $tmp = $request->request->get($form->getName()) ;
            $scenario = $this->getDoctrine()
              ->getRepository('CKMAppBundle:Scenario')
              ->findOneById($tmp["scenario"][0]);

            $this->container->get('request')->getSession()->set( 'scenario', $scenario );
            $formData->setScenario( $this->container->get('request')->getSession()->get( 'scenario' ) ) ;
          }

          # gestion des target et scan constraint
          if ( $flow->getCurrentStepNumber() === 2 ) {
            $tmp = $request->request->get($form->getName()) ;

            # gestion des saisies des target
            if ( isset($tmp["targetParameter"]) && isset($tmp["targetObservable"]) ) {
              $element_ar = array_merge( $tmp["targetObservable"], $tmp["targetParameter"] );
            }
            elseif( isset($tmp["targetObservable"]) ) {
              $element_ar = $tmp["targetObservable"];
            }
            elseif( isset($tmp["targetParameter"]) ) {
              $element_ar = $tmp["targetParameter"];
            }
            else {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please fill in the form'
                );
                return $this->redirect($this->generateUrl('CKMAppBundle_analyse_create_analyse' ));
            }

            $this->container->get('request')->getSession()->set( 'targetElement', $element_ar );
            $count = count( $this->container->get('request')->getSession()->get( 'targetElement' ) );

            # validation des contraintes sur les scan et nb d elements target
            if ($tmp["scanConstraint"]==1 && $count!==1) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please, in a 1D scan you must choose one observable OR one parameter'
                );
                return $this->redirect($this->generateUrl('CKMAppBundle_analyse_create_analyse' ));
            }
            if ($tmp["scanConstraint"]==2 && $count!==2) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please, in a 2D scan you must choose two items in the boxes'
                );
                return $this->redirect($this->generateUrl('CKMAppBundle_analyse_create_analyse' ));
            }
          }

          if ( $flow->getCurrentStepNumber() === 3 ) {
            $tmp = $request->request->get($form->getName()) ;
            $this->container->get('request')->getSession()->set( 'inputElement', $tmp["sourceElement"] );
          }

          $flow->saveCurrentStepData($form);

          if ($flow->nextStep()) {
              // form for the next step
              $form = $flow->createForm();
          } else {
              // flow finished
              $formData->setTargetElement( $this->container->get('request')->getSession()->get( 'targetElement' ) ) ;
              $formData->setSourceElement( $this->container->get('request')->getSession()->get( 'inputElement' ) ) ;
              $formData->setUser( $this->get('security.context')->getToken()->getUser() );
              //$formData->setScenario( $this->container->get('request')->getSession()->get( 'scenario' ) ) ;

echo '<pre>';
\Doctrine\Common\Util\Debug::dump($formData);
echo '</pre>';
//die('debbug');


              $em = $this->getDoctrine()->getManager();

              $em->persist($formData);

              $tmpObs = array();
              $tmpAssocedParam = array();

              foreach( $this->container->get('request')->getSession()->get( 'targetElement' ) as $key => $target )
              {
                $targetPersist = new ElementTarget($formData, $target);
                $em->persist($targetPersist);
              }

              # tous les parametres regroupes des observables
              $all_ar_parameters=array();

              foreach( $this->container->get('request')->getSession()->get( 'inputElement' ) as $key => $input )
              {
                $inputPersist = new ObservableInput($formData, $input);
                $em->persist($inputPersist);

                $parameters = $inputPersist->createAssociatedElement( $formData->getDatacard() );

                foreach ( $parameters as $parameter ) {
                  if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                    $all_ar_parameters[$parameter->getName()] = $parameter;
                  }
                }


                foreach( $parameters as $key => $parameter ) {
                  if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                    $inputPersist->addParameterInput($parameters[$key]);
                    $em->persist($parameters[$key]);
                  }
                  else {
                    $inputPersist->addParameterInput( $all_ar_parameters[$parameter->getName()] );
                  }
                }

              }

              $em->flush();

              $this->getRequest()->getSession()->remove('targetElement');
              $this->getRequest()->getSession()->remove('sourceElement');
              $flow->reset(); // remove step data from the session


              /*return $this->redirect(
                $this->generateUrl('CKMAppBundle_analyse_create_analyse_source'),
                array('analyse'=>$formData),
              ); // redirect when done

              return $this->forward('CKMAppBundle_analyse_create_analyse_source', array(
                  'analyse'=>$formData,
              ));
              */
              return $this->redirect($this->generateUrl('CKMAppBundle_analyse_create_analyse_source', array('analyse' => $formData->getId())
              ));
          }
        }

        return $this->render('CKMAppBundle:Analysis:createAnalyse.html.twig', array(
            'form' => $form->createView(),
            'flow' => $flow,
            'analyse' => $formData,
        ));
    }

    public function createAnalyseSourceAction($analyse=0, $step ) {
      $em = $this->getDoctrine()
                 ->getManager();

      $analyse = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Analysis')
        ->findOneById($analyse);

      if (!$analyse) {
        throw $this->createNotFoundException('analyse not exist');
      }


      $liste_observable = $em->getRepository('CKMAppBundle:ObservableInput')
                                  ->findByAnalyse($analyse->getId());

      $liste_targetElement = $em->getRepository('CKMAppBundle:ElementTarget')
                                  ->findByAnalyse($analyse->getId());

       return $this->render('CKMAppBundle:Analysis:source.html.twig', array(
            'observables' => $liste_observable,
            'analyse'  => $analyse,
            'step' => $step,
            'targets' => $liste_targetElement
        ));
    }

    private function removeInput($analyse ) {
      $this->isGranted('ROLE_ANALYSIS');
      $analyse = $this->getAnalysis($analyse);

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to remove the input analysis of this user');
      }

      $em = $this->getDoctrine()->getEntityManager();

      $observables = $this->getDoctrine()
        ->getRepository('CKMAppBundle:ObservableInput')
        ->findByAnalyse( $analyse->getId() );

      foreach($observables as $observable) {
        $parameters = $observable->getParameterInputs();
        foreach($parameters as $parameter) {
          $em->remove($parameter);
        }
        $em->remove($observable);
      }
    }

    public function removeAnalysisAction(Analysis $analyse ) {
      $this->isGranted('ROLE_ANALYSIS');

      if (!$analyse) {
        throw $this->createNotFoundException('analysis not exist');
      }

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to remove the analysis of this user');
      }

      $em = $this->getDoctrine()->getEntityManager();
      $tmp = $analyse->getId();

      $observables = $this->getDoctrine()
        ->getRepository('CKMAppBundle:ObservableInput')
        ->findByAnalyse( $analyse->getId() );

      foreach($observables as $observable) {
        $parameters = $observable->getParameterInputs();
        foreach($parameters as $parameter) {
          $em->remove($parameter);
        }
      }

      $em->remove($analyse);
      $em->flush();

      return new Response('analyse '.$tmp.' supprimée');
    }


### ICI : verif ce cas pour observable et parameter
    private function isRanged($tmp, $element, $id, $id_name) {
      if( $tmp['value']<$element->getAllowedRangeMin() or $tmp['value']>$element->getAllowedRangeMax() ) {
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Please respect the range value i.e. '.$element->getAllowedRangeMin().' < value < '.$element->getAllowedRangeMax()
        );
        return $this->redirect(
              $this->generateUrl('CKMAppBundle_analyse_create_analyse_source_observable',
                                  array($id_name => $id, 'type' => 'Observable' )
              )
        );
      }
    }

    public function editObservableAction($observable_id=0) {

    $request = $this->getRequest();

    $observable = $this->getDoctrine()
        ->getRepository('CKMAppBundle:ObservableInput')
        ->findOneById($observable_id);

      if (!$observable) {
        throw $this->createNotFoundException('Observable not exist');
      }

      $form = $this->createForm(new ObservableInputType, $observable);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form->getData();
          //$em->persist($observable);
          $tmp = $request->request->get($form->getName());

          $this->isRanged($tmp, $observable, $observable_id, 'observable_id');

          $observable->setValue( $tmp['value'] );
          $observable->setExpUncertity( $tmp['expUncertity'] );
          $observable->setThUncertity( $tmp['thUncertity'] );

          $em->persist( $observable );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $observable->getAnalyse()->getId() )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:editObservableInput.html.twig', array(
        'form' => $form->createView(),
        'type' => 'Observable',
      ));
    }

    public function editParameterAction($parameter_id=0) {

    $request = $this->getRequest();

    $parameter = $this->getDoctrine()
        ->getRepository('CKMAppBundle:ParameterInput')
        ->findOneById($parameter_id);

      if (!$parameter) {
        throw $this->createNotFoundException('parameter not exist');
      }

      $form = $this->createForm(new ParameterInputType, $parameter);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form->getData();
          //$em->persist($observable);
          $tmp = $request->request->get($form->getName());

          $this->isRanged($tmp, $parameter, $parameter_id, 'parameter_id');

          $parameter->setValue( $tmp['value'] );
          $parameter->setExpUncertity( $tmp['expUncertity'] );
          $parameter->setThUncertity( $tmp['thUncertity'] );

          $em->persist( $parameter );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $parameter->getObservableInputs()->first()->getAnalyse()->getId() )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:editObservableInput.html.twig', array(
        'form' => $form->createView(),
        'type' => 'Parameter',
      ));
    }

    public function editAnalysisPropertiesAction($analyse=0) {
      $request = $this->getRequest();

      $analysis = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Analysis')
        ->findOneById($analyse);

      if (!$analysis) {
        throw $this->createNotFoundException('analysis not exist');
      }

      $form = $this->createForm(new AnalysisPropertiesType, $analysis);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form->getData();
          $analysis->setGranularity( $data->getGranularity() );
          $analysis->setScanMax( $data->getScanMax() );
          $analysis->setScanMin( $data->getScanMin() );
          $analysis->setStatus( 1 );


          $em->persist( $analysis );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $analysis->getId(), 'step' => 2 )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:editAnalysisProperties.html.twig', array(
        'form' => $form->createView(),
      ));
    }

    public function analysisByUserAction(Request $request, $user_id=0)
    {
      if (!$this->get('security.context')->isGranted('ROLE_ANALYSIS')) {
        throw new AccessDeniedHttpException('no credentials for this action');
      }

      $em = $this->getDoctrine()
                 ->getManager();

      $user = $this->getDoctrine()
        ->getRepository('CKMUserBundle:User')
        ->findOneById($user_id);

      if (!$user) {
        throw $this->createNotFoundException('user not exist');
      }

      if ($user->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to see the analysis of this user');
      }

      $analysisListByUser = $em->getRepository('CKMAppBundle:Analysis')
                              ->findByUser($user->getId());

      #\Doctrine\Common\Util\Debug::dump( $analysisListByUser);
      #\Doctrine\Common\Util\Debug::dump( $user);

      return $this->render('CKMAppBundle:Analysis:userAnalysis.html.twig', array(
        'analysesbyuser' => $analysisListByUser,
      ));
    }


    public function sourceAction(Request $request)
    {
      $type = new AnalysisType();
$type->setStep();
echo "sourceAction::".$type->stepName()."<br/>";
      $form = $this->createForm($type, $this->container->get('request')->getSession()->get('analyse') , array(
            'validation_groups' => array($type->getName())
      ));


      //$form->handleRequest($request);


      if ( isset($data['ckm_appbundle_analysis_2']) ) {

print_r( $this->container->get('request')->getSession()->get('analyse') ) ;

        return $this->redirect(
            $this->generateUrl('CKMAppBundle_testform')
          );
      }

      // On passe la méthode createView() du formulaire à la vue afin qu'elle puisse afficher le formulaire toute seule
      return $this->render('CKMAppBundle:Analysis:source.html.twig', array(
        'form' => $form->createView(),
        'test' => $form->getData()
      ));
    }

    public function testformAction(Request $request) {
        return $this->render('CKMAppBundle:Analysis:testform.html.twig', array(
          'analyse' => $request//$this->container->get('request')->getSession()->get('analyse')
          )
      );
    }

    /*
     * Teste si l utilisateur courant possède le role $role pour acceder a la ressource
     */
    private function isGranted($role) {
      if (!$this->get('security.context')->isGranted($role)) {
          // Sinon on déclenche une exception « Accès interdit »
          throw new AccessDeniedHttpException('no credentials for this action');
      }
    }

    /*
     * Teste et retourne l existance d une analyse
     */
    public function getAnalysis($id) {
      $em = $this->getDoctrine()
                 ->getManager();

      $analyse = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Analysis')
        ->findOneById($id);

      if (!$analyse) {
        throw $this->createNotFoundException('analyse not exist');
      }
      return $analyse;
    }

}
