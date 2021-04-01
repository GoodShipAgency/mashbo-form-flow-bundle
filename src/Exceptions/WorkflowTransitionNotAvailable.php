<?php

namespace Mashbo\FormFlowBundle\Exceptions;

class WorkflowTransitionNotAvailable extends \RuntimeException
{
    public function __construct(?object $subject, string $workflow, string $transition)
    {
        parent::__construct(
            "The transition $transition on workflow $workflow is not available" .
            (isset($subject) ? ' on this instance of ' . get_class($subject) : '')
        );
    }
}