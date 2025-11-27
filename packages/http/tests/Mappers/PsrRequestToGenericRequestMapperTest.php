<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Mappers;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionMethod;
use Tempest\Clock\GenericClock;
use Tempest\Core\AppConfig;
use Tempest\Cryptography\Encryption\EncryptionAlgorithm;
use Tempest\Cryptography\Encryption\EncryptionConfig;
use Tempest\Cryptography\Encryption\GenericEncrypter;
use Tempest\Cryptography\Signing\GenericSigner;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tempest\Cryptography\Timelock;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Method;

final class PsrRequestToGenericRequestMapperTest extends TestCase
{
    private PsrRequestToGenericRequestMapper $mapper;
    private ReflectionMethod $requestMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new PsrRequestToGenericRequestMapper(
            $this->createEncrypter(),
            new CookieManager(
                new AppConfig(baseUri: 'https://test.com'),
                new GenericClock(),
            ),
        );

        $reflection = new ReflectionClass($this->mapper);
        $this->requestMethod = $reflection->getMethod('requestMethod');
    }

    #[DataProvider('nonPostMethodsProvider')]
    public function test_non_post_requests_are_not_affected_by_method_param(string $originalMethod): void
    {
        $request = $this->createServerRequest(
            $originalMethod,
            ['_method' => 'DELETE'],
        );

        $method = $this->requestMethod->invoke($this->mapper, $request, ['_method' => 'DELETE']);

        $this->assertSame(Method::from($originalMethod), $method);
    }

    #[DataProvider('validSpoofedMethodsProvider')]
    public function test_post_with_valid_method_is_spoofed(string $spoofedMethod): void
    {
        $request = $this->createServerRequest(
            'POST',
            ['_method' => $spoofedMethod],
        );

        $method = $this->requestMethod->invoke($this->mapper, $request, ['_method' => $spoofedMethod]);

        $this->assertSame(Method::from(strtoupper($spoofedMethod)), $method);
    }

    public function test_post_with_invalid_method_is_not_spoofed(): void
    {
        $request = $this->createServerRequest(
            'POST',
            ['_method' => 'INVALID'],
        );

        $method = $this->requestMethod->invoke($this->mapper, $request, ['_method' => 'INVALID']);

        $this->assertSame(Method::POST, $method);
    }

    public function test_method_param_is_case_insensitive(): void
    {
        $request = $this->createServerRequest(
            'POST',
            ['_method' => 'delete'],
        );

        $method = $this->requestMethod->invoke($this->mapper, $request, ['_method' => 'delete']);

        $this->assertSame(Method::DELETE, $method);
    }

    public static function nonPostMethodsProvider(): array
    {
        return [
            ['GET'],
            ['PUT'],
            ['PATCH'],
            ['DELETE'],
            ['HEAD'],
            ['OPTIONS'],
            ['TRACE'],
            ['CONNECT'],
        ];
    }

    public static function validSpoofedMethodsProvider(): array
    {
        return [
            ['PUT'],
            ['PATCH'],
            ['DELETE'],
        ];
    }

    private function createEncrypter(): GenericEncrypter
    {
        return new GenericEncrypter(
            signer: new GenericSigner(
                config: new SigningConfig(
                    algorithm: SigningAlgorithm::SHA256,
                    key: 'my_secret_key',
                    minimumExecutionDuration: false,
                ),
                timelock: new Timelock(new GenericClock()),
            ),
            config: new EncryptionConfig(
                algorithm: EncryptionAlgorithm::AES_256_GCM,
                key: 'my_secret_key',
            ),
        );
    }

    private function createServerRequest(string $method, array $body = []): ServerRequestInterface
    {
        $request = new ServerRequest([], [], '/', $method);

        if ($body !== []) {
            $request = $request->withParsedBody($body);
        }

        $stream = new Stream('php://temp', 'r+');
        $request = $request->withBody($stream);

        return $request;
    }
}
