<?php
// src/Blogger/AppBundle/Controller/AnalysisController.php
namespace CKM\AppBundle\Controller;

use CKM\AppBundle\Entity\Analysis;
use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;
use CKM\AppBundle\Entity\Input;

use CKM\AppBundle\Form\AnalysisType;
use CKM\AppBundle\Form\ObservableType;
use CKM\AppBundle\Form\ObservableTagType;
use CKM\AppBundle\Form\ParameterType;
use CKM\AppBundle\Form\AnalysisPropertiesType;

use CKM\AppBundle\Form\Analyse\AnalysisStep1Type;
use CKM\AppBundle\Form\Analyse\AnalysisStep2Type;
use CKM\AppBundle\Form\Analyse\AnalysisStep3Type;
use CKM\AppBundle\Form\TargetScanType;

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
      $form = $this->createForm(new AnalysisStep1Type(
                                $this->get('CKM.services.analysisManager')->getScenariosIsDocumented(true)
                                ),
                                $analyse);

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
        'form'     => $form->createView(),
        'message1' => 'step 1',
        'message'  => 'Scenario & Scan constraint',
        'step'     => '1',
      ));
    }

    /*
     * Target creation
     */
    public function createAnalyseStep2Action($analyse=0, $step=2 ) {
      $this->isGranted('ROLE_ANALYSIS');
      $analyse = $this->getAnalysis($analyse);

      if( $this->isForbiddenStep($analyse) ){
        return $this->errorForm(
          'warning',
          'You can not modify with analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $analyse->getId() )
        );
      }

      $request = $this->getRequest();

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      # re choose target
      if($step==0) {
        $this->removeTarget($analyse);
      }

      $form = $this->createForm(new AnalysisStep2Type($this->getDoctrine()->getManager() ),  $analyse);

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
          elseif( !isset($tmp["targetObservable"]) && !isset($tmp["targetObservable"]) ) {
            return $this->errorForm('notice',
              'Please fill in the form',
              'CKMAppBundle_analyse_create_step_2',
              array('analyse' => $analyse->getId(), 'step' => $step )
              );
          }
          # validation des contraintes sur les scan et nb d elements target
          $count = count( $element_ar );
          if ( $analyse->getScanConstraint() ==1 && $count!==1) {
            return $this->errorForm('notice',
              'Please, in a 1D scan you must choose one element',
              'CKMAppBundle_analyse_create_step_2',
              array('analyse' => $analyse->getId(), 'step' => $step )
              );
          }
          if ($analyse->getScanConstraint() ==2 && $count!==2) {
            return $this->errorForm('notice',
                'Please, in a 2D scan you must choose two items in the boxes',
                'CKMAppBundle_analyse_create_step_2',
                array('analyse' => $analyse->getId(), 'step' => $step )
                );
          }
          if( $tmp["scanMax1"] < $tmp["scanMin1"] or (isset($tmp["scanMax2"]) and $tmp["scanMax2"] < $tmp["scanMin2"] ) ) {
            return $this->errorForm('notice',
              'scanMax must be greater than scanMin',
              'CKMAppBundle_analyse_create_step_2',
              array('analyse' => $analyse->getId(), 'step' => $step )
              );
          }

          $em = $this->getDoctrine()->getManager();
          # tous les parametres regroupes des target observables
          $all_ar_parameters=array();
          $all_ar_observables=array();

          # TODO if findByInputAnalysis return empty ?
          $observables = $this->getDoctrine()
            ->getRepository('CKMAppBundle:Observable')
            ->findByInputAnalysis( $analyse->getId() );

          foreach( $observables as $observable ) {
            $all_ar_observables[$observable->getName()]=$observable;
            foreach( ($observable->getParameters()) as $parameter ) {
                $all_ar_parameters[$parameter->getName()]=$parameter;
            }
          }

          foreach( $element_ar as $key => $target )
          {
            #$targetPersist = new ElementTarget($analyse, $target);
            #$em->persist($targetPersist);

            if( $analyse->isObservable($target) ) {
              if( array_key_exists($target, $all_ar_observables) ) {
                $targetPersist=$all_ar_observables[$target];
                $targetPersist->setIstarget(true);
                #$em->persist($targetPersist);
              }
              else {
                $targetPersist = new Observable($analyse, $target, $analyse->getScenario()->getWebPath());
                $targetPersist->setIsTarget(true);
                $targetPersist->setIsInput(false);
                $em->persist($targetPersist);

                $parameters = $targetPersist->createAssociatedElement( $analyse->getScenario()->getWebPath() );

                foreach ( $parameters as $parameter ) {
                  if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                    $all_ar_parameters[$parameter->getName()] = $parameter;
                  }
                }

                foreach( $parameters as $keyParam => $parameter ) {
                  if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                    $targetPersist->addParameter($parameters[$keyParam]);
                    $em->persist($parameters[$keyParam]);
                  }
                  else {
                    $targetPersist->addParameter( $all_ar_parameters[$parameter->getName()] );
                    $em->persist($targetPersist);
                  }
                }
              }
            }
            else {
            # target is a parameter
              if( !array_key_exists($target, $all_ar_parameters) ) {
                $targetPersist = new Parameter($analyse, $target, $analyse->getScenario()->getWebPath());
                $targetPersist->setIsTarget(true);
                $targetPersist->setIsInput(false);
                $em->persist($targetPersist);
                $all_ar_parameters[$target] = $targetPersist;
              }
              else {
                $targetPersist=$all_ar_parameters[$target];
                $targetPersist->setIsTarget(true);
                #$targetPersist->setIsInput(false);
              }
            }

            if($key==0) {
              $targetPersist->setScanMax($tmp["scanMax1"]);
              $targetPersist->setScanMin($tmp["scanMin1"]);
            }
            elseif( isset($tmp["scanMax2"]) ) {
              $targetPersist->setScanMax($tmp["scanMax2"]);
              $targetPersist->setScanMin($tmp["scanMin2"]);
            }
            $em->persist($targetPersist);

          }


          $em->persist( $analyse );


          $em->flush();
          $this->setLatexAnalysis($analyse);

          #$analyse = print_r($analyse,true);
          #die('debbug <pre>'.$analyse .'</pre>');

          if($step==0) {
            return $this->redirect(
                    $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                        array('analyse' => $analyse->getId(), 'step' => 4 )
                    )
            );
          }
          else {
            return $this->redirect(
                    $this->generateUrl('CKMAppBundle_analyse_create_step_3',
                                        array('analyse' => $analyse->getId(), 'step' => 3 )
                    )
            );
          }
        }
      }
      return $this->render('CKMAppBundle:Analysis:createAnalysisStep2.html.twig', array(
        'form' => $form->createView(),
        'message1' => 'step 2',
        'message'  => 'Target Input',
        'step'     => '2',
        'analyse'  => $analyse->getId(),
        'constraint' => $analyse->getScanConstraint(),
      ));
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

    public function createAnalyseStep3Action($analyse=0, $step=3 ) {
      $this->isGranted('ROLE_ANALYSIS');
      $analyse = $this->getAnalysis($analyse);

      if( $this->isForbiddenStep($analyse) ){
        return $this->errorForm(
          'warning',
          'You can not modify with analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $analyse->getId() )
        );
      }

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      $request = $this->getRequest();

      $form = $this->createForm(new AnalysisStep3Type($this->getDoctrine()->getManager() ),  $analyse);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);

        if ($form->isValid()) {
          # re choose input
          if($step==0) {
            $this->removeInput($analyse);
          }

          $tmp = $request->request->get($form->getName()) ;

          if(!isset($tmp["sourceElement"])) {
            return $this->errorForm('notice',
              'Please fill in the form',
              'CKMAppBundle_analyse_create_step_3',
              array('analyse' => $analyse->getId(), 'step' => $step )
            );
          }

          $em = $this->getDoctrine()->getManager();
          # les observables de l analyse
          $observablesInputForPrintDatacard = array();
          # tous les parametres regroupes des observables
          $all_ar_parameters=array();
          # toutes les observables de l'analyse target+input
          $all_ar_observables=array();

          # gestion des parametres existant si la target est une observable

          $targets = $this->getDoctrine()
            ->getRepository('CKMAppBundle:Input')
            ->findTargetByAnalysis( $analyse->getId() );

          foreach( $targets as $target ) {
            if( $analyse->isObservable( $target->getname() ) ) {
              $all_ar_observables[$target->getName()]=$target;
              foreach( ($target->getParameters()) as $parameter ) {
                $all_ar_parameters[$parameter->getName()]=$parameter;
              }
            }
            else {
                # target = parameter a considerer
                $all_ar_parameters[$target->getName()]=$target;
            }
          }

          foreach( $tmp["sourceElement"] as $key => $input )
          {
            if( array_key_exists($input, $all_ar_observables) ) {
              # target = obs = input
              $inputPersist=$all_ar_observables[$input];
              $inputPersist->setIsInput(true);
              $parameters=$inputPersist->getParameters();
              foreach ( $parameters as $parameter ) {
                $parameter->setIsInput(true);
              }

              $em->persist($inputPersist);
              $observablesInputForPrintDatacard[]=$inputPersist;
            }
            else {
              $inputPersist = new Observable( $analyse, $input, $analyse->getScenario()->getWebPath() );
              $em->persist($inputPersist);
              $observablesInputForPrintDatacard[]=$inputPersist;

              $parameters = $inputPersist->createAssociatedElement( $analyse->getScenario()->getWebPath() );

              foreach ( $parameters as $parameter ) {
                if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                  $all_ar_parameters[$parameter->getName()] = $parameter;
                }
              }

              foreach( $parameters as $key => $parameter ) {
                if( !array_key_exists($parameter->getName(), $all_ar_parameters) ) {
                  $inputPersist->addParameter($parameters[$key]);
                  $em->persist($parameters[$key]);
                }
                else {
                  $tmp=$all_ar_parameters[$parameter->getName()];
                  $tmp->setIsInput(true);
                  $inputPersist->addParameter( $all_ar_parameters[$parameter->getName()] );
                }
              }
            }
          }

          $analyse->setDatacard( $observablesInputForPrintDatacard, $all_ar_parameters );

          #$analyse = print_r($analyse,true);
          #die('debbug <pre>'.$analyse .'</pre>');

          $analyse->setStatus( 1 );




          $em->persist( $analyse );
          $em->flush();
          $this->setLatexAnalysis($analyse);

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $analyse->getId(), 'step' => 4 )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:createAnalysisStep.html.twig', array(
        'form' => $form->createView(),
        'message1' => 'step 3',
        'message'  => 'Input Element',
        'step'     => '3',
        'analyse'  => $analyse->getId(),
      ));
    }

    private function setDatacard($analyse=0) {
      $analyse = $this->getAnalysis($analyse);

      $observables= $this->getDoctrine()
          ->getRepository('CKMAppBundle:Observable')
          ->findByObservableAnalysis( $analyse->getId() );

      $parameters= $this->getDoctrine()
          ->getRepository('CKMAppBundle:Parameter')
          ->findByParameterAnalysis( $analyse->getId() );

      $em = $this->getDoctrine()
                 ->getManager();
      $analyse->setDatacard($observables, $parameters);
      $em->persist($analyse);
      $em->flush();
    }

    public function seeDatacardAction($analyse=0) {
      $this->isGranted('ROLE_ANALYSIS');

      $analyse = $this->getAnalysis($analyse);

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      return $this->render('CKMAppBundle:Analysis:seeDatacard.html.twig', array(
        'datacard' => $analyse->getDatacard(),
        'id'       => $analyse->getId(),
      ));
    }

    public function copyAction($analyse=0 ) {
      $this->isGranted('ROLE_ANALYSIS');

      $analyse = $this->getAnalysis($analyse);

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      if( $analyse->getStatus()!=2 ){
        return $this->errorForm(
          'warning',
          'You can only copy a finalized analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $analyse->getId() )
        );
      }
      $em = $this->getDoctrine()->getManager();
      $analyseClone = clone $analyse;
      $analyseClone->setStatus(1);
      $analyseClone->setName( $analyseClone->getName().' [copy]' );


      $parameters = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Parameter')
        ->findByAnalysis( $analyse->getId() );

      $observables = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Observable')
        ->findByAnalysis( $analyse->getId() );

      $all_ar_parameters=array();
      $all_ar_observables=array();

      foreach( $parameters as $parameter )
      {
        $parameterClone = clone $parameter;
        $parameterClone->setAnalyse($analyseClone );
        $all_ar_parameters[$parameterClone->getName()]=$parameterClone;
        #$em->persist($parameterClone);
      }

      foreach( $observables as $observable )
      {
        $observableClone = clone $observable;
        $observableClone->setAnalyse($analyseClone );

        $em->persist($observableClone);
        $observableClone->setParameters(null);

        $parametersNameClone = $observableClone->getParameterNameForObservable($analyseClone->getScenario()->getWebPath());
        foreach( $parametersNameClone as $parameterNameClone ) {

          $tmp = $all_ar_parameters[$parameterNameClone];
          $tmp->setObservables(null);

          $observableClone->addParameter( $tmp );


          $tmp->addObservable($observableClone);

          $em->persist( $all_ar_parameters[$parameterNameClone] );
          $em->persist($observableClone);
        }
$em->persist($observableClone);

        $all_ar_observables[]=$observableClone;
      }


      $em->persist($analyseClone);
      $em->flush();

      #die('debbug');

      $this->get('session')->getFlashBag()->add(
            'notice',
            'Your analysis ['.  $analyse->getId() .'] have been copy in analysis ['.$analyseClone->getId().']'
      );

      return $this->redirect(
        $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                            array('analyse' => $analyse->getId(), 'step' => 4 )
        )
      );
    }

    public function finalizeAction($analyse=0 ) {
      $this->isGranted('ROLE_ANALYSIS');

      $analyse = $this->getAnalysis($analyse);

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      $analyse->setStatus(2);
      $em = $this->getDoctrine()->getManager();
      $em->persist($analyse);
      $em->flush();

      $this->get('session')->getFlashBag()->add(
            'notice',
            'Your analysis ['.  $analyse->getId() .'] have been run'
      );

      return $this->redirect(
        $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                            array('analyse' => $analyse->getId(), 'step' => 4 )
        )
      );
    }

    public function testDatacardAction($analyse=0) {
      $analyse = $this->getAnalysis($analyse);

      $observables = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Analysis')
        ->findOnservableByAnalysis( $analyse->getId() );

      #$analyse->setDatacard($observables);

      return new Response('debbug');
    }

    public function createAnalyseSourceAction($analyse=0, $step ) {
      $this->isGranted('ROLE_ANALYSIS');
      $analyse = $this->getAnalysis($analyse);
      // FS#9
      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      $em = $this->getDoctrine()
                 ->getManager();
      #maj de la datacard
      $this->setDatacard($analyse->getId() );

      $liste_observable = $em->getRepository('CKMAppBundle:Observable')
                                  ->findByObservableAnalysis($analyse->getId());

      $liste_targetElement = $em->getRepository('CKMAppBundle:Input')
                                  ->findTargetByAnalysis($analyse->getId());


      $arMatchTargetObs= $this->getDoctrine()
          ->getRepository('CKMAppBundle:Observable')
          ->findByInputTargetAnalysis( $analyse->getId() );


       return $this->render('CKMAppBundle:Analysis:source.html.twig', array(
            'observables' => $liste_observable,
            'analyse'  => $analyse,
            'step' => $step,
            'targets' => $liste_targetElement,
            'target_and_observable' => $arMatchTargetObs,
        ));
    }

    private function removeTarget($analyse ) {
      $this->isGranted('ROLE_ANALYSIS');
      $analyse = $this->getAnalysis($analyse);

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to remove the input analysis of this user');
      }

      $em = $this->getDoctrine()->getEntityManager();

      # gestion des target = input
      $TargetAndInputs = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Input')
          ->findByInputTargetAnalysis( $analyse->getId() );

      foreach($TargetAndInputs as $target) {
        $target->setIsTarget(false);
        $em->persist($target);
      }
      $em->flush();

      # gestion des target qui ne sont pas des input
      $targets = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Input')
        ->findTargetByAnalysis( $analyse->getId() );

      $observables = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Observable')
        ->findByInputAnalysis( $analyse->getId() );

      foreach($targets as $target) {
        if( $target instanceof Observable) {
          $parameters = $target->getParameters();
          foreach($parameters as $parameter) {
            # verifier que le parametre de la target n est pas egalement associe a un parametre d une input observable
            if( !$analyse->isParamOfObservableTarget($parameter->getName(), $observables ) ) {
              $em->remove($parameter);
            }
            $em->remove($target);
          }
        }
        else { # target is a parameter
          if( !$analyse->isParamOfObservableTarget($target->getName(), $observables ) ) {
            $em->remove($target);
          }
        }
        # on efface la quantite observable ou parameter

      }

      $em->flush();

    }

    private function removeInput($analyse ) {
      $this->isGranted('ROLE_ANALYSIS');
      $analyse = $this->getAnalysis($analyse);

      if ($analyse->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to remove the input analysis of this user');
      }

      $em = $this->getDoctrine()->getEntityManager();

      $observablesTargetInput = $this->getDoctrine()
          ->getRepository('CKMAppBundle:Input')
          ->findByInputTargetAnalysis( $analyse->getId() );

      foreach($observablesTargetInput as $observable) {
        $observable->setIsInput(false);
        $em->persist($observable);
      }
      $em->flush();

      $targets = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Input')
        ->findTargetByAnalysis( $analyse->getId() );

      $observables = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Observable')
        ->findByInputAnalysis( $analyse->getId() );

      foreach($observables as $observable) {
        $parameters = $observable->getParameters();
        foreach($parameters as $parameter) {
          #$parameter->setIsInput(false);
          # verifier que le parametre n est pas egalement associe a une target observable
          #if( !$analyse->isParamOfObservableTarget($parameter->getName(), $targets ) ) {
          # FS#11
          if( $parameter->getIsInput() and !$analyse->isParamOfObservableTarget($parameter->getName(), $targets )  ) {
            $em->remove($parameter);
          }
        }
        $em->remove($observable);
      }
      $em->flush();

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

      $targets = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Input')
        ->findTargetByAnalysis( $analyse->getId() );

      foreach($targets as $target) {
        if($analyse->isObservable($target) ) {
          $parameters = $target->getParameters();
          foreach($parameters as $parameter) {
            $em->remove($parameter);
          }
        }
      }

      $observables = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Observable')
        ->findByInputAnalysis( $analyse->getId() );

      foreach($observables as $observable) {
        $parameters = $observable->getParameters();
        foreach($parameters as $parameter) {
          $em->remove($parameter);
        }
      }

      $em->remove($analyse);
      $em->flush();

      $this->get('session')->getFlashBag()->add(
        'information',
        'Analysis '.$tmp.' deleted with success'
        );

      return $this->redirect(
              $this->generateUrl('CKMAppBundle_analyse_by_user',
                                  array('user_id' => $analyse->getUser()->getId() )
              )
      );
      #return new Response('analyse '.$tmp.' supprimée'); Request $request, $user_id=0
    }


    /*
     * Verifie que la valeur est dans le range
     */
    private function isValueRanged($tmp, $element) {
      if( $tmp['value']<$element->getAllowedRangeMin() or $tmp['value']>$element->getAllowedRangeMax() ) {
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Please respect the range value i.e. '.$element->getAllowedRangeMin().' < value < '.$element->getAllowedRangeMax()
        );
        return false;
      }
      return true;
    }

    public function editInputTagAction($input_id=0) {
      $request = $this->getRequest();

      $input = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Input')
        ->findOneById($input_id);

      if (!$input) {
        throw $this->createNotFoundException('Observable not exist');
      }

      $this->isGranted('ROLE_ANALYSIS');

      if( $this->isForbiddenStep( $this->getAnalysis($input->getAnalyse()->getId() )) ){
        return $this->errorForm(
          'warning',
          'You can not modify with analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $input->getAnalyse()->getId() )
        );
      }

      if ($input->getAnalyse()->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to remove the analysis of this user');
      }

      $form1 = $this->createForm(
                              new ObservableTagType(
                                $this->get('CKM.services.analysisManager')->getScenariosForInput($input)
                              ),
                              $input);

      if ($request->getMethod() == 'POST') {
        $form1->handleRequest($request);
        if ($form1->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form1->getData();
          //$em->persist($observable);
          $tmp = $request->request->get($form1->getName());

          $input->setValue( 0 );
          $input->setExpUncertity( 0 );
          $input->setThUncertity( 0 );

          $em->persist( $input );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $input->getAnalyse()->getId() )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:editObservableInputTag.html.twig', array(
        'form1' => $form1->createView(),
        'input_id' => $input_id,
      ));
    }

    public function editInputAction($input_id=0, $type='Observable') {
      $request = $this->getRequest();

      $observable = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Input')
        ->findOneById($input_id);

      if (!$observable) {
        throw $this->createNotFoundException('Observable not exist');
      }

      $this->isGranted('ROLE_ANALYSIS');

      if( $this->isForbiddenStep( $this->getAnalysis($observable->getAnalyse()->getId() )) ){
        return $this->errorForm(
          'warning',
          'You can not modify with analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $observable->getAnalyse()->getId() )
        );
      }

      if ($observable->getAnalyse()->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to remove the analysis of this user');
      }

      $form = $this->createForm(new ObservableType, $observable);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form->getData();
          //$em->persist($observable);
          $tmp = $request->request->get($form->getName());

          if( ! $this->isValueRanged($tmp, $observable) ) {
            return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source_input',
                                      array('input_id' => $input_id, 'type' => $type )
                  )
            );
          }

          if( $tmp['value']==0 or $tmp['expUncertity']==0 or $tmp['thUncertity']==0 ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Please, you must change the three values at once'
            );
            return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source_input',
                                      array('input_id' => $input_id, 'type' => $type )
                  )
            );
          }

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
        'type' => $type,
        'observable'   => $observable,
      ));
    }

    public function editParameterAction($parameter_id=0) {

    $request = $this->getRequest();

    $parameter = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Input')
        ->findOneById($parameter_id);

      if (!$parameter) {
        throw $this->createNotFoundException('parameter not exist');
      }

      $this->isGranted('ROLE_ANALYSIS');

      if( $this->isForbiddenStep( $this->getAnalysis($parameter->getAnalyse()->getId() )) ){
        return $this->errorForm(
          'warning',
          'You can not modify with analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $parameter->getAnalyse()->getId() )
        );
      }

      if ($parameter->getAnalyse()->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to remove the analysis of this user');
      }

      $form = $this->createForm(new ParameterType, $parameter);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form->getData();
          //$em->persist($observable);
          $tmp = $request->request->get($form->getName());

          if( ! $this->isValueRanged($tmp, $parameter) ) {
            return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source_parameter',
                                      array('parameter_id' => $parameter_id, 'type' => 'Parameter' )
                  )
            );
          }

          $parameter->setValue( $tmp['value'] );
          $parameter->setExpUncertity( $tmp['expUncertity'] );
          $parameter->setThUncertity( $tmp['thUncertity'] );

          $em->persist( $parameter );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $parameter->getAnalyse()->getId() )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:editObservableInput.html.twig', array(
        'form' => $form->createView(),
        'type' => 'Parameter',
      ));
    }

    public function editAnalysisScanAction($target_id=0) {
      $this->isGranted('ROLE_ANALYSIS');
      $target = $this->getTarget($target_id);

      if( $this->isForbiddenStep( $this->getAnalysis($target->getAnalyse()->getId() )) ){
        return $this->errorForm(
          'warning',
          'You can not modify with analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $target->getAnalyse()->getId() )
        );
      }
      $request = $this->getRequest();

      if ($target->getAnalyse()->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      $form = $this->createForm(new TargetScanType, $target);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form->getData();
          #$analysis->setGranularity( $data->getGranularity() );

          if( $data->getScanMax() < $data->getScanMin() ) {
            return $this->errorForm('notice',
              'Max must be greater than min',
              'CKMAppBundle_analyse_create_analyse_scan',
              array('target_id' => $target_id)
              );
          }

          $target->setScanMax( $data->getScanMax() );
          $target->setScanMin( $data->getScanMin() );

          $em->persist( $target );
          $em->flush();

          return $this->redirect(
                  $this->generateUrl('CKMAppBundle_analyse_create_analyse_source',
                                      array('analyse' => $target->getAnalyse()->getId(), 'step' => 2 )
                  )
          );
        }
      }
      return $this->render('CKMAppBundle:Analysis:editAnalysisProperties.html.twig', array(
        'form' => $form->createView(),
        'message' => 'Target Input',
        'type'    => 'scan',
      ));

    }

    public function editAnalysisPropertiesAction($analyse=0) {
      $this->isGranted('ROLE_ANALYSIS');
      $analysis = $this->getAnalysis($analyse);
      if( $this->isForbiddenStep($analysis) ){
        return $this->errorForm(
          'warning',
          'You can not modify with analysis',
          'CKMAppBundle_analyse_create_analyse_source',
          array('analyse' => $analysis->getId() )
        );
      }
      $request = $this->getRequest();

      if ($analysis->getUser()->getId() != $this->get('security.context')->getToken()->getUser()->getId() ) {
        throw $this->createNotFoundException('Sorry, you are not authorized to change the analysis of this user');
      }

      $form = $this->createForm(new AnalysisPropertiesType, $analysis);

      if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $data=$form->getData();
          $analysis->setGranularity( $data->getGranularity() );
          #$analysis->setScanMax( $data->getScanMax() );
          #$analysis->setScanMin( $data->getScanMin() );
          #$analysis->setStatus( 1 );


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
        'message' => 'Properties definition',
        'type'    => 'property',
      ));
    } #

    public function analysisByUserAction(Request $request, $user_id=0, $page)
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

/*      $analysisListByUser = $em->getRepository('CKMAppBundle:Analysis')
                              ->findByUser($user->getId());*/

      $maxArticles=$this->container->getParameter('max_analysis_per_page');

      $analysisListByUser = $em->getRepository('CKMAppBundle:Analysis')
                            ->findAnalysisByUserAndStatus($user->getId(), -1);

      $countListByUser = $em->getRepository('CKMAppBundle:Analysis')
                            ->countAnalysisByUser($user->getId(), -1);

      $analyse = $em->getRepository('CKMAppBundle:Analysis')
                            ->getList($page, $maxArticles, $user->getId(), -1);

      $pagination = array(
        'page' => $page,
        'route' => 'CKMAppBundle_analyse_by_user',
        'pages_count' => ceil($countListByUser / $maxArticles),
        'route_params' => array('user_id'=>$user_id)
      );


      #\Doctrine\Common\Util\Debug::dump( $analysisListByUser);
      #\Doctrine\Common\Util\Debug::dump( $user);

      return $this->render('CKMAppBundle:Analysis:userAnalysis.html.twig', array(
        'analysesbyuser' => $analyse,
        'count'     =>  $countListByUser,
        'page'=>$page,
        'pagination' => $pagination
      ));
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

    private function setLatexAnalysis($analyse) {
      $em = $this->getDoctrine()->getEntityManager();
      $inputs = $em
        ->getRepository('CKMAppBundle:Input')
        ->findByAnalysis($analyse->getId() );


      foreach($inputs as $input) {
        echo $input->getName().'<br />';
        $input->setLatex(
          $em
          ->getRepository('CKMAppBundle:Latex')
          ->findOneByName( $input->getName() )
        );
        $em->persist($input);
      }

     // die('debbug');
      $em->flush();
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

    private function isForbiddenStep($analyse) {
      if($analyse->getStatus()==2) {
        return true;
      }
      return false;
    }

    /*
     * Teste et retourne l existance d un target
     */
    public function getTarget($id) {
      $em = $this->getDoctrine()
                 ->getManager();

      $target = $this->getDoctrine()
        ->getRepository('CKMAppBundle:Input')
        ->findOneById($id);

      if (!$target) {
        throw $this->createNotFoundException('Target not exist');
      }
      return $target;
    }

}
