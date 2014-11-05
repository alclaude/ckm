<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

use CKM\AppBundle\Entity\Parameter;

/**
 * ObservableInput
 *
 * @ORM\Table(name="observable")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ObservableRepository")
 */
class Observable extends Input
{


   /**
    * @ORM\ManyToMany(targetEntity="CKM\AppBundle\Entity\Parameter", inversedBy="observables", cascade={"persist"})
    * @ORM\JoinTable(name="observables_parameters_inputs",
    *      joinColumns={@ORM\JoinColumn(nullable=false, onDelete="CASCADE")},
    *      inverseJoinColumns={@ORM\JoinColumn(nullable=false, onDelete="CASCADE")}
    *      )
    */
    #* @ORM\JoinTable(name="observables_parameters_input")
    #* @ORM\JoinColumn(nullable=false, onDelete="CASCADE", onUpdate="CASCADE")
    private $parameters;


    public function __construct($analyse, $name, $path) {
        $this->parameters = new \Doctrine\Common\Collections\ArrayCollection();
        #$this->analyse    = $analyse;
        parent::__construct($analyse, $name, $path);
    }


    /**
     * Set name
     *
     * @param string $name
     * @return Parameter
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return parent::getName();
    }

    /**
     * Set defaultInput
     *
     * @param float $defaultInput
     * @return Parameter
     */
    public function setDefaultInput($defaultInput)
    {
        parent::setDefaultInput($defaultInput);

        return $this;
    }

    /**
     * Get defaultInput
     *
     * @return float
     */
    public function getDefaultInput()
    {
        return parent::getDefaultInput();
    }

    public function __clone() {
      if ( parent::getId() ) {
          parent::setId(null);

      }
#die('debbug clone Obs');
    }

    /**
     * Set allowedRangeMax
     *
     * @param float $allowedRangeMax
     * @return Parameter
     */
    public function setAllowedRangeMax($allowedRangeMax)
    {
        parent::setAllowedRangeMax($allowedRangeMax);

        return $this;
    }

    /**
     * Get allowedRangeMax
     *
     * @return float
     */
    public function getAllowedRangeMax()
    {
        return parent::getAllowedRangeMax();
    }

    /**
     * Set allowedRangeMin
     *
     * @param float $allowedRangeMin
     * @return Parameter
     */
    public function setAllowedRangeMin($allowedRangeMin)
    {
        parent::setAllowedRangeMin($allowedRangeMin);

        return $this;
    }

    /**
     * Get allowedRangeMin
     *
     * @return float
     */
    public function getAllowedRangeMin()
    {
        return parent::getAllowedRangeMin();
    }

    /**
     * Set associatedElement
     *
     * @param array $associatedElement
     * @return Observable
     */
    public function setAssociatedElement($associatedElement)
    {
        $this->associatedElement = $associatedElement;

        return $this;
    }

    public function createAssociatedElement($datacard)
    {
      return $this->getParameterForOneObservable( $datacard );
    }



    public function getParameterNameForObservable($scenarioPath){
      $data = file_get_contents($scenarioPath) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);

      $new_line = "^\n$" ;
      $observablePattern =  '/'.preg_quote( $this->getName(), '/' ).'/';
      // info obs
      $tmp_ar_obs = array();
      // params de l obs
      $tmp_ar_obsParam = array();
      // info param
      $tmp_ar_param = array();
      // array des param a retourner
      $tmp_ar_params = array();

      // recherche des params de l observable
      foreach($lines as $line) {
        if( ! preg_match("/$new_line/", $line) ) {
          if( preg_match($observablePattern, $line) ) {
            $tmp_ar_obs = explode(';',$line);
            # les parametres associes a une observable sont le 4eme item dans le fichier :index=4
            $tmp_ar_obsParam = explode(',',$tmp_ar_obs['4']);
            break;
          }
        }
      }
      return $tmp_ar_obsParam;
    }

    private function getParameterForOneObservable($scenarioPath){
      $data = file_get_contents($scenarioPath) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);

      $new_line = "^\n$" ;
      $observablePattern =  '/'.preg_quote( $this->getName(), '/' ).'/';
      // info obs
      $tmp_ar_obs = array();
      // params de l obs
      $tmp_ar_obsParam = array();
      // info param
      $tmp_ar_param = array();
      // array des param a retourner
      $tmp_ar_params = array();

      // recherche des params de l observable
      foreach($lines as $line) {
        if( ! preg_match("/$new_line/", $line) ) {
          if( preg_match($observablePattern, $line) ) {
            $tmp_ar_obs = explode(';',$line);
            # les parametres associes a une observable sont le 6eme item dans le fichier :index=3
            $tmp_ar_obsParam = explode(',',$tmp_ar_obs['4']);
            break;
          }
        }
      }
      $type='';

      // construction des params de l observable
      if( count($tmp_ar_obsParam) > 0) {
        foreach($tmp_ar_obsParam as $param) {

          $paramPattern =  '/^'.preg_quote( $param, '/' ).'$/';
          $searchOK=false;

          foreach($lines as $line) {
            if( ! preg_match("/$new_line/", $line) ) {
              if( preg_match('/# parameter/', $line) ) {
                  $type='parameter';
              }
              elseif( $type==='parameter' ) {
                $tmp_ar_param = explode(';',$line);
                if( preg_match($paramPattern, preg_quote($tmp_ar_param['0']) ) ) {
                  $tmp_obj_param = new Parameter($this->getAnalyse(), $tmp_ar_param['0'], '', $tmp_ar_param['1'], $tmp_ar_param['2'], $tmp_ar_param['3'] ) ;

                  array_push($tmp_ar_params, $tmp_obj_param );
                  $searchOK=true;
                  break;
                }
              }
            }
          }
          if(!$searchOK) {
            die("Pas de parametres $param pour l'observable : ". $this->getName() );
          }
        }
      }
      return $tmp_ar_params;
    }

    /**
     * Get associatedElement
     *
     * @return array
     */
    public function getAssociatedElement()
    {
        return $this->associatedElement;
    }

    /**
     * Set analyse
     *
     * @param \CKM\AppBundle\Entity\Analysis $analyse
     * @return Observable
     */
    public function setAnalyse(\CKM\AppBundle\Entity\Analysis $analyse)
    {
        parent::setAnalyse($analyse);

        return $this;
    }

    /**
     * Get analyse
     *
     * @return \CKM\AppBundle\Entity\Analysis
     */
    public function getAnalyse()
    {
        return parent::getAnalyse();
    }

    /**
     * Add parameters
     *
     * @param \CKM\AppBundle\Entity\Parameter $parameters
     * @return Observable
     */
    public function addParameter(\CKM\AppBundle\Entity\Parameter $parameters)
    {
        $this->parameters[] = $parameters;
        $parameters->addObservable($this);

        #echo 'addParameter Obs: '.$this->getName().' - '.$this->getId().'<br />';
        #echo 'addParameter Param: '.$parameters->getName().' - '.$parameters->getId().'<br />';

        return $this;
    }

    /**
     * Remove parameters
     *
     * @param \CKM\AppBundle\Entity\Parameter $parameters
     */
    public function removeParameter(\CKM\AppBundle\Entity\Parameter $parameters)
    {
        $this->parameters->removeElement($parameters);
    }

    /**
     * Get parameters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setParameters($parameter)
    {
        $this->parameters=$parameter;
        return $this;
    }
}
