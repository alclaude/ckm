<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CreateAnalyseSourceForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        switch ($options['flow_step']) {
          case 1:
            $this->step0ne($builder, $options);
            break;
          case 2:
            $this->stepTwo($builder, $options);
            break;
        }
    }

    public function getName() {
        return 'createAnalyseSource';
    }

    public function step0ne(FormBuilderInterface $builder, array $options)
    {
        list($obs, $param) = $this->getListOfDatacardObservable( $builder->getData()->getDatacard() ) ;

        $builder
          ->add('scanConstraint', 'choice', array(
               'choices' => array('1D' => '1D', '2D' => '2D'),
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
    }

    public function stepTwo(FormBuilderInterface $builder, array $options)
    {
      list($obs, $param) = $this->getListOfDatacardObservable( $builder->getData()->getDatacard() ) ;
      $builder
        ->add('sourceElement', 'choice'/*'entity'*/, array(
         #'class' => 'CKM\AppBundle\Entity\Parameter',
         'choices'   => $obs,
         'mapped'    => false,
         'multiple'  => true,
         'attr'      => array('class' => 'form-control'),
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
          if( preg_match('/observable/', $line) ) {
            // Creating objects with dynamic class names
            $type='observable';

          }
          elseif( preg_match('/parameter/', $line) ) {
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