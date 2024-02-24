<?php

declare(strict_types=1);

namespace Tempest\Testing\Http;

use PHPUnit\Framework\Assert;
use Tempest\Http\Response;
use Tempest\Http\Status;

final readonly class TestResponse implements Response
{
    public function __construct(private Response $response)
    {
    }

    public function getStatus(): Status
    {
        return $this->response->getStatus();
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function getBody(): string
    {
        return $this->response->getBody();
    }

    public function body(string $body): self
    {
        $this->response->body($body);

        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->header($key, $value);

        return $this;
    }

    public function ok(): self
    {
        $this->response->ok();

        return $this;
    }

    public function notFound(): self
    {
        $this->response->notFound();

        return $this;
    }

    public function redirect(string $to): self
    {
        $this->response->redirect($to);

        return $this;
    }

    public function status(Status $status): self
    {
        $this->response->status($status);

        return $this;
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
            )
        );

        return $this;
    }
}
