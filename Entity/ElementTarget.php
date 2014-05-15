<?php

namespace CKM\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\ExecutionContextInterface;

use CKM\AppBundle\Entity\ParameterInput;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElementTarget
 *
 * @ORM\Table(name="element_target")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ElementTargetRepository")
 *
 */
class ElementTarget
{
    /**
     * @ORM\ManyToOne(targetEntity="CKM\AppBundle\Entity\Analysis")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $analyse;

   /**
    * @ORM\ManyToMany(targetEntity="CKM\AppBundle\Entity\ParameterInput", inversedBy="elementTarget", cascade={"persist", "remove"})
    * @ORM\JoinTable(name="observables_parameters_target",
    *      joinColumns={@ORM\JoinColumn(nullable=false, onDelete="CASCADE")},
    *      inverseJoinColumns={@ORM\JoinColumn(nullable=false)}
    *      )
    */
    private $parameters;

    /**
     * @ORM\OneToOne(targetEntity="CKM\AppBundle\Entity\ObservableInput", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $observable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="default_input", type="float")
     *
     */
    private $defaultInput;

    /**
     * @var float
     *
     * @ORM\Column(name="allowed_range_max", type="float")
     */
    private $allowedRangeMax;

    /**
     * @var float
     *
     * @ORM\Column(name="allowed_range_min", type="float")
     */
    private $allowedRangeMin;

    /**
     * @var float
     *
     * @ORM\Column(name="scan_max", type="float")
     * @Assert\Type(type="float")
     */
    private $scanMax;

    /**
     * @var float
     *
     * @ORM\Column(name="scan_min", type="float")
     */
    private $scanMin;

    public function __construct($analyse, $name='', $defaultInput=0, $allowedRangeMax=0, $allowedRangeMin=0)
    {
      $this->name            = $name;
      $this->defaultInput    = $defaultInput;
      $this->allowedRangeMax = $allowedRangeMax;
      $this->allowedRangeMin = $allowedRangeMin;
      $this->analyse         = $analyse;
      $this->scanMax         = 0.0;
      $this->scanMin         = 0.0;

      $this->parameters = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function isScanValid(ExecutionContextInterface $context)
    {
        // Vous avez un tableau de « faux noms »
        $fakeNames = array();

        // vérifie si le nom est un faux
        if ($this->getScanMax() < $this->getScanMin() ) {
            $context->addViolationAt('scanMin', 'scanMax must be greater than scanMin ', array(), null);
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ElementTarget
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
        return $this->name;
    }

    /**
     * Set defaultInput
     *
     * @param float $defaultInput
     * @return ElementTarget
     */
    public function setDefaultInput($defaultInput)
    {
        $this->defaultInput = $defaultInput;

        return $this;
    }

    /**
     * Get defaultInput
     *
     * @return float
     */
    public function getDefaultInput()
    {
        return $this->defaultInput;
    }

    /**
     * Set allowedRangeMax
     *
     * @param float $allowedRangeMax
     * @return ElementTarget
     */
    public function setAllowedRangeMax($allowedRangeMax)
    {
        $this->allowedRangeMax = $allowedRangeMax;

        return $this;
    }

    /**
     * Get allowedRangeMax
     *
     * @return float
     */
    public function getAllowedRangeMax()
    {
        return $this->allowedRangeMax;
    }

    /**
     * Set allowedRangeMin
     *
     * @param float $allowedRangeMin
     * @return ElementTarget
     */
    public function setAllowedRangeMin($allowedRangeMin)
    {
        $this->allowedRangeMin = $allowedRangeMin;

        return $this;
    }

    /**
     * Get allowedRangeMin
     *
     * @return float
     */
    public function getAllowedRangeMin()
    {
        return $this->allowedRangeMin;
    }

    /**
     * Set analyse
     *
     * @param \CKM\AppBundle\Entity\Analysis $analyse
     * @return ElementTarget
     */
    public function setAnalyse(\CKM\AppBundle\Entity\Analysis $analyse)
    {
        $this->analyse = $analyse;

        return $this;
    }

    /**
     * Get analyse
     *
     * @return \CKM\AppBundle\Entity\Analysis
     */
    public function getAnalyse()
    {
        return $this->analyse;
    }

    /**
     * Set scanMax
     *
     * @param float $scanMax
     * @return ElementTarget
     */
    public function setScanMax($scanMax)
    {
        $this->scanMax = $scanMax;

        return $this;
    }

    /**
     * Get scanMax
     *
     * @return float
     */
    public function getScanMax()
    {
        return $this->scanMax;
    }

    /**
     * Set scanMin
     *
     * @param float $scanMin
     * @return ElementTarget
     */
    public function setScanMin($scanMin)
    {
        $this->scanMin = $scanMin;

        return $this;
    }

    /**
     * Get scanMin
     *
     * @return float
     */
    public function getScanMin()
    {
        return $this->scanMin;
    }

    /**
     * Add parameter
     *
     * @param \CKM\AppBundle\Entity\ParameterInput $parameter
     * @return ElementTarget
     */
    public function addParameter(\CKM\AppBundle\Entity\ParameterInput $parameter)
    {
        $this->parameters[] = $parameter;
        $parameter->addElementTarget($this);

        return $this;
    }

    /**
     * Remove parameter
     *
     * @param \CKM\AppBundle\Entity\ParameterInput $parameter
     */
    public function removeParameter(\CKM\AppBundle\Entity\ParameterInput $parameter)
    {
        $this->parameters->removeElement($parameter);
    }

    public function createAssociatedElement($datacard)
    {
      return $this->getParameterForOneObservable( $datacard );
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
            $tmp_ar_obsParam = explode(',',$tmp_ar_obs['7']);
            break;
          }
        }
      }
      $type='';

      // construction des params de l observable
      if( count($tmp_ar_obsParam) > 0) {
        foreach($tmp_ar_obsParam as $param) {

          $paramPattern =  '/^'.preg_quote( $param, '/' ).'$/';

          foreach($lines as $line) {
            if( ! preg_match("/$new_line/", $line) ) {
              if( preg_match('/# parameter/', $line) ) {
                  $type='parameter';
              }
              elseif( $type==='parameter' ) {
                $tmp_ar_param = explode(';',$line);
                if( preg_match($paramPattern, preg_quote($tmp_ar_param['0']) ) ) {
                  $tmp_obj_param = new ParameterInput($this, $tmp_ar_param['0'], $tmp_ar_param['1'], $tmp_ar_param['2'], $tmp_ar_param['3'], $tmp_ar_param['4'], $tmp_ar_param['5'],$tmp_ar_param['6'] ) ;

                  array_push($tmp_ar_params, $tmp_obj_param );
                  break;
                }
              }
            }
          }
        }
      } else  {
        die("Pas de parametres pour l'observable : $this->getName()");
      }
      return $tmp_ar_params;
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
}
