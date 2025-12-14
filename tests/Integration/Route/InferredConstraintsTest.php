<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Status;
use Tempest\Router\GenericRouter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class InferredConstraintsTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function int_parameter_accepts_numeric_values(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/inferred/int/123'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('int: 123', $response->body);
    }

    #[Test]
    public function int_parameter_rejects_non_numeric_values(): void
    {
        $this->http
            ->get('/inferred/int/abc')
            ->assertNotFound();
    }

    #[Test]
    public function string_parameter_accepts_any_value(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response1 = $router->dispatch($this->http->makePsrRequest('/inferred/string/test'));
        $this->assertEquals(Status::OK, $response1->status);
        $this->assertEquals('string: test', $response1->body);

        $response2 = $router->dispatch($this->http->makePsrRequest('/inferred/string/123'));
        $this->assertEquals(Status::OK, $response2->status);
        $this->assertEquals('string: 123', $response2->body);
    }

    #[Test]
    public function float_parameter_accepts_decimal_values(): void
    {
        $router = $this->container->get(GenericRouter::class);
        $response = $router->dispatch($this->http->makePsrRequest('/inferred/float/12.34'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('float: 12.34', $response->body);
    }

    #[Test]
    public function float_parameter_accepts_integer_values(): void
    {
        $router = $this->container->get(GenericRouter::class);
        $response = $router->dispatch($this->http->makePsrRequest('/inferred/float/42'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('float: 42', $response->body);
    }

    #[Test]
    public function optional_int_parameter_accepts_numeric_values(): void
    {
        $router = $this->container->get(GenericRouter::class);
        $response = $router->dispatch($this->http->makePsrRequest('/inferred/optional-int/456'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('int: 456', $response->body);
    }

    #[Test]
    public function optional_int_parameter_rejects_non_numeric_values(): void
    {
        $this->http
            ->get('/inferred/optional-int/xyz')
            ->assertNotFound();
    }

    #[Test]
    public function optional_int_parameter_accepts_no_value(): void
    {
        $router = $this->container->get(GenericRouter::class);
        $response = $router->dispatch($this->http->makePsrRequest('/inferred/optional-int'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('no id', $response->body);
    }

    #[Test]
    public function explicit_constraint_is_preserved(): void
    {
        $this->http
            ->get('/inferred/explicit/123')
            ->assertOk()
            ->assertSee('explicit: 123');

        $this->http
            ->get('/inferred/explicit/1234')
            ->assertNotFound();

        $this->http
            ->get('/inferred/explicit/12')
            ->assertNotFound();
    }

    #[Test]
    public function mixed_parameter_types(): void
    {
        $router = $this->container->get(GenericRouter::class);
        $response = $router->dispatch($this->http->makePsrRequest('/inferred/mixed/42/john'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('id: 42, name: john', $response->body);
    }

    #[Test]
    public function mixed_parameter_types_reject_invalid_int(): void
    {
        $this->http
            ->get('/inferred/mixed/abc/john')
            ->assertNotFound();
    }
}
