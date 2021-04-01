<?php

namespace Mashbo\FormFlowBundle\Exceptions;

use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class FormFailedValidation extends \RuntimeException
{
    private FlowContext $context;
    private ConstraintViolationListInterface $errors;

    public function __construct(FlowContext $context, ConstraintViolationListInterface $errors)
    {
        $this->context = $context;
        $this->errors = $errors;
        $count = count($this->errors);
        parent::__construct("The form for {$context->getName()} had $count validation error(s)");
    }

    public function getContext(): FlowContext
    {
        return $this->context;
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }
}