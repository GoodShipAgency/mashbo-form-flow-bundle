<?php

namespace Mashbo\FormFlowBundle\Events;

use Mashbo\FormFlowBundle\FlowInterface;

class BeforeFormCreationEvent
{
    private ?object $data = null;

    public function __construct(private FlowInterface $flow, private ?object $subject) {}

    public function getFlow(): FlowInterface
    {
        return $this->flow;
    }

    public function getSubject(): ?object
    {
        return $this->subject;
    }

    public function setData(object $command): void
    {
        $this->data = $command;
    }

    public function getData(): ?object
    {
        return $this->data;
    }
}