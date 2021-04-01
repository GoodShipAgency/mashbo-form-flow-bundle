<?php

namespace Mashbo\FormFlowBundle;

use Mashbo\FormFlowBundle\Exceptions\NoSuchFlow;

class FlowRegistry
{
    private array $flows = [];

    public function registerFlow(string $name, FlowInterface $flow): void
    {
        $this->flows[$name] =  $flow;
    }
    public function findFlow(string $name): FlowInterface
    {
        if (!array_key_exists($name, $this->flows)) {
            throw new NoSuchFlow($name);
        }
        return $this->flows[$name];
    }
}