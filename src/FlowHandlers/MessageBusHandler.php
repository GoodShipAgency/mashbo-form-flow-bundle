<?php

namespace Mashbo\FormFlowBundle\FlowHandlers;

use Mashbo\FormFlowBundle\Events\FlowSucceeded;
use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusHandler implements FlowHandler
{
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $bus;
    private EventDispatcherInterface $dispatcher;

    public function __construct(MessageBusInterface $bus, EventDispatcherInterface $dispatcher)
    {
        $this->bus = $bus;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke($command, FlowContext $context): void
    {
        $this->bus->dispatch($command);
        $this->dispatcher->dispatch(new FlowSucceeded($context, false));
    }
}