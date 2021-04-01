<?php

namespace Mashbo\FormFlowBundle\Exceptions;

class NoSuchFlow extends \LogicException
{
    public function __construct(string $name)
    {
        parent::__construct("No flow is defined named $name. Check for typos or define the flow in config");
    }
}