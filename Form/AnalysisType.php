<?php

namespace CKM\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;


class AnalysisType extends AbstractType
{
    protected $_step = 1;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      switch ( $this->_step ) {
          case 1:
            $this->step0ne($builder, $options);
            break;
          case 2:
            $this->stepTwo($builder, $options);
            break;
      }
    }

    public function step0ne(FormBuilderInterface $builder, array $options)
    {
        list($obs, $param) = $this->getListOfDatacardObservable( $builder->getData()->getDatacard() ) ;

        $builder
          ->add('scanConstraint', 'choice', array(
               'choices' => array('11D' => '1D', '2D' => '22D'),
               'attr'    => array('class' => 'form-control'),
               'empty_value' => '',
               ))
          ->add('targetObservable', 'choice'/*'entity'*/, array(
               #'class' => 'CKM\AppBundle\Entity\Observable',
               'choices'   => $obs,
               'mapped'    => false,
               'multiple'  => true,
               'attr'      => array('class' => 'form-control'),
               'empty_value' => '',
               ))
          ->add('targetParameter', 'choice'/*'entity'*/, array(
               #'class' => 'CKM\AppBundle\Entity\Parameter',
               'choices'   => $param,
               'mapped'    => false,
               'multiple'  => true,
               'attr'      => array('class' => 'form-control'),
               'empty_value' => '',
               ))
          ->add('step 1', 'submit')
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
         'empty_value' => '',
         ))
         ->add('step 2', 'submit')
      ;
    }

    public function setStep()
    {
        ++$this->_step;
    }

    public function stepName()
    {
      switch ( $this->_step ) {
          case 1:
            return "definition";
            break;
          case 2:
            return "source";
            break;
          case 3:
            return "testform";
            break;
      }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CKM\AppBundle\Entity\Analysis',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        if($this->_step == 1)
          return 'ckm_appbundle_analysis';
        elseif($this->_step == 2)
          return 'ckm_appbundle_analysis_2';
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
            // Creating objects with dynamic class names
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

    private function getListOfDatacardParameter($datacardPath){

    }
}
