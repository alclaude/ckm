<?php

namespace CKM\AppBundle\Form\Analyse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CKM\AppBundle\Entity\Observable;
use CKM\AppBundle\Entity\Parameter;


class AnalysisStep3Type extends AbstractType
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
      //\Doctrine\Common\Util\Debug::dump($builder);
      $path = $options['data']->getScenario()->getWebPath();
      $obs = $this->getListOfDatacardObservable( $path ) ;
      $obs= $this->latexLike($obs) ;

      $builder
        ->add('sourceElement', 'choice'/*'entity'*/, array(
         'choices'   => $obs,
         //'choices'   => array_merge($obs, $param),
         'label'     => 'Inputs',
         'mapped'    => false,
         'multiple'  => true,
         'attr'      => array('class' => 'form-control', 'size' => 12),
         'required'  => false,
         ))
      ;
    }

    private function latexLike($observablesAggrByTag){
      foreach($observablesAggrByTag as &$observables) {
        foreach($observables as &$observable) {
          $latex = $this->em
            ->getRepository('CKMAppBundle:Latex')
            ->findOneByName( $observable );

          if($latex) {
            $observable=$latex->getLatex();
          } else {
            $observable= '('.$observable.')';
          }
        }
      }
      return $observablesAggrByTag;
    }

    private function getListOfDatacardObservable($datacardPath){
      $data = file_get_contents($datacardPath) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);

      $type='';
      $new_line = "^\n$" ;
      $observables = array();
      $obs_aggr = array();
      $obs_aggr_by_tag = array();

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
              $observables[] = $line;
            }
          }
        }
      }

      foreach($observables as $observable) {
        $tmp_ar = explode(';',$observable);
        $obs_aggr[] = $tmp_ar[5];
      }

      $obs_tag = array_unique($obs_aggr);

      foreach($observables as $observable) {
        $tmp_ar = explode(';',$observable);
        foreach ($obs_tag as $tag) {
          if($tmp_ar[5]==$tag){
            $obs_aggr_by_tag[$tag][ $tmp_ar[0] ]= $tmp_ar[0];
          }
        }
      }
      return $obs_aggr_by_tag;
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
        return 'ckm_appbundle_analysis_step3';
    }

}
