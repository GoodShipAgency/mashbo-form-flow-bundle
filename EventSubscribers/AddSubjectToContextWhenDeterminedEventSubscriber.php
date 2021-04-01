<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\FlowWasStarted;
use Mashbo\FormFlowBundle\Events\SubjectWasDetermined;
use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddSubjectToContextWhenDeterminedEventSubscriber implements EventSubscriberInterface
{
    private FlowContext $latestContext;

    public function onFlowWasStarted(FlowWasStarted $event)
    {
        $this->latestContext = $event->getContext();
    }
    public function onSubjectWasDetermined(SubjectWasDetermined $event)
    {
        $this->latestContext->setSubject($event->getSubject());
    }
    public static function getSubscribedEvents()
    {
        return [
            FlowWasStarted::class => 'onFlowWasStarted',
            SubjectWasDetermined::class => 'onSubjectWasDetermined'
        ];
    }
}