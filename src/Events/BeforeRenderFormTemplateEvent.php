<?php

namespace Mashbo\FormFlowBundle\Events;

use Mashbo\FormFlowBundle\FlowContext;

class BeforeRenderFormTemplateEvent
{
    private string $template;
    private array $parameters;
    private FlowContext $context;

    public function __construct(FlowContext $context, string $template, array $parameters)
    {
        $this->template = $template;
        $this->parameters = $parameters;
        $this->context = $context;
    }

    public function getContext(): FlowContext
    {
        return $this->context;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}