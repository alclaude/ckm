<?php
namespace CKM\AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class RetrieveUserByIDAnalysisCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ckm:retrieveuserbyidanalysis')
             ->addArgument('ID')
             ->addArgument('pathDat')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $ID      = $input->getArgument('ID');
      $nameFileDat = $input->getArgument('pathDat');
           
      if ( ! $ID or ! $nameFileDat) {
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
          exit("Bad analysis Id: $ID - $nameFileDat");
      } 
      
      $user = $analysisToFinalize->getUser();
      #$analysisToFinalize->setResultDat();
      #echo $user->getEmail();

      $fs = new Filesystem();
      $resultRepository = __DIR__.'/resultCKMAnalysis/';
      
      $pathDat = $resultRepository.$ID.'/'.$nameFileDat;

      if( $fs->exists($pathDat) ) {
        try {
          if( ! $analysisToFinalize->getResultDat() ) {
            $contentDat = file_get_contents($pathDat);
            $analysisToFinalize->setResultDat($contentDat);
            $analysisToFinalize->setStatus(4);
            $em->getManager()->persist( $analysisToFinalize );
            $em->getManager()->flush();
            
            # envoi du mail
            $message = \Swift_Message::newInstance()
                ->setSubject('incoming result analysis '.$nameFileDat)
                #->setFrom('ckmliveweb@gmail.com')
                ->setFrom( array( 'ckmliveweb@in2p3.fr' => 'CKM Live Web' ) )
                ->setTo($user->getEmail())
                ->setBody($template->render('CKMUserBundle:Mail:resultNotification.txt.twig', array('user' => $user->getName(), 'analysis' => $nameFileDat ) ) )
            ;
            $mail->send($message);
            
            # in case of bad result, admin are warned
            if( preg_match('/overtime\.dat/', $nameFileDat, $matches) or
                preg_match('/error\.dat/',    $nameFileDat, $matches) ) {

              $message = \Swift_Message::newInstance()
                ->setSubject('[Admin] bad incoming result analysis '.$nameFileDat)
                ->setFrom( array( 'ckmliveweb@in2p3.fr' => 'CKM Live Web' ) )
                ->setTo( $this->getContainer()->getParameter('email_admin') )
                ->setBody($template->render('CKMUserBundle:Mail:AdminBadResultNotification.txt.twig', array('user' => $user->getName(), 'analysis' => $nameFileDat, "id" => $ID ) ) )
              ;
              $mail->send($message);
            }
            
          }
          else { echo "analysis $ID contains already a result"; }
        } catch (IOException $e) {
            echo "An error occured while creating your directory ". $pathDat."\n LOG \n".$e."\n";
        }
      }
      else {
        echo 'file '.$pathDat. ' non existant'."\n";
      }
    }
}
