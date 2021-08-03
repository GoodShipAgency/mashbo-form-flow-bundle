<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\FindResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectOnSuccessEventSubscriber implements EventSubscriberInterface
{
    private Session $session;

    public function __construct(
        private string $flowName,
        private UrlGeneratorInterface $urlGenerator,
        private ?string $routeName,
        private ?array $routeParams,
        private RequestStack $requestStack
    ) {
        $this->session = $this->requestStack->getSession();
    }

    public function onFindResponseEvent(FindResponseEvent $event): void
    {
        $context = $event->getContext();

        if ($context->getName() !== $this->flowName) {
            return;
        }

        if (!$context->submitted || !$context->successful) {
            return;
        }

        if ($event->getResponse() !== null) {
            return;
        }

        if ($this->routeName !== null) {
            $expr = new ExpressionLanguage();
            $params = [];
            foreach ($this->routeParams as $key => $value) {
                $params[$key] = $expr->evaluate($value, ['context' => $context, 'request' => $event->getRequest()]);
            }

            $event->setResponse(
                new RedirectResponse(
                    $this->urlGenerator->generate($this->routeName, $params)
                )
            );
            return;
        }

        $this->session->getFlashBag()->add('success', 'Submission successful');

        $event->setResponse(
            new RedirectResponse($event->getRequest()->getRequestUri())
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FindResponseEvent::class => 'onFindResponseEvent'
        ];
    }
}