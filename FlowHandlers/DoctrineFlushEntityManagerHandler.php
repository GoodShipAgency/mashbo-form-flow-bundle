<?php

namespace Mashbo\FormFlowBundle\FlowHandlers;

use Doctrine\ORM\EntityManagerInterface;
use Mashbo\FormFlowBundle\Events\FlowSucceeded;
use Mashbo\FormFlowBundle\FlowContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineFlushEntityManagerHandler implements FlowHandler
{
    private FlowHandler $handler;
    private EntityManagerInterface $manager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(FlowHandler $handler, EntityManagerInterface $manager, EventDispatcherInterface $dispatcher)
    {
        $this->handler = $handler;
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke($command, FlowContext $context): void
    {
        $this->handler->__invoke($command, $context);
        $this->dispatcher->dispatch(new FlowSucceeded($context, false));

        $this->manager->flush();
    }
}