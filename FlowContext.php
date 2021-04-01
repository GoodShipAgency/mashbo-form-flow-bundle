<?php

namespace Mashbo\FormFlowBundle;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FlowContext
{
    private string $name;
    private ?Request $request;
    public bool $submitted = false;
    public bool $successful = false;
    public FormInterface $form;
    public ?object $subject = null;
    public ?bool $valid = null;
    public ?\Throwable $exception = null;
    private FlowInterface $flow;

    public function __construct(FlowInterface $flow, string $name, ?Request $request, FormInterface $form)
    {
        $this->name = $name;
        $this->request = $request;
        $this->form = $form;
        $this->flow = $flow;
    }

    public function getFlow(): FlowInterface
    {
        return $this->flow;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
}