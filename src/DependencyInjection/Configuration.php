<?php

namespace Mashbo\FormFlowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyNullReference
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('form_flow');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('flow_defaults')
                    ->children()
                        ->scalarNode('handler')->end()
                        ->booleanNode('flush_entity_manager')->defaultFalse()->end()
                        ->scalarNode('template')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('flows')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('default_data')
                                ->children()
                                    ->scalarNode('class')->defaultNull()->end()
                                    ->variableNode('arguments')->defaultValue([])->end()
                                ->end()
                            ->end()
                            ->variableNode('append_data')->defaultValue([])->end()
                            ->variableNode('prepend_data')->defaultValue([])->end()
                            ->variableNode('metadata')->defaultValue([])->end()
                            ->scalarNode('template')->defaultNull()->end()
                            ->arrayNode('http_redirect')
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return $v === false; })
                                    ->then(function($v) { return ['enabled' => false]; })
                                ->end()
                                ->children()
                                    ->booleanNode('enabled')->defaultValue(true)->end()
                                    ->scalarNode('route')->defaultNull()->end()
                                    ->variableNode('parameters')->defaultValue([])->end()
                                ->end()
                            ->end()
                            ->arrayNode('ajax_redirect')
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return $v === false; })
                                    ->then(function($v) { return ['enabled' => false]; })
                                ->end()
                                ->children()
                                    ->booleanNode('enabled')->defaultValue(true)->end()
                                    ->scalarNode('route')->defaultNull()->end()
                                    ->variableNode('parameters')->defaultValue([])->end()
                                ->end()
                            ->end()
                            ->arrayNode('workflow_transition')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function(string $v): array { $parts = explode('.', $v); return ['workflow' => $parts[0], 'transition' => $parts[1]]; })
                                ->end()
                                ->children()
                                    ->scalarNode('workflow')->end()
                                    ->scalarNode('transition')->end()
                                ->end()
                            ->end()
                            ->scalarNode('form')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}