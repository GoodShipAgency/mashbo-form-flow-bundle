<?php

namespace Mashbo\FormFlowBundle;

use Mashbo\FormFlowBundle\FlowHandlers\FlowHandler;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class Flow implements FlowInterface
{
    private string $formType;
    private Registry $registry;
    private ?string $workflow;
    private ?string $transition;
    private FormFactoryInterface $formFactory;
    /**
     * @var FlowHandler
     */
    private FlowHandler $flowHandler;
    private array $metadata;
    private FormEmbedder $formEmbedder;
    private string $name;
    private array $httpRedirectConfig;
    private array $ajaxRedirectConfig;

    public function __construct(
        FormFactoryInterface $formFactory,
        FormEmbedder $formEmbedder,
        FlowHandler $flowHandler,
        string $formType,
        Registry $registry,
        string $name,
        array $metadata,
        ?string $workflow,
        ?string $transition,
        array $httpRedirectConfig,
        array $ajaxRedirectConfig,
    ) {
        $this->formType = $formType;
        $this->registry = $registry;
        $this->workflow = $workflow;
        $this->transition = $transition;
        $this->formFactory = $formFactory;
        $this->flowHandler = $flowHandler;
        $this->metadata = $metadata;
        $this->formEmbedder = $formEmbedder;
        $this->name = $name;
        $this->httpRedirectConfig = $httpRedirectConfig;
        $this->ajaxRedirectConfig = $ajaxRedirectConfig;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getForm(?object $data = null): FormInterface
    {
        return $this->formFactory->create($this->formType, $data, []);
    }

    public function getSubject(): ?object
    {
        return null;
    }

    public function getTransition(): ?string
    {
        if ($this->workflow === null || $this->transition === null) {
            return null;
        }

        return $this->transition;
    }

    public function getWorkflow(): ?Workflow
    {
        if ($this->workflow === null || $this->transition === null) {
            return null;
        }

        return $this->registry->get($this->getSubject(), $this->workflow);
    }

    public function getHandler(): FlowHandler
    {
        return $this->flowHandler;
    }

    public function embedForm(?object $subject = null): FormInterface
    {
        return $this->formEmbedder->embedForm($this->name, $subject);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHttpRedirectConfig(): array
    {
        return $this->httpRedirectConfig;
    }

    public function getAjaxRedirectConfig(): array
    {
        return $this->ajaxRedirectConfig;
    }
}