<?php

namespace Mashbo\FormFlowBundle;

use Mashbo\FormFlowBundle\FlowHandlers\FlowHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

interface FlowInterface
{
    public function getMetadata(): array;
    public function getForm(): FormInterface;
    public function getTransition(): ?string;
    public function getWorkflow(): ?Workflow;
    public function getHandler(): FlowHandler;
    public function embedForm(?object $subject = null): FormInterface;
    public function getName(): string;
}
