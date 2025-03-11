<?php

namespace Tests\Tempest\Integration\Route;

use Tempest\Http\Method;
use Tempest\Router\GenericRequest;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rules\NotNull;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\RequestObjectA;
use function Tempest\map;

final class RequestToObjectMapperTest extends FrameworkIntegrationTestCase
{
    public function test_request(): void
    {
        $request = new GenericRequest(method: Method::POST, uri: '/', body: []);

        try {
            map($request)->to(RequestObjectA::class);
        } catch (ValidationException $validationException) {
            $this->assertInstanceOf(NotNull::class, $validationException->failingRules['b'][0]);
        }
    }
}