<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\FlowWasStarted;
use Mashbo\FormFlowBundle\Events\SubjectWasDetermined;
use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddSubjectToContextWhenDeterminedEventSubscriber implements EventSubscriberInterface
{
    private ?FlowContext $latestContext = null;

    public function onFlowWasStarted(FlowWasStarted $event): void
    {
        $this->latestContext = $event->getContext();
    }
    public function onSubjectWasDetermined(SubjectWasDetermined $event): void
    {
        if ($this->latestContext === null) {
            throw new \LogicException("Context has not been set yet. Has the flow started?");
        }
        $this->latestContext->setSubject($event->getSubject());
    }
    public static function getSubscribedEvents(): array
    {
        return [
            FlowWasStarted::class => 'onFlowWasStarted',
            SubjectWasDetermined::class => 'onSubjectWasDetermined'
        ];
    }
}
