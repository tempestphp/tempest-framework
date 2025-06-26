<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Exception;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Exceptions\ConvertsToResponse;

final class ConvertsToResponseException extends Exception implements ConvertsToResponse
{
    public function __construct(
        private readonly string $name,
    )
    {
        parent::__construct($name);
    }

    public function toResponse(): Response
    {
        return new Ok($this->name);
    }
}