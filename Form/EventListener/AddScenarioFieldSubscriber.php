<?php

namespace CKM\AppBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;


class AddScenarioFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }

    private function addScenarioForm($form, $model_id)
    {
        $formOptions = array(
            'class'         => 'CKMAppBundle:Scenario',
            'empty_value'   => 'Scenario',
            'label'         => 'Scenario',
            'mapped'        => false,
            'property' => 'name',
            'attr'          => array(
                'class' => 'form-control',
            ),
            'query_builder' => function (EntityRepository $repository) use ($model_id) {
                $qb = $repository->createQueryBuilder('scenario')
                    ->innerJoin('scenario.model', 'model')
                    ->where('model.id = :model')
                    ->setParameter('model', $model_id)
                ;

                return $qb;
            }
        );

        $form->add('scenario', 'entity', $formOptions);
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
die('debbug');
*/
        #$accessor    = PropertyAccess::createPropertyAccessor();



        $scenario        = $data->getScenario();
        $model_id = ($scenario) ? $scenario->getModel()->getId() : null;


        $this->addScenarioForm($form, $model_id);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $model_id = array_key_exists('model', $data) ? $data['model'] : null;

        $this->addScenarioForm($form, $model_id);
    }
}