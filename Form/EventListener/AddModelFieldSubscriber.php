<?php

namespace CKM\AppBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;


class AddModelFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }

    private function addModelForm($form, $model=null)
    {
        $formOptions = array(
            'class'         => 'CKMAppBundle:Model',
            'empty_value'   => 'Model',
            'mapped'        => false,
            'label'         => 'Model',
            'property' => 'name',
            'attr'          => array(
                'class' => 'form-control',
            ),
        );

        if ($model) {
            $formOptions['data'] = $model;
        }

        $form->add('model', 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }
/*
echo '<pre>';
\Doctrine\Common\Util\Debug::dump($data);
\Doctrine\Common\Util\Debug::dump($form);
echo '</pre>';
die('debbugModel');
*/

        #$accessor    = PropertyAccess::createPropertyAccessor();

        $scenario        = $data->getScenario();
        $model = ($scenario) ? $scenario->getModel() : null;


        $this->addModelForm($form, $model);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $model = array_key_exists('model', $data) ? $data['model'] : null;

        $this->addModelForm($form, $model);
    }
}