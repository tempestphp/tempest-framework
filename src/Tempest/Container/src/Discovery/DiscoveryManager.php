<?php

namespace Tempest\Container\Discovery;

use ReflectionClass;
use Tempest\Container\Discovery\Agent\DiscoveryAgent;
use Tempest\Container\Discovery\ClassFactory\ClassFactory;
use Tempest\Container\Discovery\ClassFactory\ReflectionClassFactory;
use Tempest\Container\Discovery\ClassLoader\ClassLoader;

final class DiscoveryManager
{
    /** @var array<DiscoveryAgent> */
    private array $agents = [];

    /** @var array<ClassLoader> */
    private array $loaders = [];

    /** @var array<ReflectionClass> */
    private array $classes = [];

    private bool $initialized = false;

    public function __construct(private readonly ClassFactory $classFactory = new ReflectionClassFactory())
    {}

    public function addAgent(DiscoveryAgent $agent): self
    {
        $this->agents[] = $agent;

        return $this;
    }

    public function addClassLoader(ClassLoader $classLoader): self
    {
        $this->loaders[] = $classLoader;

        return $this;
    }

    public function initialize(): self
    {
        if ($this->initialized) {
            return $this;
        }

        // Discover and register agents.
        // This is technically a cacheable step.
        $this->classes = [];

        foreach ($this->loaders as $loader) {
            foreach ($loader->load() as $class) {
                if ($class->isInstantiable() && $class->implementsInterface(DiscoveryAgent::class)) {
                    $this->agents[] = $this->classFactory->create($class->getName());
                }

                $this->classes[] = $class;
            }
        }

        $this->initialized = true;

        return $this;
    }

    public function run(): void
    {
        $this->initialize();

        foreach ($this->agents as $agent) {
            foreach ($this->classes as $class) {
                $agent->inspect($class);
            }
        }
    }
}