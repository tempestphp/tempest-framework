<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Mappers;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
use Tempest\Http\Cookie\CookieConfig;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Method;
use Tempest\Http\Upload;

final class PsrRequestToGenericRequestMapperTest extends TestCase
{
    private PsrRequestToGenericRequestMapper $mapper;

    private ReflectionMethod $requestMethod;

    private ReflectionMethod $createUploads;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new PsrRequestToGenericRequestMapper(
            $this->createEncrypter(),
            new CookieManager(
                new AppConfig(baseUri: 'https://test.com'),
                new GenericClock(),
            ),
            new CookieConfig(),
        );

        $reflection = new ReflectionClass($this->mapper);
        $this->requestMethod = $reflection->getMethod('requestMethod');
        $this->createUploads = $reflection->getMethod('createUploads');
    }

    #[Test]
    public function nested_uploaded_files_are_mapped_to_uploads(): void
    {
        $files = $this->createUploads->invoke($this->mapper, [
            'avatar' => $this->createUploadedFile('avatar.png'),
            'documents' => [
                $this->createUploadedFile('one.txt'),
                'contract' => $this->createUploadedFile('contract.pdf'),
            ],
        ]);

        $this->assertInstanceOf(Upload::class, $files['avatar']);
        $this->assertSame('avatar.png', $files['avatar']->getClientFilename());
        $this->assertSame('one.txt', $files['documents'][0]->getClientFilename());
        $this->assertSame('contract.pdf', $files['documents']['contract']->getClientFilename());
    }

    #[DataProvider('nonPostMethodsProvider')]
    #[Test]
    public function non_post_requests_are_not_affected_by_method_param(string $originalMethod): void
    {
        $request = $this->createServerRequest(
            $originalMethod,
            ['_method' => 'DELETE'],
        );

        $method = $this->requestMethod->invoke($this->mapper, $request, ['_method' => 'DELETE']);

        $this->assertSame(Method::from($originalMethod), $method);
    }

    #[DataProvider('validSpoofedMethodsProvider')]
    #[Test]
    public function post_with_valid_method_is_spoofed(string $spoofedMethod): void
    {
        $request = $this->createServerRequest(
            'POST',
            ['_method' => $spoofedMethod],
        );

        $method = $this->requestMethod->invoke($this->mapper, $request, ['_method' => $spoofedMethod]);

        $this->assertSame(Method::from(strtoupper($spoofedMethod)), $method);
    }

    #[Test]
    public function post_with_invalid_method_is_not_spoofed(): void
    {
        $request = $this->createServerRequest(
            'POST',
            ['_method' => 'INVALID'],
        );

        $method = $this->requestMethod->invoke($this->mapper, $request, ['_method' => 'INVALID']);

        $this->assertSame(Method::POST, $method);
    }

    #[Test]
    public function method_param_is_case_insensitive(): void
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
            ['QUERY'],
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
        return $request->withBody($stream);
    }

    private function createUploadedFile(string $filename): UploadedFile
    {
        return new UploadedFile(
            streamOrFile: new Stream('php://temp', 'r+'),
            size: 0,
            errorStatus: UPLOAD_ERR_OK,
            clientFilename: $filename,
        );
    }
}
