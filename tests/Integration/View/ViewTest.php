<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Http\Status;
use Tests\Tempest\Fixtures\Controllers\TestController;
use Tests\Tempest\Fixtures\Views\ViewModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\uri;
use function Tempest\view;

/**
 * @internal
 */
#[CoversNothing]
final class ViewTest extends FrameworkIntegrationTestCase
{
    public function test_render(): void
    {
        $view = view(__DIR__ . '/../../Fixtures/Views/overview.view.php')->data(name: 'Brent');

        $html = $this->render($view);

        $this->assertStringContainsString(
            'Brent!',
            $html,
        );

        $this->assertStringContainsString(
            '<title></title>',
            $html,
        );
    }

    public function test_render_with_view_model(): void
    {
        $view = new ViewModel('Brent');

        $html = $this->render($view);

        $expected = <<<HTML
        ViewModel Brent, 2020-01-01
        HTML;

        $this->assertEquals($expected, $html);
    }

    public function test_custom_view_with_response_data(): void
    {
        $this->http
            ->get(uri([TestController::class, 'viewWithResponseData']))
            ->assertHasHeader('x-from-view')
            ->assertStatus(Status::CREATED);
    }
}
