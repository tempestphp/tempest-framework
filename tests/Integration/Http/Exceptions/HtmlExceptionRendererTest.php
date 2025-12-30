<?php

namespace Tests\Tempest\Integration\Http\Exceptions;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Auth\AccessControl\AccessDecision;
use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Core\Environment;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Method;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Session\CsrfTokenDidNotMatch;
use Tempest\Http\Status;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\Locale;
use Tempest\Router\Exceptions\DevelopmentException;
use Tempest\Router\Exceptions\HtmlExceptionRenderer;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Rules\IsNotNull;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\ExceptionThatConvertsToRedirectResponse;

final class HtmlExceptionRendererTest extends FrameworkIntegrationTestCase
{
    private HtmlExceptionRenderer $renderer {
        get => $this->container->get(HtmlExceptionRenderer::class);
    }

    #[Test]
    #[TestWith(['text/html', true])]
    #[TestWith(['application/xhtml+xml', true])]
    #[TestWith(['text/html, application/json', true])]
    #[TestWith(['application/json', false])]
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
            ],
            subject: new GenericRequest(
                method: Method::POST,
                uri: '/test',
                body: ['name' => null],
            ),
        ));

        $this->assertInstanceOf(GenericResponse::class, $response);
        $this->assertSame(Status::UNPROCESSABLE_CONTENT, $response->status);
        $this->assertNotNull($response->getHeader('x-validation'));
    }

    #[Test]
    public function access_denied_with_custom_message(): void
    {
        $response = $this->renderer->render(new AccessWasDenied(
            accessDecision: AccessDecision::denied('Access denied'),
        ));

        $this->assertSame(Status::FORBIDDEN, $response->status);
        $this->assertSame('Access denied', $response->body->data['message']);
    }

    #[Test]
    public function csrf_mismatch(): void
    {
        $response = $this->renderer->render(new CsrfTokenDidNotMatch());

        $this->assertSame(Status::UNPROCESSABLE_CONTENT, $response->status);
    }

    #[Test]
    public function http_request_failed(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(Status::BAD_REQUEST));

        $this->assertSame(Status::BAD_REQUEST, $response->status);
    }

    #[Test]
    public function generic_exception(): void
    {
        $response = $this->renderer->render(new Exception('Something went wrong'));

        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $response->status);
    }

    #[Test]
    public function renders_development_exception_in_local_environment(): void
    {
        $this->container->singleton(Environment::class, Environment::LOCAL);
        $this->container->singleton(GenericRequest::class, new GenericRequest(
            method: Method::GET,
            uri: '/',
        ));

        $this->assertInstanceOf(
            expected: DevelopmentException::class,
            actual: $this->renderer->render(new Exception('Something went wrong')),
        );
    }

    #[Test]
    public function does_not_render_development_exception_for_not_found_in_local(): void
    {
        $this->container->singleton(Environment::class, Environment::LOCAL);
        $this->container->singleton(GenericRequest::class, new GenericRequest(
            method: Method::GET,
            uri: '/',
        ));

        $response = $this->renderer->render(new HttpRequestFailed(Status::NOT_FOUND));

        $this->assertNotInstanceOf(DevelopmentException::class, $response);
        $this->assertSame(Status::NOT_FOUND, $response->status);
    }

    #[Test]
    public function converts_request_failed_string_bodies_to_proper_responses(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(
            status: Status::NOT_FOUND,
            cause: new NotFound('custom 404'),
        ));

        $this->assertInstanceOf(GenericResponse::class, $response);
        $this->assertSame(Status::NOT_FOUND, $response->status);
        $this->assertInstanceOf(GenericView::class, $response->body);
        $this->assertSame('custom 404', $response->body->data['message']);
    }

    #[Test]
    public function renders_original_response_body(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(
            status: Status::NOT_FOUND,
            cause: new NotFound(new GenericView('./error.view.php')),
        ));

        $this->assertInstanceOf(NotFound::class, $response);
        $this->assertSame(Status::NOT_FOUND, $response->status);
        $this->assertInstanceOf(GenericView::class, $response->body);
        $this->assertSame('./error.view.php', $response->body->path);
    }

    #[Test]
    public function uses_translations(): void
    {
        $this->container->get(Catalog::class)->add(
            locale: Locale::ENGLISH,
            key: 'http_status_error.418',
            message: 'I am a teapot.',
        );

        $response = $this->renderer->render(new HttpRequestFailed(Status::IM_A_TEAPOT));

        $this->assertSame(Status::IM_A_TEAPOT, $response->status);
        $this->assertInstanceOf(View::class, $response->body);
        $this->assertSame('I am a teapot.', $response->body->data['message']);
    }

    #[Test]
    public function http_request_failed_with_custom_message(): void
    {
        $response = $this->renderer->render(new HttpRequestFailed(
            status: Status::BAD_REQUEST,
            message: 'Custom error message',
        ));

        $this->assertSame(Status::BAD_REQUEST, $response->status);
        $this->assertInstanceOf(GenericResponse::class, $response);
        $this->assertInstanceOf(GenericView::class, $response->body);
        $this->assertSame('Custom error message', $response->body->data['message']);
    }
}
