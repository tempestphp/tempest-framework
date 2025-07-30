<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\Http;

use Closure;
use Generator;
use JsonSerializable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Container\Container;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Support\Arr;
use Tempest\Validation\Rule;
use Tempest\Validation\Validator;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

use function Tempest\get;
use function Tempest\Support\arr;

final class TestResponseHelper
{
    public function __construct(
        private(set) Response $response,
        private(set) ?Container $container = null,
    ) {}

    public Status $status {
        get => $this->response->status;
    }

    /** @var \Tempest\Http\Header[] */
    public array $headers {
        get => $this->response->headers;
    }

    public View|string|array|Generator|JsonSerializable|null $body {
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

    public function assertDoesNotHaveHeader(string $name): self
    {
        Assert::assertArrayNotHasKey(
            $name,
            $this->response->headers,
            sprintf('Failed to assert that response does not contain header [%s].', $name),
        );

        return $this;
    }

    /**
     * Asserts that the given header contains the given value.
     */
    public function assertHeaderContains(string $name, mixed $value): self
    {
        $this->assertHasHeader($name);

        $header = $this->response->getHeader($name);
        $headerString = var_export($header, return: true); // @mago-expect best-practices/no-debug-symbols

        Assert::assertContains(
            $value,
            $header->values,
            sprintf('Failed to assert that response header [%s] value contains [%s]. These header values were found: %s', $name, $value, $headerString),
        );

        return $this;
    }

    /**
     * Asserts that the given header contains the given value. The value may contains placeholders such as `%s`, `%d`, etc.
     */
    public function assertHeaderMatches(string $name, string $format): self
    {
        $this->assertHasHeader($name);

        $header = $this->response->getHeader($name);
        $headerString = var_export($header, return: true); // @mago-expect best-practices/no-debug-symbols

        foreach ($header->values as $value) {
            try {
                Assert::assertStringMatchesFormat($format, $value);

                return $this;
            } catch (ExpectationFailedException) { // @mago-expect best-practices/no-empty-catch-clause
            }
        }

        Assert::fail(sprintf('Failed to assert that response header [%s] value contains [%s]. These header values were found: %s', $name, $format, $headerString));
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

    public function assertHasCookie(string $key, null|string|Closure $value = null): self
    {
        /** @var array<string,Cookie> */
        $cookies = Arr\map_with_keys(
            array: $this->response->getHeader('set-cookie')->values,
            map: function (string $cookie) {
                $cookie = Cookie::createFromString($cookie);
                yield $cookie->key => $cookie;
            },
        );

        Assert::assertArrayHasKey(
            key: $key,
            array: $cookies,
            message: sprintf('No cookie was set for [%s], available cookies: %s', $key, implode(', ', array_keys($cookies))),
        );

        $encrypter = $this->container->get(Encrypter::class);
        $cookie = $cookies[$key]->value
            ? $encrypter->decrypt($cookies[$key]->value)
            : '';

        if ($value instanceof Closure) {
            $value($cookie);
        }

        if (is_string($value)) {
            Assert::assertEquals($value, $cookie);
        }

        return $this;
    }

    public function assertHasSession(string $key, ?Closure $callback = null): self
    {
        $this->assertHasContainer();

        $session = $this->container->get(Session::class);
        $data = $session->get($key);

        Assert::assertNotNull(
            actual: $data,
            message: sprintf(
                'No session value was set for [%s], available session keys: %s',
                $key,
                implode(', ', array_keys($session->data)),
            ),
        );

        if ($callback !== null) {
            $callback($session, $data);
        }

        return $this;
    }

    public function assertHasValidationError(string $key, ?Closure $callback = null): self
    {
        $session = $this->container->get(Session::class);
        $validationErrors = $session->get(Session::VALIDATION_ERRORS) ?? [];

        Assert::assertArrayHasKey(
            key: $key,
            array: $validationErrors,
            message: sprintf(
                'No validation error was set for [%s], available validation errors: %s',
                $key,
                implode(', ', array_keys($validationErrors)),
            ),
        );

        if ($callback !== null) {
            $callback($validationErrors);
        }

        return $this;
    }

    public function assertHasNoValidationsErrors(): self
    {
        $session = $this->container->get(Session::class);
        $validationErrors = $session->get(Session::VALIDATION_ERRORS) ?? [];

        Assert::assertEmpty(
            actual: $validationErrors,
            message: arr($validationErrors)
                ->map(fn (array $failingRules, string $key) => $key . ': ' . arr($failingRules)->map(fn (Rule $rule) => $rule::class)->implode(', '))
                ->implode(', ')
                ->prepend('There should be no validation errors, but there were: ')
                ->toString(),
        );

        return $this;
    }

    public function assertSee(string $search): self
    {
        $body = $this->body;

        if ($body instanceof View) {
            $body = $this->container->get(ViewRenderer::class)->render($body);
        }

        Assert::assertStringContainsString($search, $body);

        return $this;
    }

    public function assertNotSee(string $search): self
    {
        $body = $this->body;

        if ($body instanceof View) {
            $body = $this->container->get(ViewRenderer::class)->render($body);
        }

        Assert::assertStringNotContainsString($search, $body);

        return $this;
    }

    /**
     * Asserts view data key exists and optionally assert the value
     *
     * ->assertViewData('name', fn (array $data, mixed $value) => Assert::assertEquals('Brent', $value));
     *
     * @param Closure(array<string, mixed>, mixed): (void|bool)|null $callback
     */
    public function assertViewData(string $key, ?Closure $callback = null): self
    {
        $data = $this->body->data;
        $value = $data[$key];

        Assert::assertArrayHasKey(
            key: $key,
            array: $data,
            message: sprintf(
                'No view data was set for [%s], available view data keys: %s',
                $key,
                implode(', ', array_keys($data)),
            ),
        );

        if ($callback !== null && $callback($data, $value) === false) {
            Assert::fail(sprintf('Failed validating view data for [%s]', $key));
        }

        return $this;
    }

    /**
     * Asserts view data key doesn't exist
     *
     * ->assertViewDataMissing('email');
     */
    public function assertViewDataMissing(string $key): self
    {
        $data = $this->body->data;

        Assert::assertArrayNotHasKey(
            key: $key,
            array: $data,
            message: sprintf('Failed asserting that view data key [%s] was not set', $key),
        );

        return $this;
    }

    /**
     * Asserts all view data
     *
     * ->assertViewDataAll(fn (array $data) => Assert::assertEquals(['name' => 'Brent'], $data));
     * ->assertViewDataAll(fn (array $data) => Assert::assertEquals(['name', 'email'], array_keys($data)));
     *
     * @param Closure(array<string, mixed>): (void|bool) $callback
     */
    public function assertViewDataAll(Closure $callback): self
    {
        $data = $this->body->data;

        if ($callback($data) === false) {
            Assert::fail('Failed validating all view data');
        }

        return $this;
    }

    /**
     * Asserts the view path
     *
     * ->assertView('./view.php');
     * ->assertView(__DIR__ . '/../view.php');
     */
    public function assertView(string $view): self
    {
        if (! ($this->body instanceof View)) {
            Assert::fail(sprintf('Response is not a %s', View::class));
        }

        Assert::assertEquals(
            expected: $view,
            actual: $this->body->path,
        );

        return $this;
    }

    /**
     * Asserts the view model object
     *
     * ->assertViewModel(CustomViewModel::class);
     * ->assertViewModel(CustomViewModel::class, fn (CustomViewModel $viewModel) => Assert::assertEquals('Brent', $viewModel->name));
     *
     * @template T of View
     * @param class-string<T> $expected
     * @param Closure(T): (void|bool)|null $callback
     */
    public function assertViewModel(string $expected, ?Closure $callback = null): self
    {
        Assert::assertInstanceOf(
            expected: $expected,
            actual: $this->body,
        );

        if ($callback !== null && $callback($this->body) === false) {
            Assert::fail('Failed validating view model');
        }

        return $this;
    }

    /**
     * Assert the response body is an exact match to the given array.
     *
     * The keys can also be specified using dot notation.
     *
     * ### Example
     * ```
     * // build the expected array with dot notation
     * $this->http->get(uri([BookController::class, 'index']))
     *     ->assertJson([
     *          'id' => 1,
     *          'title' => 'Timeline Taxi',
     *          'author.name' => 'Brent',
     *      ]);
     *
     * // build the expected array with a normal array
     * $this->http->get(uri([BookController::class, 'index']))
     *     ->assertJson([
     *          'id' => 1,
     *          'title' => 'Timeline Taxi',
     *          'author' => [
     *              'name' => 'Brent',
     *          ],
     *      ]);
     * ```
     *
     * @param array<string, mixed> $expected
     */
    public function assertJson(array $expected): self
    {
        Assert::assertEquals(
            expected: arr($expected)->undot()->toArray(),
            actual: $this->response->body,
        );

        return $this;
    }

    /**
     * Asserts the response contains the given keys.
     *
     * The keys can also be specified using dot notation.
     *
     * ### Example
     * ```
     * $this->http->get(uri([BookController::class, 'index']))
     *     ->assertJsonHasKeys('id', 'title', 'author.name');
     * ```
     */
    public function assertJsonHasKeys(string ...$keys): self
    {
        foreach ($keys as $key) {
            Assert::assertArrayHasKey($key, arr($this->response->body)->dot());
        }

        return $this;
    }

    /**
     * Asserts the response contains the given keys and values.
     *
     * The keys can also be specified using dot notation.
     *
     * ### Example
     * ```
     * $this->http->get(uri([BookController::class, 'index']))
     *     ->assertJsonContains([
     *          'id' => 1,
     *          'title' => 'Timeline Taxi',
     *      ])
     *     ->assertJsonContains(['author' => ['name' => 'Brent']])
     *     ->assertJsonContains(['author.name' => 'Brent']);
     * ```
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey, TValue> $expected
     */
    public function assertJsonContains(array $expected): self
    {
        foreach (arr($expected)->undot() as $key => $value) {
            Assert::assertEquals($this->response->body[$key], $value);
        }

        return $this;
    }

    /**
     * Asserts the response contains the given JSON validation errors.
     *
     * The keys can also be specified using dot notation.
     *
     * ### Example
     * ```
     * $this->http->get(uri([BookController::class, 'index']))
     *     ->assertJsonValidationErrors([
     *          'title' => 'The title field is required.',
     *      ]);
     * ```
     *
     * @param array<string, string|string[]> $expectedErrors
     */
    public function assertHasJsonValidationErrors(array $expectedErrors): self
    {
        $this->assertHasContainer();

        Assert::assertInstanceOf(Invalid::class, $this->response);
        Assert::assertContains($this->response->status, [Status::BAD_REQUEST, Status::FOUND]);
        Assert::assertNotNull($this->response->getHeader('x-validation'));

        $session = $this->container->get(Session::class);
        $validator = $this->container->get(Validator::class);
        $validationRules = arr($session->get(Session::VALIDATION_ERRORS))->dot();

        arr($expectedErrors)
            ->dot()
            ->each(fn ($expectedErrorValue, $expectedErrorKey) => Assert::assertEquals(
                expected: $expectedErrorValue,
                actual: $validator->getErrorMessage($validationRules->get($expectedErrorKey)),
            ));

        return $this;
    }

    /**
     * Asserts the response does not contain any JSON validation errors.
     *
     * ### Example
     * ```
     * $this->http->get(uri([BookController::class, 'index']))
     *     ->assertHasNoJsonValidationErrors();
     * ```
     */
    public function assertHasNoJsonValidationErrors(): self
    {
        Assert::assertNotContains($this->response->status, [Status::BAD_REQUEST, Status::FOUND]);
        Assert::assertNotInstanceOf(Invalid::class, $this->response);
        Assert::assertNull($this->response->getHeader('x-validation'));

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

    private function assertHasContainer(): void
    {
        if ($this->container === null) {
            Assert::fail('This assertion requires a container.');
        }
    }
}
