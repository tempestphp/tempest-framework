<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Session\Session;
use Tempest\Validation\Rule;
use Tempest\Validation\Rules\IsEmail;
use Tests\Tempest\Fixtures\Controllers\MultiFormController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\uri;

/**
 * @internal
 */
final class NamedErrorBagsTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure a clean session for each test
        $this->container->get(Session::class)->destroy();
    }

    public function test_default_error_bag_works_without_attribute(): void
    {
        $this->http
            ->post(
                uri: uri([MultiFormController::class, 'defaultForm']),
                body: ['number' => 11],
                headers: ['referer' => '/form'],
            )
            ->assertRedirect('/form')
            ->assertHasSession(Session::VALIDATION_ERRORS, function (Session $_session, array $data): void {
                $this->assertArrayHasKey('default', $data);
                $this->assertArrayHasKey('number', $data['default']);
            });
    }

    public function test_named_error_bags_separate_errors(): void
    {
        $this->http
            ->post(
                uri: uri([MultiFormController::class, 'login']),
                body: ['email' => 'invalid', 'password' => ''],
                headers: ['referer' => '/login'],
            )
            ->assertRedirect('/login')
            ->assertHasSession(Session::VALIDATION_ERRORS, function (Session $_session, array $data): void {
                $this->assertArrayHasKey('login', $data);
                $this->assertArrayHasKey('email', $data['login']);
                $this->assertArrayHasKey('password', $data['login']);
                $this->assertArrayNotHasKey('register', $data);
            });

        $this->http
            ->post(
                uri: uri([MultiFormController::class, 'register']),
                body: ['email' => 'invalid', 'password' => '', 'name' => ''],
                headers: ['referer' => '/register'],
            )
            ->assertRedirect('/register')
            ->assertHasSession(Session::VALIDATION_ERRORS, function (Session $_session, array $data): void {
                $this->assertArrayHasKey('register', $data);
                $this->assertArrayHasKey('email', $data['register']);
                $this->assertArrayHasKey('password', $data['register']);
                $this->assertArrayHasKey('name', $data['register']);
            });
    }

    public function test_session_methods_with_named_bags(): void
    {
        $session = $this->container->get(Session::class);

        $session->set(Session::VALIDATION_ERRORS, [
            'login' => ['email' => ['error1']],
            'register' => ['email' => ['error2']],
        ]);

        $loginErrors = $session->getErrorsFor('email', 'login');
        $registerErrors = $session->getErrorsFor('email', 'register');

        $this->assertEquals(['error1'], $loginErrors);
        $this->assertEquals(['error2'], $registerErrors);

        $session->set(Session::ORIGINAL_VALUES, [
            'login' => ['email' => 'login@example.com'],
            'register' => ['email' => 'register@example.com'],
        ]);

        $loginEmail = $session->getOriginalValueFor('email', '', 'login');
        $registerEmail = $session->getOriginalValueFor('email', '', 'register');

        $this->assertEquals('login@example.com', $loginEmail);
        $this->assertEquals('register@example.com', $registerEmail);
    }

    public function test_backward_compatibility_with_old_session_structure(): void
    {
        $session = $this->container->get(Session::class);

        $rule = new IsEmail();
        $session->set(Session::VALIDATION_ERRORS, ['email' => [$rule]]);
        $session->set(Session::ORIGINAL_VALUES, ['email' => 'old@example.com']);

        $errors = $session->getErrorsFor('email');
        $value = $session->getOriginalValueFor('email');

        $this->assertCount(1, $errors);
        $this->assertInstanceOf(Rule::class, $errors[0]);
        $this->assertEquals('old@example.com', $value);

        $namedErrors = $session->getErrorsFor('email', 'other');
        $namedValue = $session->getOriginalValueFor('email', 'default', 'other');

        $this->assertEquals([], $namedErrors);
        $this->assertEquals('default', $namedValue);
    }

    public function test_clear_errors_for_specific_bag(): void
    {
        $session = $this->container->get(Session::class);

        $session->flashValidationErrors(['field' => ['error']], 'bag1');
        $session->flashValidationErrors(['field' => ['error']], 'bag2');

        $session->clearErrors('bag1');

        $this->assertEquals([], $session->getAllErrors('bag1'));
        $this->assertEquals(['field' => ['error']], $session->getAllErrors('bag2'));
    }

    public function test_invalid_response_with_error_bag(): void
    {
        $response = $this->http
            ->post(
                uri: uri([MultiFormController::class, 'login']),
                body: ['email' => 'invalid'],
                headers: ['referer' => '/login'],
            )
            ->assertRedirect('/login');

        $this->assertEquals('login', $response->response->getHeader('x-validation-bag')->values[0]);
    }
}
