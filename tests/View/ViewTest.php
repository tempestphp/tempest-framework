<?php

declare(strict_types=1);

use App\Views\ViewModel;
use Tempest\AppConfig;
use Tempest\View\GenericView;
use Tests\Tempest\TestCase;
use function Tempest\view;

uses(TestCase::class);

test('render', function () {
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

	expect($html)->toEqual($expected);
});

test('render with view model', function () {
	$appConfig = $this->container->get(AppConfig::class);

	$view = new ViewModel('Brent');

	$html = $view->render($appConfig);

	$expected = <<<HTML

ViewModel Brent, 2020-01-01
HTML;

	expect($html)->toEqual($expected);
});

test('with view function', function () {
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

	expect($html)->toEqual($expected);
});

test('raw and escaping', function () {
	$html = view('Views/rawAndEscaping.php')->data(
		property: '<h1>hi</h1>',
	)->render($this->container->get(AppConfig::class));

	$expected = <<<HTML
        &lt;h1&gt;hi&lt;/h1&gt;<h1>hi</h1>
        HTML;

	expect(trim($html))->toBe(trim($expected));
});

test('extends parameters', function () {
	$html = view('Views/extendsWithVariables.php')->render($this->container->get(AppConfig::class));

	$this->assertStringContainsString('<title>Test</title>', $html);
	$this->assertStringContainsString('<h1>Hello</h1>', $html);
});

test('named slots', function () {
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
});

test('include parameters', function () {
	$html = view('Views/include-parent.php')
		->data(prop: 'test')
		->render($this->container->get(AppConfig::class));

	$expected = <<<HTML
        parent test
        child test
        HTML;

	expect(trim($html))->toBe(trim($expected));
});
