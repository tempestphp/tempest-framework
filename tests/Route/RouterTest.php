<?php

declare(strict_types=1);

use App\Controllers\TestController;
use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\GenericRouter;
use Tempest\Http\Router;
use Tempest\Http\Status;
use function Tempest\request;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('dispatch', function () {
    $router = $this->container->get(GenericRouter::class);

    $response = $router->dispatch(request('/test'));

    expect($response->getStatus())->toEqual(Status::OK);
    expect($response->getBody())->toEqual('test');
});

test('dispatch with parameter', function () {
    $router = $this->container->get(GenericRouter::class);

    $response = $router->dispatch(request('/test/1/a'));

    expect($response->getStatus())->toEqual(Status::OK);
    expect($response->getBody())->toEqual('1a');
});

test('generate uri', function () {
    $router = $this->container->get(GenericRouter::class);

    expect($router->toUri([TestController::class, 'withParams'], id: 1, name: 'a'))->toEqual('/test/1/a');
    expect($router->toUri(TestController::class))->toEqual('/test');
});

test('with view', function () {
    $router = $this->container->get(GenericRouter::class);

    $response = $router->dispatch(request('/view'));

    expect($response->getStatus())->toEqual(Status::OK);

    $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>Hello Brent!</body>
</html>
HTML;

    expect($response->getBody())->toEqual($expected);
});

test('route binding', function () {
    $this->migrate(
        CreateMigrationsTable::class,
        CreateBookTable::class,
        CreateAuthorTable::class,
    );

    Book::create(
        title: 'Test',
        author: new Author(name: 'Brent'),
    );

    $router = $this->container->get(Router::class);

    $response = $router->dispatch(request('/books/1'));

    expect($response->getStatus())->toBe(Status::OK);
    expect($response->getBody())->toBe('Test');
});

test('middleware', function () {
    $router = $this->container->get(GenericRouter::class);

    $response = $router->dispatch(request('/with-middleware'));

    expect($response->getHeaders())->toEqual(['middleware' => 'from-dependency']);
});
