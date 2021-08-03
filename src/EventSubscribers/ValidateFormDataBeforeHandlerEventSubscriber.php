<?php

namespace Mashbo\FormFlowBundle\EventSubscribers;

use Mashbo\FormFlowBundle\Events\BeforeHandlerEvent;
use Mashbo\FormFlowBundle\Exceptions\FormFailedValidation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidateFormDataBeforeHandlerEventSubscriber implements EventSubscriberInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function onBeforeHandler(BeforeHandlerEvent $event): void
    {
        $context = $event->getContext();
        $formData = $context->form->getData();

        $errors = $this->validator->validate($formData, null, $context->form->getConfig()->getOption('validation_groups'));
        $context->valid = (count($errors) === 0);

        if ($context->valid) {
            return;
        }

        throw new FormFailedValidation($context, $errors);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeHandlerEvent::class => 'onBeforeHandler'
        ];
    }
}