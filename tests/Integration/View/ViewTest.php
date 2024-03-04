<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use App\Controllers\TestController;
use App\Views\ViewModel;
use Tempest\AppConfig;
use Tempest\Http\Status;
use Tempest\Testing\IntegrationTest;
use function Tempest\uri;
use function Tempest\view;
use Tempest\View\GenericView;

class ViewTest extends IntegrationTest
{
    /** @test */
    public function test_render()
    {
        $appConfig = $this->container->get(AppConfig::class);

        $view = new GenericView(
            'Views/overview.php',
            params: [
                'name' => 'Brent',
            ],
        );

        $html = $view->render($appConfig);

        $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>Hello Brent!</body>
</html>
HTML;

        $this->assertEquals($expected, $html);
    }

    /** @test */
    public function test_render_with_view_model()
    {
        $appConfig = $this->container->get(AppConfig::class);

        $view = new ViewModel('Brent');

        $html = $view->render($appConfig);

        $expected = <<<HTML

ViewModel Brent, 2020-01-01
HTML;

        $this->assertEquals($expected, $html);
    }

    /** @test */
    public function test_with_view_function()
    {
        $appConfig = $this->container->get(AppConfig::class);

        $view = view('Views/overview.php')->data(
            name: 'Brent',
        );

        $html = $view->render($appConfig);

        $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>Hello Brent!</body>
</html>
HTML;

        $this->assertEquals($expected, $html);
    }

    /** @test */
    public function test_raw_and_escaping()
    {
        $html = view('Views/rawAndEscaping.php')->data(
            property: '<h1>hi</h1>',
        )->render($this->container->get(AppConfig::class));

        $expected = <<<HTML
        &lt;h1&gt;hi&lt;/h1&gt;<h1>hi</h1>
        HTML;

        $this->assertSame(trim($expected), trim($html));
    }

    /** @test */
    public function test_extends_parameters()
    {
        $html = view('Views/extendsWithVariables.php')->render($this->container->get(AppConfig::class));

        $this->assertStringContainsString('<title>Test</title>', $html);
        $this->assertStringContainsString('<h1>Hello</h1>', $html);
    }

    /** @test */
    public function test_named_slots()
    {
        $html = view('Views/extendsWithNamedSlot.php')->render($this->container->get(AppConfig::class));

        $this->assertStringContainsString(
            needle: <<<HTML
            <div class="defaultSlot"><h1>beginning</h1>
            <p>in between</p>
            <p>default slot</p></div>
            HTML,
            haystack: $html
        );

        $this->assertStringContainsString(
            needle: <<<HTML
            <div class="namedSlot"><h1>named slot</h1></div>
            HTML,
            haystack: $html
        );

        $this->assertStringContainsString(
            needle: <<<HTML
            <div class="namedSlot2"><h1>named slot 2</h1></div>
            HTML,
            haystack: $html
        );
    }

    /** @test */
    public function test_include_parameters()
    {
        $html = view('Views/include-parent.php')
            ->data(prop: 'test')
            ->render($this->container->get(AppConfig::class));

        $expected = <<<HTML
        parent test 
        child test
        HTML;

        $this->assertSame(trim($expected), trim($html));
    }

    /** @test */
    public function view_model_with_response_data()
    {
        $this->http
            ->get(uri([TestController::class, 'viewModelWithResponseData']))
            ->assertHasHeader('x-from-viewmodel')
            ->assertStatus(Status::CREATED);
    }
}
