<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\View\GenericView;
use Tests\Tempest\Fixtures\Controllers\RelativeViewController;
use Tests\Tempest\Fixtures\Controllers\TestController;
use Tests\Tempest\Fixtures\Views\ViewModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Router\uri;

/**
 * @internal
 */
final class ViewDataTest extends FrameworkIntegrationTestCase
{
    public function test_can_assert_view_data(): void
    {
        $this->http
            ->get(uri([TestController::class, 'withView']))
            ->assertViewData('name')
            ->assertViewData('name', function (array $data, string $value): void {
                $this->assertEquals(['name' => 'Brent'], $data);
                $this->assertEquals('Brent', $value);
            })
            ->assertViewDataMissing('email')
            ->assertViewDataAll(function (array $data): void {
                $this->assertEquals(['name' => 'Brent'], $data);
            });
    }

    public function test_can_assert_generic_view_model(): void
    {
        $this->http
            ->get(uri([RelativeViewController::class, 'asFunction']))
            ->assertViewModel(GenericView::class)
            ->assertView('./relative-view.view.php');
    }

    public function test_can_assert_view_model(): void
    {
        $this->http
            ->get(uri([TestController::class, 'viewModel']))
            ->assertViewModel(ViewModel::class, function (ViewModel $model): void {
                $this->assertEquals('Brent', $model->name);
            })
            ->assertView('withViewModel.php');
    }
}
