<?php

namespace Mashbo\FormFlowBundle\Events;

use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Contracts\EventDispatcher\Event;

class FlowFailed extends Event
{
    private FlowContext $context;
    private \Throwable $throwable;

    public function __construct(FlowContext $context, \Throwable $throwable)
    {
        $this->context = $context;
        $this->throwable = $throwable;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }

    public function getContext(): FlowContext
    {
        return $this->context;
    }
}