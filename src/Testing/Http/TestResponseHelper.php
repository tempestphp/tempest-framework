<?php

declare(strict_types=1);

namespace Tempest\Testing\Http;

use Closure;
use PHPUnit\Framework\Assert;
use Tempest\Http\Response;
use Tempest\Http\Status;

final readonly class TestResponseHelper
{
    public function __construct(private Response $response)
    {
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getStatus(): Status
    {
        return $this->getResponse()->getStatus();
    }

    public function getHeaders(): array
    {
        return $this->getResponse()->getHeaders();
    }

    public function getBody(): string|array
    {
        return $this->getResponse()->getBody();
    }

    public function assertHasHeader(string $name): self
    {
        Assert::assertArrayHasKey(
            $name,
            $this->response->getHeaders(),
            sprintf('Failed to assert that response contains header [%s].', $name),
        );

        return $this;
    }

    public function assertHeaderContains(string $name, mixed $value): self
    {
        $this->assertHasHeader($name);

        $header = $this->response->getHeader($name);

        $headerString = var_export($header, true);

        Assert::assertContains(
            $value,
            $header->values,
            sprintf('Failed to assert that response header [%s] value contains %s. These header values were found: %s', $name, $value, $headerString),
        );

        return $this;
    }

    public function assertRedirect(?string $to = null): self
    {
        Assert::assertTrue(
            $this->response->getStatus()->isRedirect(),
            sprintf('Failed asserting that status [%s] is a redirect.', $this->response->getStatus()->value),
        );

        return $to === null
            ? $this->assertHasHeader('Location')
            : $this->assertHeaderContains('Location', $to);
    }

    public function assertOk(): self
    {
        return $this->assertStatus(Status::OK);
    }

    public function assertNotFound(): self
    {
        return $this->assertStatus(Status::NOT_FOUND);
    }

    public function assertStatus(Status $expected): self
    {
        Assert::assertSame(
            expected: $expected,
            actual: $this->response->getStatus(),
            message: sprintf(
                'Failed asserting status [%s] matched expected status of [%s].',
                $expected->value,
                $this->response->getStatus()->value,
            ),
        );

        return $this;
    }

    public function assertHasCookie(string $key, ?Closure $test = null): self
    {
        $cookie = $this->response->getCookie($key);

        if ($test) {
            $test($cookie);
        }

        Assert::assertNotNull($cookie);

        return $this;
    }
}
