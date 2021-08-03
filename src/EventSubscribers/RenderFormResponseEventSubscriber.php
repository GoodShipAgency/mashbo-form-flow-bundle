<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\BeforeRenderFormTemplateEvent;
use Mashbo\FormFlowBundle\Events\FindResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class RenderFormResponseEventSubscriber implements EventSubscriberInterface
{
    private Environment $twig;
    private string $template;
    private string $flowName;
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, Environment $twig, string $flowName, string $template)
    {
        $this->twig = $twig;
        $this->template = $template;
        $this->flowName = $flowName;
        $this->dispatcher = $dispatcher;
    }

    public function onFindResponseEvent(FindResponseEvent $event): void
    {
        if ($event->getContext()->getName() !== $this->flowName) {
            return;
        }

        $context = $event->getContext();

        if (!$context->submitted || !$context->successful) {

            $beforeRenderFormTemplateEvent = new BeforeRenderFormTemplateEvent(
                $context,
                $this->template,
                [
                    'form' => $context->form->createView(),
                    'context' => $context,
                    'flow' => $context->getFlow(),
                ]
            );
            $this->dispatcher->dispatch($beforeRenderFormTemplateEvent);

            $event->setResponse(
                new Response(
                    $this->twig->render(
                        $beforeRenderFormTemplateEvent->getTemplate(),
                        $beforeRenderFormTemplateEvent->getParameters(),
                    )
                )
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FindResponseEvent::class => 'onFindResponseEvent'
        ];
    }
}