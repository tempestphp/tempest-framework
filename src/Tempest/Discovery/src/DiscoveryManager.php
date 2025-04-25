<?php

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Cache\Cache;
use Tempest\Discovery\ClassLocator\ClassLocator;
use Tempest\Discovery\Test\NewDiscoveryAgent;

final class DiscoveryManager
{
    private string $key = 'discovery';

    /** @var array<class-string, NewDiscoveryAgent> */
    private array $agents = [];

    /**
     * @var array<class-string, array<class-string>>
     */
    private array $matches = [];

    public function __construct(
        private ClassLocator $classLocator,
        private ?Cache $cache = null
    ) {}

    public function addAgent(NewDiscoveryAgent $agent): void
    {
        $this->agents[$agent::class] = $agent;
    }

    public function run(bool $enableCache = true): void
    {
        if (! $enableCache) {
            $this->cache->remove($this->key);
        }

        $this
            ->matchAgentsWithClasses()
            ->runDiscoveryAgents();
    }

    private function matchAgentsWithClasses(): self
    {
        if ($cached = $this->cache?->get($this->key) !== null) {
            $this->matches = $cached;

            return $this;
        }

        // Iterator 1: go through all our discovered classes
        foreach ($this->classLocator->getClasses() as $class) {
            $reflectionClass = new ReflectionClass($class);

            // Iterator 2: for each discovered class, attempt to
            // match it with one or more discovery agents.
            foreach ($this->agents as $agent) {
                if (! $agent->rules()->match($class, $reflectionClass)) {
                    continue;
                }

                if (! isset($this->matches[$class])) {
                    $this->matches[$class] = [];
                }

                $this->matches[$class][] = $agent::class;
            }
        }

        $this->cache?->put($this->key, $this->matches);
    }

    private function runDiscoveryAgents(): void
    {
        foreach ($this->matches as $class => $agentClasses) {
            $reflectionClass = new ReflectionClass($class);

            foreach ($agentClasses as $agentClass) {
                $this->agents[$agentClass]->run($class, $reflectionClass);
            }
        }
    }
}