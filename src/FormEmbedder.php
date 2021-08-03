<?php

namespace Mashbo\FormFlowBundle;

use Mashbo\FormFlowBundle\Events\BeforeFormCreationEvent;
use Mashbo\FormFlowBundle\Events\BeforeFormEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

class FormEmbedder
{
    private FlowRegistry $registry;
    private EventDispatcherInterface $dispatcher;

    public function __construct(FlowRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
    }

    public function embedForm(string $flowName, ?object $subject = null): FormInterface
    {
        $flow = $this->registry->findFlow($flowName);

        $beforeFormCreationEvent = new BeforeFormCreationEvent($flow, $subject);
        $this->dispatcher->dispatch($beforeFormCreationEvent);

        $form = $flow->getForm($beforeFormCreationEvent->getData());
        $context = new FlowContext($flow, $flowName, null, $form);
        $context->subject = $subject;

        $this->dispatcher->dispatch(new BeforeFormEvent($context));

        return $form;
    }
}