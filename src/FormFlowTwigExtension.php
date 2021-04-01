<?php

namespace Mashbo\FormFlowBundle;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;

class FormFlowTwigExtension extends AbstractExtension
{
    private FlowRegistry $registry;

    public function __construct(FlowRegistry $registry)
    {
        $this->registry = $registry;
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lookup_flow', [$this, 'lookupFlow'], []),
            new TwigFunction('embed_form', [$this, 'embedForm'], []),
        ];
    }

    public function lookupFlow(string $flowName): FlowInterface
    {
        return $this->registry->findFlow($flowName);
    }

    public function embedForm(string $flowName, ?object $subject): FormView
    {
        return $this->registry->findFlow($flowName)->embedForm($subject)->createView();
    }
}
