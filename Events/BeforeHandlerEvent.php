<?php

namespace Mashbo\FormFlowBundle\Events;

use Mashbo\FormFlowBundle\FlowContext;

class BeforeHandlerEvent
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