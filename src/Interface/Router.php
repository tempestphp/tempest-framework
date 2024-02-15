<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface Router
{
    public function dispatch(Request $request): Response;

    public function toUri(array|string $action, ...$params): string;

    /**
     * @return \Tempest\Http\Route[][]
     */
    public function getRoutes(): array;
}
