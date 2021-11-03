<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\BeforeRenderFormTemplateEvent;
use Mashbo\FormFlowBundle\Events\FindResponseEvent;
use Mashbo\FormFlowBundle\FormEmbedder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Twig\Environment;

class HtmxEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private FormEmbedder $formEmbedder,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onBeforeRenderFormTemplateEvent(BeforeRenderFormTemplateEvent $event): void
    {
        $request = $event->getContext()->getRequest();
        if ($request->headers->get('HX-Request') === 'true') {
            $event->setParameters(
                array_merge($event->getParameters(), ['ajax' => true, 'flow' => $event->getContext()->getFlow()])
            );
        }
    }

    public function onFindResponseEvent(FindResponseEvent $event): void
    {
        $context = $event->getContext();

        if (!$context->submitted || !$context->successful) {
            return;
        }

        if ($event->getResponse() !== null) {
            return;
        }

        if ($event->getRequest()->headers->get('HX-Request') !== 'true') {
            return;
        }

        $event->setResponse(
            new Response(
                $this->twig->render('@MashboTemplate/components/overlays/slideout-form.html.twig',
                    [
                        'ajax' => true,
                        'flow' => $event->getFlow(),
                        'form' => $this->formEmbedder->embedForm($event->getContext()->getName(), $event->getContext()->subject)->createView(),
                        'success' => true,
                    ]
                ),
                Response::HTTP_OK,
                (function () use ($event): array {
                    $headers = [];

                    $redirectConfig = $event->getFlow()->getAjaxRedirectConfig();
                    if ($redirectConfig['enabled']) {
                        $url = null;

                        /**
                         * @psalm-suppress MixedAssignment
                         */
                        $route = $redirectConfig['route'] ?? null;
                        /**
                         * @psalm-suppress MixedAssignment
                         */
                        $parameterExpressions = $redirectConfig['parameters'] ?? [];

                        if ($route !== null) {
                            $expr = new ExpressionLanguage();
                            $parameters = [];
                            /**
                             * @psalm-suppress MixedAssignment
                             */
                            foreach ($parameterExpressions as $key => $value) {
                                /**
                                 * @psalm-suppress MixedArrayOffset
                                 * @psalm-suppress MixedArgument
                                 */
                                $parameters[$key] = $expr->evaluate($value, ['context' => $event->getContext(), 'request' => $event->getRequest()]);
                            }

                            $url = $this->urlGenerator->generate((string) $route, $parameters);
                        }

                        $headers['HX-Redirect'] = $url ?? $event->getRequest()->getRequestUri();
                    }

                    return $headers;
                })()
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeRenderFormTemplateEvent::class => 'onBeforeRenderFormTemplateEvent',
            FindResponseEvent::class => 'onFindResponseEvent',
        ];
    }
}