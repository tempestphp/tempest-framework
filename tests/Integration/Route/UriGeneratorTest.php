<?php

namespace Tests\Tempest\Integration\Route;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use Tempest\Core\AppConfig;
use Tempest\Database\PrimaryKey;
use Tempest\DateTime\Duration;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Router\UriGenerator;
use Tests\Tempest\Fixtures\Controllers\ControllerWithEnumBinding;
use Tests\Tempest\Fixtures\Controllers\EnumForController;
use Tests\Tempest\Fixtures\Controllers\TestController;
use Tests\Tempest\Fixtures\Controllers\UriGeneratorController;
use Tests\Tempest\Fixtures\Modules\Books\BookController;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Router\uri;

final class UriGeneratorTest extends FrameworkIntegrationTestCase
{
    private UriGenerator $generator {
        get => $this->container->get(UriGenerator::class);
    }

    #[Test]
    public function generates_uri(): void
    {
        $this->assertEquals('/test/1/a', $this->generator->createUri([TestController::class, 'withParams'], id: 1, name: 'a'));

        $this->assertEquals('/test/1', $this->generator->createUri([TestController::class, 'withComplexCustomRegexParams'], id: 1));

        $this->assertEquals('/test/1/a/b', $this->generator->createUri([TestController::class, 'withCustomRegexParams'], id: 1, name: 'a/b'));
        $this->assertEquals('/test', $this->generator->createUri(TestController::class));

        $this->assertEquals('/test/1/a?q=hi&i=test', $this->generator->createUri([TestController::class, 'withParams'], id: 1, name: 'a', q: 'hi', i: 'test'));

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->assertEquals('https://test.com/test/1/a', $this->generator->createUri([TestController::class, 'withParams'], id: 1, name: 'a'));

        $this->assertSame('https://test.com/abc', $this->generator->createUri('/abc'));
        $this->assertEquals('https://test.com/test/1/a/b/c/d', $this->generator->createUri([TestController::class, 'withCustomRegexParams'], id: 1, name: 'a/b/c/d'));
    }

    #[Test]
    public function uri_functions(): void
    {
        $this->assertEquals('/test/1/a', uri([TestController::class, 'withParams'], id: 1, name: 'a'));
    }

    #[Test]
    public function uri_generation_with_invalid_fqcn(): void
    {
        $this->expectException(ReflectionException::class);

        $this->generator->createUri(TestController::class . 'Invalid');
    }

    #[Test]
    public function uri_generation_with_query_param(): void
    {
        $this->assertSame('/test?test=foo', $this->generator->createUri(TestController::class, test: 'foo'));
    }

    #[Test]
    public function generate_uri_with_enum(): void
    {
        $this->assertSame(
            '/with-enum/foo',
            $this->generator->createUri(ControllerWithEnumBinding::class, input: EnumForController::FOO),
        );

        $this->assertSame(
            '/with-enum/bar',
            $this->generator->createUri(ControllerWithEnumBinding::class, input: EnumForController::BAR),
        );
    }

    #[Test]
    public function generate_uri_with_bindable_model(): void
    {
        $book = Book::new(id: new PrimaryKey('abc'));

        $this->assertSame(
            '/books/abc',
            uri([BookController::class, 'show'], book: $book),
        );
    }

    #[Test]
    public function generate_uri_with_primary_key(): void
    {
        $book = Book::new(id: new PrimaryKey('abc'));

        $this->assertSame(
            '/books/abc',
            uri([BookController::class, 'show'], book: $book->id),
        );
    }

    #[Test]
    public function uri_with_query_param_that_collides_partially_with_route_param(): void
    {
        $this->assertSame(
            '/test-with-collision/hi?id=1',
            $this->generator->createUri([UriGeneratorController::class, 'withCollidingNames'], id: '1', idea: 'hi'),
        );
    }

    #[Test]
    public function is_current_uri(): void
    {
        $this->http->get('/test')->assertOk();

        $this->assertTrue($this->generator->isCurrentUri([TestController::class, '__invoke']));
        $this->assertFalse($this->generator->isCurrentUri([TestController::class, 'withParams']));
        $this->assertFalse($this->generator->isCurrentUri([TestController::class, 'withParams'], id: 1));
        $this->assertFalse($this->generator->isCurrentUri([TestController::class, 'withParams'], id: 1, name: 'a'));
    }

    #[Test]
    public function is_current_uri_with_constrained_parameters(): void
    {
        $this->http->get('/test/1/a')->assertOk();

        $this->assertTrue($this->generator->isCurrentUri([TestController::class, 'withCustomRegexParams']));
        $this->assertTrue($this->generator->isCurrentUri([TestController::class, 'withCustomRegexParams'], id: 1));
        $this->assertTrue($this->generator->isCurrentUri([TestController::class, 'withCustomRegexParams'], id: 1, name: 'a'));
        $this->assertFalse($this->generator->isCurrentUri([TestController::class, 'withCustomRegexParams'], id: 1, name: 'b'));
        $this->assertFalse($this->generator->isCurrentUri([TestController::class, 'withCustomRegexParams'], id: 0, name: 'a'));
        $this->assertFalse($this->generator->isCurrentUri([TestController::class, 'withCustomRegexParams'], id: 0, name: 'b'));
    }

    #[Test]
    public function is_current_uri_with_enum(): void
    {
        $this->http->get('/with-enum/foo')->assertOk();

        $this->assertTrue($this->generator->isCurrentUri(ControllerWithEnumBinding::class));
        $this->assertTrue($this->generator->isCurrentUri(ControllerWithEnumBinding::class, input: EnumForController::FOO));
        $this->assertFalse($this->generator->isCurrentUri(ControllerWithEnumBinding::class, input: EnumForController::BAR));
    }

    #[Test]
    public function signed_uri(): void
    {
        $uri = $this->generator->createSignedUri(
            action: [TestController::class, 'withParams'],
            id: 1,
            name: 'a',
            foo: 'bar',
        );

        $this->assertTrue($this->generator->hasValidSignature(
            new GenericRequest(Method::POST, $uri),
        ));
    }

    #[Test]
    #[TestWith(['/1', '/2'], name: 'tampered path')]
    #[TestWith(['foo=', 'foo=uwu'], name: 'tampered query param')]
    #[TestWith(['foo=', 'bar=baz&foo='], name: 'additional query param')]
    #[TestWith(['signature=', 'signature=invalid'], name: 'tampered signature')]
    public function tampered_uri(string $fragment, string $tamper): void
    {
        $uri = $this->generator->createSignedUri(
            action: [TestController::class, 'withParams'],
            id: 1,
            name: 'a',
            foo: 'bar',
        );

        $this->assertFalse($this->generator->hasValidSignature(
            new GenericRequest(Method::POST, str_replace($fragment, $tamper, $uri)),
        ));
    }

    #[Test]
    public function temporary_signed_uri(): void
    {
        $clock = $this->clock();

        $uri = $this->generator->createTemporarySignedUri(
            action: [TestController::class, 'withParams'],
            duration: Duration::minutes(20),
            id: 1,
            name: 'a',
            foo: 'bar',
        );

        $request = new GenericRequest(Method::POST, $uri);

        $this->assertTrue($this->generator->hasValidSignature($request));
        $clock->plus(Duration::minutes(15));
        $this->assertTrue($this->generator->hasValidSignature($request));
        $clock->plus(Duration::minutes(5));
        $this->assertFalse($this->generator->hasValidSignature($request));
    }

    #[Test]
    public function tampered_duration_in_signed_uri(): void
    {
        $clock = $this->clock();

        $uri = $this->generator->createTemporarySignedUri(
            action: [TestController::class, 'withParams'],
            duration: Duration::minutes(20),
            id: 1,
            name: 'a',
            foo: 'bar',
        );

        $timestamp = $clock->now()->plusMinutes(20)->getTimestamp()->getSeconds();
        $tamperedUri = str_replace((string) $timestamp, (string) ($timestamp + 20), $uri);

        $this->assertFalse($this->generator->hasValidSignature(new GenericRequest(Method::POST, $tamperedUri)));
    }

    #[Test]
    public function cannot_add_custom_expires_at(): void
    {
        $this->expectExceptionMessage('Cannot create a signed URI with an "expires_at" parameter. It will be added automatically.');

        $this->generator->createTemporarySignedUri(
            action: [TestController::class, 'withParams'],
            duration: Duration::minutes(20),
            id: 1,
            name: 'a',
            foo: 'bar',
            expires_at: 'uwu',
        );
    }

    #[Test]
    public function cannot_add_custom_signature(): void
    {
        $this->expectExceptionMessage('Cannot create a signed URI with a "signature" parameter. It will be added automatically.');

        $this->generator->createSignedUri(
            action: [TestController::class, 'withParams'],
            id: 1,
            name: 'a',
            foo: 'bar',
            signature: 'uwu',
        );
    }
}
