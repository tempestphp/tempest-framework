<?php

namespace Tempest\Container\Discovery\ClassLoader;

// TODO: Okay, so right now this doesn't do anything...
// But you can see how it would, right?
final class CacheableClassLoader implements ClassLoader
{
    public function __construct(private ClassLoader $loader)
    {
    }

    public function load(): array
    {
        return $this->loader->load();
    }
}