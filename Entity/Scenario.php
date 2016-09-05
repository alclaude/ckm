<?php

namespace CKM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Mapping\ClassMetadata;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Scenario
 *
 * @ORM\Table(name="ckm_scenario")
 * @ORM\Entity(repositoryClass="CKM\AppBundle\Entity\ScenarioRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 */
class Scenario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must specify one scenario", groups={"choice"}
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var file
     *
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"text/plain"},
     *     maxSizeMessage = "The maxmimum allowed file size is 5MB.",
     *     mimeTypesMessage = "Only the filetypes text/plain are allowed."
     * )
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_create", type="datetime")
     */
    private $dateCreate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_update", type="datetime")
     */
    private $dateUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=255)
     */
    private $tag;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_documented", type="boolean")
     */
    private $isDocumented=false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_development", type="boolean")
     */
    private $isDevelopment=false;

    /**
     * @ORM\ManyToOne(targetEntity="CKM\AppBundle\Entity\Model")
     * @ORM\JoinColumn(nullable=false)
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="documentation", type="text")
     */
    private $documentation='';

    public function __construct()
    {
        $this->path = "/home/alclaude/Documents/DEV_PHP/ckm-web2/src/CKM/globalCKMfit_scenario.txt";

        $this->dateCreate = new \DateTime();
        $this->dateUpdate = new \DateTime();
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

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

/*    public function __toString() {
      return $this->getName();
    }
*/

    /**
     * Set path
     *
     * @param string $path
     * @return Datacard
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set dateCreate
     *
     * @param \DateTime $dateCreate
     * @return Datacard
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Get dateCreate
     *
     * @return \DateTime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return Datacard
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    public function getInput()
    {
      $data = file_get_contents( $this->getWebPath() ) or die("fichier non trouv&eacute;");
      $lines = explode("\n", $data);


      $type='';
      $new_line = "^\n$" ;
      $input_ar = array();

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
              #$obs_ar[ "$tmp_ar[0]" ] = $tmp_ar[0];
              $input_ar[] = $tmp_ar[0];
            }
            if( $type==='parameter' ) {
              $tmp_ar = explode(';',$line);
              #$param_tmp = new Parameter($tmp_ar['0'], $tmp_ar['0'], 1, 2);
              #$param_ar[ "$tmp_ar[0]" ] = $param_tmp;
              #$param_ar[ "$tmp_ar[0]" ] = $tmp_ar[0];
              $input_ar[] = $tmp_ar[0];
            }
          }
        }

      }
      return $input_ar;

    }
   
    public function cleanFile($observables, $parameters)
    {
      #list($observables, $parameters) = $this->getInputLineInFile();
      $handle = fopen($this->getWebPath(), 'w');
      
      $answers = array_merge(
        array('# observable;default input;min allowed value;max allowed value;associated parameters;input tag'),
        $observables,
        array('# parameter;default input;min allowed value;max allowed value;input tag'),
        $parameters
      );

      $data='';
      foreach ($answers as $answer) {
        if( !preg_match("/^\s*$/",$answer) ) {
          $answer=preg_replace("/\s*$/","",$answer);
          $data.=$answer."\n";
          #fwrite($handle, $answer."\n");
        }
      }
      $data=rtrim($data);
      fwrite($handle, $data);
      fclose($handle);
    }
    
    public function getInputLineInFile()
    {
      $data = file_get_contents( $this->getWebPath() ) or die("fichier non trouv&eacute;");
      $lines = explode(PHP_EOL, $data);

      $type='';
      $new_line = "^\n$" ;
      $observables = array();
      $parameters  = array();

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
              $observables[] = $line;
            }
            if( $type==='parameter' ) {
              $parameters[] = $line;
            }
          }
        }
      }
      
      return array($observables, $parameters);
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'uploads/datacards';
    }

    public function getParameter()
    {}

    /**
     * Set name
     *
     * @param string $name
     * @return Datacard
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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // faites ce que vous voulez pour générer un nom unique
            $this->path = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        $this->file->move($this->getUploadRootDir(), $this->path);
        
        list($observables, $parameters) = $this->getInputLineInFile();     
        $this->cleanFile($observables, $parameters);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * Set tag
     *
     * @param string $tag
     * @return Scenario
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set isDocumented
     *
     * @param boolean $isDocumented
     * @return Scenario
     */
    public function setIsDocumented($isDocumented)
    {
        $this->isDocumented = $isDocumented;

        return $this;
    }

    /**
     * Get isDocumented
     *
     * @return boolean
     */
    public function getIsDocumented()
    {
        return $this->isDocumented;
    }

    /**
     * Set model
     *
     * @param \CKM\AppBundle\Entity\Model $model
     * @return Scenario
     */
    public function setModel(\CKM\AppBundle\Entity\Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return \CKM\AppBundle\Entity\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set documentation
     *
     * @param string $documentation
     * @return Scenario
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;

        return $this;
    }

    /**
     * Get documentation
     *
     * @return string 
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Set isDevelopment
     *
     * @param boolean $isDevelopment
     * @return Scenario
     */
    public function setIsDevelopment($isDevelopment)
    {
        $this->isDevelopment = $isDevelopment;

        return $this;
    }

    /**
     * Get isDevelopment
     *
     * @return boolean 
     */
    public function getIsDevelopment()
    {
        return $this->isDevelopment;
    }
}
