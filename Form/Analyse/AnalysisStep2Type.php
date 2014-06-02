<?php

namespace CKM\AppBundle\Form\Analyse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;


class AnalysisStep2Type extends AbstractType
{
    protected $em;

    public function __construct( \Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $path = $options['data']->getScenario()->getWebPath();

      list($obs, $param) = $this->getListOfDatacardObservable( $path );
      list($obs, $param) = $this->latexLike($obs, $param) ;

      /*echo '<pre>';
      print_r($obs);
      print_r($param);
      echo '</pre>';

      die('debbug');*/

      $builder
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
        ->add('scanMin1', 'number', array(
            'attr'             => array('class' => 'form-control'),
            'mapped'           => false,
            'invalid_message'  => 'the value must be a number',
            'label'            => 'Scan min of the first target ',
          ))
        ->add('scanMax1', 'number',  array(
            'attr'             => array('class' => 'form-control'),
            'mapped'           => false,
            'invalid_message'  => 'the value must be a number',
            'label'            => 'Scan max of the first target',
          ))
       ;

      if( $options['data']->getScanConstraint()==2 ) {
        $builder
        ->add('scanMin2', 'number', array(
            'attr'             => array('class' => 'form-control'),
            'mapped'           => false,
            'invalid_message'  => 'the value must be a number',
            'label'            => 'Scan min of the second target',
          ))
        ->add('scanMax2', 'number',  array(
            'attr'             => array('class' => 'form-control'),
            'mapped'           => false,
            'invalid_message'  => 'the value must be a number',
            'label'            => 'Scan max of the second target',
          ))
       ;
      }

    }

    private function latexLike($observables, $parameters){
      foreach($observables as &$observable) {
        $latex = $this->em
          ->getRepository('CKMAppBundle:Latex')
          ->findOneByName( $observable );

        if($latex) {
          $observable=$latex->getLatex().' ('.$observable.')';
        }
      }
      foreach($parameters as &$parameter) {
        $latex = $this->em
          ->getRepository('CKMAppBundle:Latex')
          ->findOneByName( $parameter );
        if($latex) {
          $parameter=$latex->getLatex().' ('.$parameter.')';
        }
      }

      return array($observables, $parameters);
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
        return 'ckm_appbundle_analysis_step2';
    }

}