<?php
namespace CKM\AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class AchievementPlottingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ckm:achievementplot')
             ->addArgument('ID')
             ->addArgument('filePlotName')
             ->addArgument('pathRepPlot')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $ID      = $input->getArgument('ID');
      $nameFilePlot = $input->getArgument('filePlotName');
      $repPlot = $input->getArgument('pathRepPlot');
      
      if ( ! $ID or ! $nameFilePlot or ! $repPlot) {
          exit("no ID or pathDat given");
      }

      ## start
      $em = $this->getContainer()->get('doctrine');
      $template = $this->getContainer()->get('templating');
      $mail = $this->getContainer()->get('mailer');
      
      $analysisToFinalize = $em
            ->getRepository('CKMAppBundle:Analysis')
            ->findOneById( $ID );
      #\Doctrine\Common\Util\Debug::dump($analysisToFinalize);

      if ( ! $analysisToFinalize) {
          exit("Bad analysis Id: $ID - $nameFilePlot");
      } 
      
      $user = $analysisToFinalize->getUser();
      #$analysisToFinalize->setResultDat();
      #echo $user->getEmail();

      $fs = new Filesystem();
      $resultRepository = __DIR__.'/Plot_resultCKMAnalysis/';
      
      # pathPlot =  pathDat
      $pathPlot = $resultRepository.$ID.'/'.$repPlot.'/'.$nameFilePlot;

      if( $fs->exists($pathPlot) ) {
        try {
          # TODO trouver  moyen de matcher resultat avec ckm_plotting getpath_pdf getpath_eps ou getpath_pdf en fonction de $nameFilePlot
          if (preg_match("/(.*?\.(eps|png|pdf))$/i", $nameFilePlot, $extension)) {
            if (preg_match("/plot(\d*)\.toplot$/i", $repPlot, $numberOfPlot)) {
            

              $plotToRun = $em
                ->getRepository('CKMAppBundle:Plotting')
                ->findPlottingByAnalysisAndNumberOfPlot($analysisToFinalize->getId(), $numberOfPlot[1]);

              if($plotToRun){
                $hasResult = '';
                switch ($extension[2]) {
                    case 'eps':
                        $hasResult = $plotToRun->getPathEps();
                        break;
                    case 'pdf':
                        $hasResult = $plotToRun->getPathPdf();
                        break;
                    case 'png':
                        $hasResult = $plotToRun->getPathPng();
                        break;
                }
                #echo "Analyse : ".$analysisToFinalize->getId()."  --  ". $numberOfPlot[1]." - ".$extension[2]."\n" ;
                if( ! $hasResult ) {
                  
                  switch ($extension[2]) {
                      case 'eps':
                          $plotToRun->setPathEps($nameFilePlot);
                          break;
                      case 'pdf':
                          $plotToRun->setPathPdf($nameFilePlot);
                          break;
                      case 'png':
                          $plotToRun->setPathPng($nameFilePlot);
                          break;
                  }
                  $em->getManager()->persist( $plotToRun );
                  $em->getManager()->flush();
                  
                  # envoi du mail
                  $message = \Swift_Message::newInstance()
                      ->setSubject('incoming result analysis '.$nameFileDat)
                      #->setFrom('ckmliveweb@gmail.com')
                      ->setFrom( array( 'ckmliveweb@in2p3.fr' => 'CKM Live Web' ) )
                      ->setTo($user->getEmail())
                      ->setBody($template->render('CKMUserBundle:Mail:plotNotification.txt.twig', array('user' => $user->getName(), 'plot' => $nameFilePlot ) ) )
                  ;
                  $mail->send($message);
                  /*
                  # in case of bad result, admin are warned
                  if( preg_match('/overtime\.dat/', $nameFileDat, $matches) or
                      preg_match('/error\.dat/',    $nameFileDat, $matches) ) {

                    $message = \Swift_Message::newInstance()
                      ->setSubject('[Admin] bad incoming result analysis '.$nameFileDat)
                      ->setFrom( array( 'ckmliveweb@in2p3.fr' => 'CKM Live Web' ) )
                      ->setTo( $this->getContainer()->getParameter('email_admin') )
                      ->setBody($template->render('CKMUserBundle:Mail:AdminBadResultNotification.txt.twig', array('user' => $user->getName(), 'login' => $user->getUsername(), 'analysis' => $nameFileDat, "id" => $ID ) ) )
                    ;
                    $mail->send($message);
                  }
                  */
                } else { echo "analysis $ID contains already a result number of plot ".$numberOfPlot[1]." in format ".$extension[2]; }
              }
            }
          }
        } catch (IOException $e) {
            echo "An error occured while creating your directory ". $pathDat."\n LOG \n".$e."\n";
        }
      }
      else {
        echo 'file '.$pathDat. ' non existant'."\n";
      }
    }
}
