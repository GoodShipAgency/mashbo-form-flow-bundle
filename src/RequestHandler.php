<?php

namespace Mashbo\FormFlowBundle;

use Mashbo\FormFlowBundle\Events\BeforeFormCreationEvent;
use Mashbo\FormFlowBundle\Events\BeforeFormEvent;
use Mashbo\FormFlowBundle\Events\BeforeHandlerEvent;
use Mashbo\FormFlowBundle\Events\FindResponseEvent;
use Mashbo\FormFlowBundle\Events\FlowFailed;
use Mashbo\FormFlowBundle\Events\FlowSucceeded;
use Mashbo\FormFlowBundle\Events\FlowWasStarted;
use Mashbo\FormFlowBundle\Events\SubjectWasDetermined;
use Mashbo\FormFlowBundle\Exceptions\FormFailedValidation;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RequestHandler
{
    private FlowRegistry $registry;
    private EventDispatcherInterface $dispatcher;

    public function __construct(FlowRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
    }

    public function handle(string $name, Request $request, ?object $subject): Response
    {
        $flow = $this->registry->findFlow($name);

        $beforeFormCreationEvent = new BeforeFormCreationEvent($flow, $subject);
        $this->dispatcher->dispatch($beforeFormCreationEvent);

        $form = $flow->getForm($beforeFormCreationEvent->getData());
        $context = new FlowContext($flow, $name, $request, $form);

        $this->dispatcher->dispatch(new FlowWasStarted($context));

        if (is_object($subject)) {
            $this->dispatcher->dispatch(new SubjectWasDetermined($subject));
        }

        $this->dispatcher->dispatch(new BeforeFormEvent($context));

        $form->handleRequest($request);

        $handler = $flow->getHandler();
        if ($form->isSubmitted()) {
            $context->submitted = true;

            try {
                $this->dispatcher->dispatch(new BeforeHandlerEvent($context));
                $handler($form->getData(), $context);
                $context->successful = true;
                $this->dispatcher->dispatch(new FlowSucceeded($context, true));
            } catch (FormFailedValidation $exception) {

                $this->dispatcher->dispatch(new FlowFailed($context, $exception));
                $context->successful = false;
                $context->exception = $exception;
            } catch (HandlerFailedException $handlerFailedException) {
                $previous = $handlerFailedException->getPrevious();

                if ($previous instanceof \DomainException) {
                    $form->addError(new FormError($previous->getMessage()));
                } else {
                    $form->addError(new FormError('There was an error processing this request'));

                    throw $handlerFailedException;
                }
            }
        }

        $event = new FindResponseEvent($flow, $context);
        $this->dispatcher->dispatch($event);

        $response = $event->getResponse();
        if ($response === null) {
            throw new \LogicException("No response to return");
        }

        return $response;
    }
}