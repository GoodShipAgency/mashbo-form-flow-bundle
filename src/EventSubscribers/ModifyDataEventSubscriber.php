<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\BeforeFormCreationEvent;
use Mashbo\FormFlowBundle\Events\BeforeFormEvent;
use Mashbo\FormFlowBundle\Events\BeforeHandlerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ModifyDataEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ?array $defaultData, private array $appendData, private array $prependData, private string $flowName
    ) {}

    public function onBeforeFormCreationEvent(BeforeFormCreationEvent $event): void
    {
        if ($event->getFlow()->getName() !== $this->flowName) {
            return;
        }

        if ($this->defaultData === null) {
            return;
        }

        $data = new $this->defaultData['class']();
        $accessor = PropertyAccess::createPropertyAccessor();
        $expression = new ExpressionLanguage();

        foreach ($this->defaultData['arguments'] as $propertyPath => $expressionString) {
            $accessor->setValue(
                $data,
                $propertyPath,
                $expression->evaluate(
                    $expressionString,
                    [
                        'subject' => $event->getSubject(),
                    ]
                )
            );
        }

        $event->setData($data);
    }

    public function onBeforeHandler(BeforeHandlerEvent $event): void
    {
        if ($event->getContext()->getName() !== $this->flowName) {
            return;
        }
        $accessor = PropertyAccess::createPropertyAccessor();
        $expression = new ExpressionLanguage();

        $formData = $event->getContext()->form->getData();
        foreach ($this->appendData as $propertyPath => $expressionString) {
            $accessor->setValue(
                $formData,
                $propertyPath,
                $expression->evaluate(
                    $expressionString,
                    [
                        'subject' => $event->getContext()->subject,
                        'context' => $event->getContext()
                    ]
                )
            );
        }
    }

    public function onBeforeFormEvent(BeforeFormEvent $event): void
    {
        if ($event->getContext()->getName() !== $this->flowName) {
            return;
        }

        $expression = new ExpressionLanguage();

        foreach ($this->prependData as $propertyPath => $expressionString) {

            $formPathParts = explode('.', $propertyPath);
            $form = $event->getContext()->form;
            foreach ($formPathParts as $part) {
                $form = $form->get($part);
            }

            $form->setData(
                $expression->evaluate(
                    $expressionString,
                    [
                        'subject' => $event->getContext()->subject,
                        'context' => $event->getContext()
                    ]
                )
            );
        }
    }


    public static function getSubscribedEvents()
    {
        return [
            BeforeFormCreationEvent::class => 'onBeforeFormCreationEvent',
            BeforeFormEvent::class => 'onBeforeFormEvent',
            BeforeHandlerEvent::class => 'onBeforeHandler',
        ];
    }
}