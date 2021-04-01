<?php

namespace Mashbo\FormFlowBundle\Events;

use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Contracts\EventDispatcher\Event;

class FlowWasStarted extends Event
{
    private FlowContext $context;

    public function __construct(FlowContext $context)
    {
        $this->context = $context;
    }

    public function getContext(): FlowContext
    {
        return $this->context;
    }
}