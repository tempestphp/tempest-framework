<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\Http;

use Closure;
use Generator;
use JsonException;
use PHPUnit\Framework\Assert;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Validation\Rule;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

use function Tempest\get;
use function Tempest\Support\arr;

final class TestResponseHelper
{
    public function __construct(
        private(set) Response $response,
    ) {}

    public Status $status {
        get => $this->response->status;
    }

    /** @var \Tempest\Http\Header[] */
    public array $headers {
        get => $this->response->headers;
    }

    public View|string|array|Generator|null $body {
        get => $this->response->body;
    }

    public function assertHasHeader(string $name): self
    {
        Assert::assertArrayHasKey(
            $name,
            $this->response->headers,
            sprintf('Failed to assert that response contains header [%s].', $name),
        );

        return $this;
    }

    public function assertHeaderContains(string $name, mixed $value): self
    {
        $this->assertHasHeader($name);

        $header = $this->response->getHeader($name);

        $headerString = var_export($header, true); // @mago-expect best-practices/no-debug-symbols

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
            $this->status->isRedirect(),
            sprintf('Failed asserting that status [%s] is a redirect.', $this->status->value),
        );

        return $to === null
            ? $this->assertHasHeader('Location')
            : $this->assertHeaderContains('Location', $to);
    }

    public function assertOk(): self
    {
        return $this->assertStatus(Status::OK);
    }

    public function assertForbidden(): self
    {
        return $this->assertStatus(Status::FORBIDDEN);
    }

    public function assertNotFound(): self
    {
        return $this->assertStatus(Status::NOT_FOUND);
    }

    public function assertStatus(Status $expected): self
    {
        Assert::assertSame(
            expected: $expected,
            actual: $this->status,
            message: sprintf(
                'Failed asserting status [%s] matched expected status of [%s].',
                $expected->value,
                $this->status->value,
            ),
        );

        return $this;
    }

    public function assertHasCookie(string $key, ?Closure $test = null): self
    {
        $cookies = get(CookieManager::class);

        $cookie = $cookies->get($key);

        Assert::assertNotNull($cookie);

        if ($test !== null) {
            $test($cookie);
        }

        return $this;
    }

    public function assertHasSession(string $key, ?Closure $test = null): self
    {
        /** @var Session $session */
        $session = get(Session::class);

        $data = $session->get($key);

        Assert::assertNotNull(
            $data,
            sprintf(
                'No session value was set for %s, available session keys: %s',
                $key,
                implode(', ', array_keys($session->data)),
            ),
        );

        if ($test !== null) {
            $test($session, $data);
        }

        return $this;
    }

    public function assertHasValidationError(string $key, ?Closure $test = null): self
    {
        /** @var Session $session */
        $session = get(Session::class);

        $validationErrors = $session->get(Session::VALIDATION_ERRORS) ?? [];

        Assert::assertArrayHasKey(
            $key,
            $validationErrors,
            sprintf(
                'No validation error was set for %s, available validation errors: %s',
                $key,
                implode(', ', array_keys($validationErrors)),
            ),
        );

        if ($test !== null) {
            $test($validationErrors);
        }

        return $this;
    }

    public function assertHasNoValidationsErrors(): self
    {
        /** @var Session $session */
        $session = get(Session::class);

        $validationErrors = $session->get(Session::VALIDATION_ERRORS) ?? [];

        Assert::assertEmpty(
            $validationErrors,
            sprintf(
                'There should be no validation errors, but there were: %s',
                arr($validationErrors)
                    ->map(function (array $failingRules, $key) {
                        $failingRules = arr($failingRules)->map(fn (Rule $rule) => $rule->message())->implode(', ');

                        return $key . ': ' . $failingRules;
                    })
                    ->implode(', '),
            ),
        );

        return $this;
    }

    public function assertSee(string $search): self
    {
        $body = $this->body;

        if ($body instanceof View) {
            $body = get(ViewRenderer::class)->render($body);
        }

        Assert::assertStringContainsString($search, $body);

        return $this;
    }

    public function assertNotSee(string $search): self
    {
        $body = $this->body;

        if ($body instanceof View) {
            $body = get(ViewRenderer::class)->render($body);
        }

        Assert::assertStringNotContainsString($search, $body);

        return $this;
    }

    public function assertJson(array $expected): self
    {
        Assert::assertNotNull($this->response->body);
        Assert::assertEquals($expected, $this->response->body);

        return $this;
    }

    public function assertJsonByKeys(array $expected, array $keys): self
    {
        $filteredBody = array_reduce(
            [$expected],
            function ($carry) use ($keys) {
                foreach ($keys as $key) {
                    if (isset($this->response->body[$key])) {
                        $carry[$key] = $this->response->body[$key];
                    }

                    if (str_contains($key, '.') && substr_count($key, '.') === 1) {
                        [$relation, $relatedKey] = explode('.', $key);
                        $carry[$relation][$relatedKey] = $this->body[$relation][$relatedKey];
                    }
                }

                return $carry;
            },
            [],
        );

        Assert::assertNotEmpty($filteredBody);
        Assert::assertEquals($expected, $filteredBody);

        return $this;
    }

    public function assertHasJsonValidationError(string $key, ?Closure $test = null): self
    {
        Assert::assertInstanceOf(Invalid::class, $this->response);

        $validationErrors = array_map(function ($failingRules) use ($key) {
            try {
                $errors = json_decode($failingRules, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $errors = [];
            }

            return arr($errors)->filter(fn (array $_error, $errorKey) => $errorKey === $key)->flatten()->first();
        }, $this->response->getHeader('x-validation')->values);

        Assert::assertNotEmpty($validationErrors, message: 'no validation errors found');

        if ($test !== null) {
            $test($validationErrors);
        }

        return $this;
    }

    public function assertHasNoJsonValidationErrors(): self
    {
        Assert::assertNotInstanceOf(Invalid::class, $this->response);

        return $this;
    }

    public function dd(): void
    {
        /**
         * @noinspection ForgottenDebugOutputInspection
         * @phpstan-ignore disallowed.function
         */
        dd($this->response); // @mago-expect best-practices/no-debug-symbols
    }
}
