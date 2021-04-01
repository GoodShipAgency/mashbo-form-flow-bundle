<?php

namespace Mashbo\FormFlowBundle\FlowHandlers;

use Mashbo\FormFlowBundle\FlowContext;

interface FlowHandler
{
    public function __invoke($data, FlowContext $context): void;
}