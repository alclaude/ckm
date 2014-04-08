<?php
// src/Blogger/AppBundle/Controller/PageController.php
namespace CKM\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Import new namespaces
#use Blogger\BlogBundle\Entity\Enquiry;
#use Blogger\BlogBundle\Form\EnquiryType;

class PageController extends Controller
{
    public function indexAction()
    {
      return $this->render('CKMAppBundle:Page:index.html.twig', array(
      ));
    }

    public function communicationAction()
    {
      return $this->render('CKMAppBundle:Page:communication.html.twig', array(
      ));
    }

}
