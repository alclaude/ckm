<?php
namespace CKM\AppBundle\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
class ToPlotCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ckm:toplot')
        ->setDescription('')
        ->setHelp('')
        ;
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $em = $this->getContainer()->get('doctrine');
      $analysisToRun = $em
            ->getRepository('CKMAppBundle:Analysis')
            ->findAnalysisByStatus( 4 );
      #\Doctrine\Common\Util\Debug::dump($analysisToRun);
      $fs = new Filesystem();
      $currentRepository = __DIR__.'/resultCKMAnalysis/';
      $listtoPlot = array();
      foreach($analysisToRun as $analysis) {
        $analysisRep = $currentRepository.$analysis->getId().'.end';

        #echo $analysis->getId()."\n";

        # on recupere le plot de l nalyse ayant le NumberOfPlot le plus grand
        $plotToRun = $em
            ->getRepository('CKMAppBundle:Plotting')
            ->findLastPlottingByAnalysis($analysis->getId());

        if($plotToRun) {
          #echo \Doctrine\Common\Util\Debug::dump($plotToRun);
          # on verifie absence de eps qui devrait tjs etre present comme format
          if( !$plotToRun->getPathEps() ) {
            //echo "\n $analysisRep : rep analysis exist\n";
            if( $fs->exists($analysisRep) ) {
              $analysisResultFile = $analysisRep.'/'.$analysis->getName().'.dat';
              if( $fs->exists($analysisResultFile) ) {
                # TODO
                # envoyer liste des repertoires ayant analyses a plotter
                # pour que le Perl:
                # 1/ cree le repertoire plotX (recuperer X par le php)
                # 2/ envoi du .dat sur clrlhcbsrv
                $plotDir = $analysisRep.'/plot'.$plotToRun->getNumberOfPlot();
                # extensions du dossier
                # toplot : plot a envoyer
                # run    : plot envoye / en attente d un resultat
                # end    : possede un resultat / acheve

                if( !$fs->exists($plotDir.'.end') 
                 and !$fs->exists($plotDir.'.runplot') 
                 and !$fs->exists($plotDir.'.toplot') 
                ) {
                  //echo "\nmkdir php\n";
		# alx test
                  $fs->mkdir($plotDir.'.toplot', 0700);
                  //$analysisFile = $plotDir.'.toplot/'.$analysis->getName().'.dat';
                  // create file .dat with format title____nickname____scenario-tag____dimension.dat
                  $title = preg_replace("# +#", "-",$plotToRun->getTitle());
                  $nickname = preg_replace("# +#", "-",$plotToRun->getNickname());
                  $analysisFile = $plotDir.'.toplot/'.$title.'____'.$nickname.'____'.$analysis->getScenario()->getTag().'____'.$analysis->getScanConstraint().'.dat';
                  //echo $analysisFile."\n";

                  ini_set('memory_limit', '1024M');

                  $fs->touch($analysisFile);

                  $result = $analysis->getResultDat();
                  $result = preg_replace("#__NO__TITLE__#", $plotToRun->getTitle(),$result);
                  $result = preg_replace("#__NO__NICKNAME__#", $plotToRun->getNickname(),$result);
                  
                  file_put_contents( $analysisFile, $result );
                }
              }
            }
          } // if no existing eps
        } // if plot to run

      } // foreach analysis
    
    }
}
