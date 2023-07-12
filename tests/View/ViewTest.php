<?php

namespace Tests\Tempest\View;

use App\Views\ViewModel;
use Tempest\AppConfig;
use Tempest\View\GenericView;
use Tests\Tempest\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function test_render()
    {
        $appConfig = $this->container->get(AppConfig::class);

        $view = GenericView::new(
            'Views/overview.php',
            name: 'Brent',
        );

        $renderedView = $view->render($appConfig);

        $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>
Hello Brent!</body>
</html>
HTML;

        $this->assertEquals($expected, $renderedView->contents);
    }

    /** @test */
    public function test_render_with_view_model()
    {
        $appConfig = $this->container->get(AppConfig::class);

        $view = new ViewModel('Brent');

        $renderedView = $view->render($appConfig);

        $expected = <<<HTML

ViewModel Brent, 2020-01-01
HTML;

        $this->assertEquals($expected, $renderedView->contents);
    }

    /** @test */
    public function test_with_view_function()
    {
        $appConfig = $this->container->get(AppConfig::class);

        $view = view('Views/overview.php')->data(
            name: 'Brent',
        );

        $renderedView = $view->render($appConfig);

        $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>
Hello Brent!</body>
</html>
HTML;

        $this->assertEquals($expected, $renderedView->contents);
    }
}
