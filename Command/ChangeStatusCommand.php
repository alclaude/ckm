<?php
namespace CKM\AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class ChangeStatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ckm:changestatus');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      ## start
      $em = $this->getContainer()->get('doctrine');
      $analysisToRun = $em
            ->getRepository('CKMAppBundle:Analysis')
            ->findAnalysisByStatus( 2 );
      #\Doctrine\Common\Util\Debug::dump($analysisToRun);

      $fs = new Filesystem();
      $currentRepository = __DIR__.'/inprogress/';
      #$currentRepository = '/root/';


      foreach($analysisToRun as $analysis) {
        $analysisRep = $currentRepository.$analysis->getId();

        if( $fs->exists($analysisRep.'.run') ) {
          try {
            # file .data
            $analysisFile = $analysisRep.'/'.$analysis->getId().'.data';

            # TODO
            # .m file
            $analysis->setStatus(3);
            $em->getManager()->persist( $analysis );
            $em->getManager()->flush();
          } catch (IOException $e) {
              echo "An error occured while creating your directory ". $analysisRep."\n LOG \n".$e."\n";
          }
        }
        else {
          echo 'repertoire '.$analysisRep. ' non existant'."\n";
        }
      }
    }
}
