<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\BeforeHandlerEvent;
use Mashbo\FormFlowBundle\Events\FlowSucceeded;
use Mashbo\FormFlowBundle\Events\SubjectWasDetermined;
use Mashbo\FormFlowBundle\Exceptions\WorkflowTransitionNotAvailable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Registry;

class WorkflowEventSubscriber implements EventSubscriberInterface
{
    private string $workflow;
    private string $transition;
    private Registry $registry;
    private string $flowName;
    private bool $applied = false;

    public function __construct(string $flowName, Registry $registry, string $workflow, string $transition)
    {
        $this->workflow = $workflow;
        $this->transition = $transition;
        $this->registry = $registry;
        $this->flowName = $flowName;
    }

    public function onBeforeHandler(BeforeHandlerEvent $event): void
    {
        if ($event->getContext()->getName() !== $this->flowName) {
            return;
        }

        $subject = $event->getContext()->subject;

        /** @psalm-suppress PossiblyNullArgument $workflow */
        $workflow = $this->registry->get($subject, $this->workflow);

        if (!$workflow->can($subject, $this->transition)) {
            throw new WorkflowTransitionNotAvailable($subject, $this->workflow, $this->transition);
        }

    }

    public function onFlowSucceeded(FlowSucceeded $event): void
    {
        if ($event->getContext()->getName() !== $this->flowName) {
            return;
        }

        // Prevent attempting the workflow transition twice for multiple FlowSucceeded events
        if ($this->applied) {
            return;
        }

        $subject = $event->getContext()->subject;

        /** @psalm-suppress PossiblyNullArgument $workflow */
        $workflow = $this->registry->get($subject, $this->workflow);

        $workflow->apply($subject, $this->transition);
        $this->applied = true;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeHandlerEvent::class => 'onBeforeHandler',
            FlowSucceeded::class => 'onFlowSucceeded'
        ];
    }
}
