<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;


use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;

class CreateAnalyseForm extends AbstractType {

    private $em;
    private $conn;

    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
        $this->conn = $em->getConnection();
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        switch ($options['flow_step']) {
          case 1:
            $this->step0ne($builder, $options);
            break;
          case 2:
            $this->stepTwo($builder, $options);
            break;
          case 3:
            $this->stepThree($builder, $options);
            break;
        }
    }

    public function getName() {
        return 'createAnalyse';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            #'validation_groups' => array('registration'),
            'constraints' => array(
                        new Assert\Callback(array(array($this, 'isNumberOfTargetValid')))
            )
        ));
    }

    public function isNumberOfTargetValid(CKM\AppBundle\Entity\Analysis $analyse, ExecutionContextInterface $context)
    {
        $context->addViolationAt('startDate', 'There is already an event during this time!');

        $query = $em->createQuery('SELECT COUNT(e.id) FROM CKM\AppBundle\Entity\ElementTarget e WHERE e.analyse_id = :analyse');
        $query->setParameter('analyse', $analyse->getId() );
        $count = $query->getSingleScalarResult();


        /*if ($this->scanConstraint==1 && $count!==1) {
            $context->addViolationAt('scanConstraint', 'Error specific AC scan=1', array(), null);
        }
        if ($this->scanConstraint==2 && $count!==2) {
            $context->addViolationAt('scanConstraint', 'Error specific AC scan=2', array(), null);
        }*/
        die( 'count : '.$count );
    }

    public function step0ne(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('scenario', 'entity', array(
               'class' => 'CKMAppBundle:Scenario',
               'mapped'    => false,
               'multiple'  => false,
               'property' => 'name',
               'attr'      => array('class' => 'form-control'),
               ))
       ;
    }

    public function stepTwo(FormBuilderInterface $builder, array $options)
    {
        \Doctrine\Common\Util\Debug::dump($builder->getData());
        list($obs, $param) = $this->getListOfDatacardObservable( $builder->getData()->getDatacard() /*$builder->getData()->getScenario()->getWebPath()*/ ) ;
        #->getScenario()->getWebPath()

        $builder
          ->add('scanConstraint', 'choice', array(
               'choices' => array('1' => '1D', '2' => '2D'),
               'attr'    => array('class' => 'form-control'),
               ))
          ->add('targetObservable', 'choice'/*'entity'*/, array(
               #'class' => 'CKM\AppBundle\Entity\Observable',
               'choices'   => $obs,
               'mapped'    => false,
               'multiple'  => true,
               'attr'      => array('class' => 'form-control'),
               ))
          ->add('targetParameter', 'choice'/*'entity'*/, array(
               #'class' => 'CKM\AppBundle\Entity\Parameter',
               'choices'   => $param,
               'mapped'    => false,
               'multiple'  => true,
               'attr'      => array('class' => 'form-control'),
               ))
          //->add('step 1', 'submit')
        ;
        #$builder->addValidator(new CallbackValidator(array($this, ‘isOk’)));
    }

    public function isOk(FormInterface $form)
    {
        $scanConstraint = $form->get('scanConstraint');
        $targetObservable = $form->get('targetObservable');
        $targetParameter = $form->get('targetParameter');

        print_r($scanConstraint);
        print_r($targetObservable);
        print_r($targetParameter);

        die("addValidator CallbackValidator");

       /* if ( ! is_null($myfield->getData()) ) {
            $validator      = new RegexValidator();
            $constraint     = new Regex(array(
                'pattern' => "/^[a-z0-9-]+$/"
            ));
            $isValid = $validator->validate( $myfield->getData(), $constraint );
            if ( ! $isValid ) {
                $myfield->addError( new FormError( "This field is not valid (only alphanumeric characters separated by hyphens)" ) );
            }
        }*/
    }

    public function stepThree(FormBuilderInterface $builder, array $options)
    {
      list($obs, $param) = $this->getListOfDatacardObservable( $builder->getData()->getDatacard() ) ;
      $builder
        ->add('sourceElement', 'choice'/*'entity'*/, array(
         #'class' => 'CKM\AppBundle\Entity\Parameter',
         'choices'   => $obs,
         'label'     => 'Input elements',
         'mapped'    => false,
         'multiple'  => true,
         'attr'      => array('class' => 'form-control', 'size' => 12),
         ))
         //->add('step 2', 'submit')
      ;
    }

    private function getListOfDatacardObservable($datacardPath){
      $data = file_get_contents($datacardPath) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);


      $type='';
      $new_line = "^\n$" ;
      $obs_ar = array();
      $param_ar = array();

      foreach($lines as $line) {

        if( ! preg_match("/$new_line/", $line) ) {
          if( preg_match('/# observable/', $line) ) {
            $type='observable';
          }
          elseif( preg_match('/# parameter/', $line) ) {
            $type='parameter';
          }
          else {
            if( $type==='observable' ) {
              $tmp_ar = explode(';',$line);
              #$obs_tmp = new Observable($tmp_ar['0'], $tmp_ar['0'], 1, 2, array("toto", "titi"));
              #$obs_ar[ "$tmp_ar[0]" ] = $obs_tmp;
              $obs_ar[ "$tmp_ar[0]" ] = $tmp_ar[0];
            }
            if( $type==='parameter' ) {
              $tmp_ar = explode(';',$line);
              #$param_tmp = new Parameter($tmp_ar['0'], $tmp_ar['0'], 1, 2);
              #$param_ar[ "$tmp_ar[0]" ] = $param_tmp;
              $param_ar[ "$tmp_ar[0]" ] = $tmp_ar[0];
            }
          }
        }

      }
      return array($obs_ar, $param_ar);

    }

}
