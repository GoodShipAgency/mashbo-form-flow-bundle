<?php

namespace Mashbo\FormFlowBundle\Events;

use Mashbo\FormFlowBundle\Flow;
use Mashbo\FormFlowBundle\FlowContext;
use Mashbo\FormFlowBundle\FlowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class FindResponseEvent extends Event
{
    private FlowInterface $flow;
    private FlowContext $context;
    private ?Response $response = null;

    public function __construct(FlowInterface $flow, FlowContext $context)
    {
        $this->flow = $flow;
        $this->context = $context;
    }

    public function getFlow(): FlowInterface
    {
        return $this->flow;
    }

    public function getContext(): FlowContext
    {
        return $this->context;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->context->getRequest();
    }

}