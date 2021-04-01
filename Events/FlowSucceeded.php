<?php

namespace Mashbo\FormFlowBundle\Events;

use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event may be fired multiple times during the same flow, for example if
 * the workflow wrapper is active, once when the underlying handler succeeds and
 * once when the workflow transition is applied
 *
 * The finalised parameter indicates whether everything has been done, no event listeners should
 * prevent this from firing at this point. Workflow transitions should have run, transactions should have been committed.
 *
 * This would be a sensible point to send emails, queue jobs, etc...
 */
class FlowSucceeded extends Event
{
    private FlowContext $context;
    private bool $finalised;

    public function __construct(FlowContext $context, bool $finalised)
    {
        $this->context = $context;
        $this->finalised = $finalised;
    }

    public function getContext(): FlowContext
    {
        return $this->context;
    }

    public function isFinalised(): bool
    {
        return $this->finalised;
    }
}
