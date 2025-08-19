<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\Http\Session\Session;
use Tempest\View\GenericView;
use Tempest\View\ViewRenderer;
use Tests\Tempest\Fixtures\Controllers\MultiFormController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\uri;

/**
 * @internal
 */
final class FormWithErrorBagTest extends FrameworkIntegrationTestCase
{
    public function test_form_with_bag_sends_hidden_input(): void
    {
        $view = $this->renderTemplate('
            <x-form action="/test" bag="login">
                <input type="text" name="email" />
                <input type="password" name="password" />
            </x-form>
        ');

        $this->assertStringContainsString('<input type="hidden" name="__error_bag" value="login"', $view);
    }

    public function test_form_without_bag_has_no_hidden_input(): void
    {
        $view = $this->renderTemplate('
            <x-form action="/test">
                <input type="text" name="email" />
            </x-form>
        ');

        $this->assertStringNotContainsString('__error_bag', $view);
    }

    public function test_form_with_bag_hidden_input_is_handled_by_middleware(): void
    {
        $response = $this->http
            ->post(
                uri: uri([MultiFormController::class, 'defaultForm']),
                body: [
                    'number' => 11,
                    '__error_bag' => 'custom-bag',
                ],
                headers: ['referer' => '/form'],
            );

        $session = $this->container->get(Session::class);
        $errors = $session->get(Session::VALIDATION_ERRORS);

        $this->assertArrayHasKey('custom-bag', $errors);
        $this->assertArrayHasKey('number', $errors['custom-bag']);
    }

    public function test_error_bag_from_hidden_input_takes_precedence_over_attribute(): void
    {
        $response = $this->http
            ->post(
                uri: uri([MultiFormController::class, 'login']),
                body: [
                    'email' => 'invalid',
                    'password' => '',
                    '__error_bag' => 'form-override',
                ],
                headers: ['referer' => '/login'],
            );

        $session = $this->container->get(Session::class);
        $errors = $session->get(Session::VALIDATION_ERRORS);

        $this->assertArrayHasKey('form-override', $errors);
        $this->assertArrayHasKey('email', $errors['form-override']);
        $this->assertArrayHasKey('password', $errors['form-override']);
        $this->assertArrayNotHasKey('login', $errors);
    }

    private function renderTemplate(string $template): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_view_') . '.view.php';
        file_put_contents($tempFile, $template);

        try {
            $view = new GenericView($tempFile);
            $renderer = $this->container->get(ViewRenderer::class);
            return $renderer->render($view);
        } finally {
            unlink($tempFile);
        }
    }
}
