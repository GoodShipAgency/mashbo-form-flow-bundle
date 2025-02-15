<?php

namespace Mashbo\FormFlowBundle\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;
use Mashbo\FormFlowBundle\EventSubscribers\HtmxEventSubscriber;
use Mashbo\FormFlowBundle\EventSubscribers\ModifyDataEventSubscriber;
use Mashbo\FormFlowBundle\EventSubscribers\RedirectOnSuccessEventSubscriber;
use Mashbo\FormFlowBundle\EventSubscribers\RenderFormResponseEventSubscriber;
use Mashbo\FormFlowBundle\EventSubscribers\ValidateFormDataBeforeHandlerEventSubscriber;
use Mashbo\FormFlowBundle\EventSubscribers\WorkflowEventSubscriber;
use Mashbo\FormFlowBundle\Flow;
use Mashbo\FormFlowBundle\FlowHandlers\DoctrineFlushEntityManagerHandler;
use Mashbo\FormFlowBundle\FlowHandlers\MessageBusHandler;
use Mashbo\FormFlowBundle\FlowRegistry;
use Mashbo\FormFlowBundle\FormEmbedder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\EventListener\ValidationListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;

class MashboFormFlowExtension extends Extension
{

    /**
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $registryDefinition = $container->getDefinition(FlowRegistry::class);

        $htmxEventSubscriber = new Definition(HtmxEventSubscriber::class);
        $htmxEventSubscriber->setArgument('$twig', new Reference('twig'));
        $htmxEventSubscriber->setArgument('$formEmbedder', new Reference(FormEmbedder::class));
        $htmxEventSubscriber->setArgument('$urlGenerator', new Reference('router'));

        $htmxEventSubscriber->addTag('kernel.event_subscriber');
        $container->setDefinition("form_flow.htmx_event_subscriber", $htmxEventSubscriber);

        foreach ($config['flows'] as $flowName => $flowConfig) {

            $handlerService = $this->resolveHandlerService(
                $flowName,
                $flowConfig,
                $config['flow_defaults']
            );

            // Not set implies enabled: true, with no parameters, will redirect to same route
            // Set enabled: false to fully disable
            if (!isset($flowConfig['http_redirect'])) {
                $flowConfig['http_redirect'] = ['enabled' => true];
            }

            // Not set implies enabled: false, with no parameters, will redirect to same route
            // Set enabled: false to fully disable
            if (!isset($flowConfig['ajax_redirect'])) {
                $flowConfig['ajax_redirect'] = ['enabled' => false];
            }

            $flowDefinition = new Definition(Flow::class);
            $flowDefinition->setArgument('$formFactory', new Reference('form.factory'));
            $flowDefinition->setArgument('$flowHandler', $handlerService);
            $flowDefinition->setArgument('$formEmbedder', new Reference(FormEmbedder::class));
            $flowDefinition->setArgument('$name', $flowName);
            $flowDefinition->setArgument('$metadata', $flowConfig['metadata']);
            $flowDefinition->setArgument('$formType', $flowConfig['form']);
            $flowDefinition->setArgument('$registry', new Reference(Registry::class));
            $flowDefinition->setArgument('$workflow', $flowConfig['workflow'] ?? null);
            $flowDefinition->setArgument('$transition', $flowConfig['transition'] ?? null);
            $flowDefinition->setArgument('$httpRedirectConfig', $flowConfig['http_redirect']);
            $flowDefinition->setArgument('$ajaxRedirectConfig', $flowConfig['ajax_redirect']);
            $registryDefinition->addMethodCall(
                'registerFlow',
                [
                    $flowName,
                    $flowDefinition
                ]
            );

            if ($flowConfig['http_redirect']['enabled']) {
                $successRedirectSubscriberDefinition = new Definition(RedirectOnSuccessEventSubscriber::class);
                $successRedirectSubscriberDefinition->setArgument('$flowName', $flowName);
                $successRedirectSubscriberDefinition->setArgument('$urlGenerator', new Reference('router'));
                $successRedirectSubscriberDefinition->setArgument('$routeName', $flowConfig['http_redirect']['route'] ?? null);
                $successRedirectSubscriberDefinition->setArgument('$routeParams', $flowConfig['http_redirect']['parameters'] ?? []);
                $successRedirectSubscriberDefinition->setArgument('$requestStack', new Reference('request_stack'));
                $successRedirectSubscriberDefinition->addTag('kernel.event_subscriber');

                $container->setDefinition("form_flow.flows.$flowName.http_redirect_event_subscriber", $successRedirectSubscriberDefinition);
            }

            $useWorkflowListener = isset($flowConfig['workflow_transition']) && is_array($flowConfig['workflow_transition']) && count($flowConfig['workflow_transition']) == 2;

            if ($useWorkflowListener) {
                $workflowListenerDefinition = new Definition(WorkflowEventSubscriber::class, [
                    $flowName,
                    new Reference(Registry::class),
                    $flowConfig['workflow_transition']['workflow'],
                    $flowConfig['workflow_transition']['transition']
                ]);
                $workflowListenerDefinition->addTag('kernel.event_subscriber');
                $container->setDefinition("form_flow.flows.$flowName.workflow_event_subscriber", $workflowListenerDefinition);
            }

            $renderFormListenerDefinition = new Definition(RenderFormResponseEventSubscriber::class);
            $renderFormListenerDefinition->setArgument('$dispatcher', new Reference(EventDispatcherInterface::class));
            $renderFormListenerDefinition->setArgument('$twig', new Reference('twig'));
            $renderFormListenerDefinition->setArgument('$flowName', $flowName);
            $renderFormListenerDefinition->setArgument('$template', $flowConfig['template'] ?? $config['flow_defaults']['template'] ?? '@MashboFormFlowExtension/form.html.twig');
            $renderFormListenerDefinition->setArgument('$failureResponseCode', $flowConfig['failure_response_code'] ?? $config['flow_defaults']['failure_response_code'] ?? 200);
            $renderFormListenerDefinition->addTag('kernel.event_subscriber');

            $container->setDefinition("form_flow.flows.$flowName.render_form_event_subscriber", $renderFormListenerDefinition);

            if (!empty($flowConfig['default_data']) || !empty($flowConfig['append_data']) || !empty($flowConfig['prepend_data'])) {
                $modifyDataListener = new Definition(ModifyDataEventSubscriber::class);
                $modifyDataListener->setArgument('$defaultData', $flowConfig['default_data'] ?? null);
                $modifyDataListener->setArgument('$appendData', $flowConfig['append_data']);
                $modifyDataListener->setArgument('$prependData', $flowConfig['prepend_data']);
                $modifyDataListener->setArgument('$flowName', $flowName);
                $modifyDataListener->addTag('kernel.event_subscriber');

                $container->setDefinition("form_flow.flows.$flowName.modify_data_event_subscriber", $modifyDataListener);
            }
        }

        $validationListener = new Definition(ValidateFormDataBeforeHandlerEventSubscriber::class);
        $validationListener->setArgument('$validator', new Reference(ValidatorInterface::class));
        $validationListener->addTag('kernel.event_subscriber');
        $container->setDefinition(ValidationListener::class, $validationListener);
    }

    private function resolveHandlerService(string $flowName, array $config, array $defaults): Definition
    {
        $key = $config['handler'] ?? $defaults['handler'] ?? null;
        if ($key === null) {
            throw new \LogicException("No valid handler found for $flowName");
        }

        if ($key === 'message_bus') {
            $definition = new Definition(
                MessageBusHandler::class,
                [
                    new Reference(MessageBusInterface::class),
                    new Reference(EventDispatcherInterface::class)
                ]
            );
        } else {
            $definition = new ChildDefinition($key);
        }

        $useFlushingDecorator = $config['flush_entity_manager'] ?? $defaults['flush_entity_manager'];

        if ($useFlushingDecorator) {
            $definition = new Definition(
                DoctrineFlushEntityManagerHandler::class,
                [
                    $definition,
                    new Reference(EntityManagerInterface::class),
                    new Reference(EventDispatcherInterface::class)
                ]);
        }

        return $definition;
    }
}