<?php

namespace Tempest\MessageBus\Routing\Matching;

use Tempest\MessageBus\Envelope;
use Tempest\MessageBus\Route;

final class RouteMatcher
{
    public function __construct
    (
        /**
         * @var Route[] $routes
         */
        private array $routes){}

    public function match(Envelope $envelope)
    {
        foreach ($this->routes as $route){
            $this->matchesRoute($route, $envelope);
        }
    }

    private function matchesRoute(Route $route, Envelope $envelope): bool
    {
        if (! $this->matchesPattern($route->messageName, $envelope->messageName)) {
            return false;
        }

        return array_all($route->filters, fn($filter) => $filter($envelope));
    }

    private function matchesPattern(string $pattern, string $subject): bool
    {
        // Escape dots
        $pattern = str_replace('.', '\.', $pattern);

        // Replace wildcards
        $pattern = str_replace('*', '[^.]+', $pattern); // * = one word
        $pattern = str_replace('#', '.*', $pattern);    // # = zero or more words

        // Add start/end anchors
        $regex = '/^' . $pattern . '$/';

        return preg_match($regex, $subject) === 1;
    }
}