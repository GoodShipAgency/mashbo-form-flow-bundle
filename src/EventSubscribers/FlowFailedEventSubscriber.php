<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\FlowFailed;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;

class FlowFailedEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FlowFailed::class => 'onFlowFailed'];
    }

    public function onFlowFailed(FlowFailed $event): void
    {
        $form = $event->getContext()->form;
        $throwable = $event->getThrowable();

        if ($throwable instanceof \DomainException) {
            $form->addError(new FormError($throwable->getMessage()));
        }

    }
}