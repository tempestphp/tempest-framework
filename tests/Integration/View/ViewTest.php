<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Status;
use Tests\Tempest\Fixtures\Controllers\TestController;
use Tests\Tempest\Fixtures\Views\ViewModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Router\uri;
use function Tempest\View\view;

/**
 * @internal
 */
final class ViewTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function render(): void
    {
        $view = view(__DIR__ . '/../../Fixtures/Views/overview.view.php')->data(name: 'Brent');

        $html = $this->view->render($view);

        $this->assertStringContainsString(
            'Brent!',
            $html,
        );

        $this->assertStringContainsString(
            '<title></title>',
            $html,
        );
    }

    #[Test]
    public function render_with_view_model(): void
    {
        $view = new ViewModel('Brent');

        $html = $this->view->render($view);

        $expected = <<<HTML
        ViewModel Brent, 2020-01-01
        HTML;

        $this->assertEquals($expected, $html);
    }

    #[Test]
    public function custom_view_with_response_data(): void
    {
        $this->http
            ->get(uri([TestController::class, 'viewWithResponseData']))
            ->assertHasHeader('x-from-view')
            ->assertStatus(Status::CREATED);
    }
}
