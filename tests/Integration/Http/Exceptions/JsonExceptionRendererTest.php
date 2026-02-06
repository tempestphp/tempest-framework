<?php

namespace Tests\Tempest\Integration\Http\Exceptions;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Auth\AccessControl\AccessDecision;
use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Core\Environment;
use Tempest\Http\GenericRequest;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Method;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tempest\Router\Exceptions\JsonExceptionRenderer;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Rules\IsNotNull;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\ExceptionThatConvertsToRedirectResponse;

final class JsonExceptionRendererTest extends FrameworkIntegrationTestCase
{
    private JsonExceptionRenderer $renderer {
        get => $this->container->get(JsonExceptionRenderer::class);
    }

    #[Test]
    #[TestWith(['application/json', true])]
    #[TestWith(['application/json, text/html', true])]
    #[TestWith(['text/html', false])]
    #[TestWith(['application/xhtml+xml', false])]
    public function can_render_depends_on_accept_header(string $accept, bool $expectsToRender): void
    {
        $this->container->singleton(
            GenericRequest::class,
            $request = new GenericRequest(
                method: Method::GET,
                uri: '/',
                headers: ['Accept' => $accept],
            ),
        );

        $this->assertSame($expectsToRender, $this->renderer->canRender(new Exception(), $request));
    }

    #[Test]
    public function converts_to_response(): void
    {
        $response = $this->renderer->render(new ExceptionThatConvertsToRedirectResponse());

        $this->assertSame(Status::FOUND, $response->status);
    }

    #[Test]
    public function validation_failed(): void
    {
        $response = $this->renderer->render(new ValidationFailed(
            failingRules: [
                'name' => [new FailingRule(new IsNotNull())],
                'email' => [new FailingRule(new IsNotNull())],
            ],
            subject: new GenericRequest(
                method: Method::POST,
                uri: '/test',
                body: ['name' => null, 'email' => null],
            ),
        ));

        $this->assertInstanceOf(Json::class, $response);
        $this->assertSame(Status::UNPROCESSABLE_CONTENT, $response->status);
        $this->assertArrayHasKey('message', $response->body);
        $this->assertArrayHasKey('errors', $response->body);
        $this->assertArrayHasKey('name', $response->body['errors']);
        $this->assertArrayHasKey('email', $response->body['errors']);
    }

    #[Test]
    public function access_denied_with_custom_message(): void
    {
        $response = $this->renderer->render(new AccessWasDenied(
            accessDecision: AccessDecision::denied('Custom access denied message'),
        ));

        $this->assertInstanceOf(Json::class, $response);
        $this->assertSame(Status::FORBIDDEN, $response->status);
        $this->assertArrayHasKey('message', $response->body);
        $this->assertSame('Custom access denied message', $response->body['message']);
    }

    #[Test]
    public function http_request_failed(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(Status::BAD_REQUEST));

        $this->assertInstanceOf(Json::class, $response);
        $this->assertSame(Status::BAD_REQUEST, $response->status);
    }

    #[Test]
    public function http_request_failed_not_found(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(Status::NOT_FOUND));

        $this->assertInstanceOf(NotFound::class, $response);
        $this->assertSame(Status::NOT_FOUND, $response->status);
    }

    #[Test]
    public function generic_exception(): void
    {
        $response = $this->renderer->render(new Exception('Something went wrong'));

        $this->assertInstanceOf(Json::class, $response);
        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $response->status);
        $this->assertArrayHasKey('message', $response->body);
    }

    #[Test]
    public function includes_debug_info_in_local_environment(): void
    {
        $this->container->singleton(Environment::class, Environment::LOCAL);

        $exception = new Exception('Test error message');
        $response = $this->renderer->render($exception);

        $this->assertInstanceOf(Json::class, $response);
        $this->assertArrayHasKey('debug', $response->body);
        $this->assertSame('Test error message', $response->body['debug']['message']);
        $this->assertSame(Exception::class, $response->body['debug']['exception']);
        $this->assertArrayHasKey('file', $response->body['debug']);
        $this->assertArrayHasKey('line', $response->body['debug']);
        $this->assertArrayHasKey('trace', $response->body['debug']);
    }

    #[Test]
    public function does_not_include_debug_info_in_production_environment(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $response = $this->renderer->render(new Exception('Test error'));

        $this->assertInstanceOf(Json::class, $response);
        $this->assertArrayNotHasKey('debug', $response->body);
    }

    #[Test]
    public function http_request_failed_with_string_cause_body(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(
            status: Status::BAD_REQUEST,
            cause: new NotFound('Custom error message'),
        ));

        $this->assertInstanceOf(Json::class, $response);
        $this->assertSame(Status::BAD_REQUEST, $response->status);
        $this->assertSame('Custom error message', $response->body['message']);
    }

    #[Test]
    public function uses_status_description(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(Status::IM_A_TEAPOT));

        $this->assertSame("I'm a teapot", $response->body['message']);
    }

    #[Test]
    public function http_request_failed_with_custom_message(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(
            status: Status::BAD_REQUEST,
            message: 'Custom error message',
        ));

        $this->assertInstanceOf(Json::class, $response);
        $this->assertSame(Status::BAD_REQUEST, $response->status);
        $this->assertSame('Custom error message', $response->body['message']);
    }
}
