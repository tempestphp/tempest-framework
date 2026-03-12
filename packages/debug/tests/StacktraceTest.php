<?php

declare(strict_types=1);

namespace Tempest\Debug\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tempest\Debug\Stacktrace\CodeSnippet;
use Tempest\Debug\Stacktrace\Frame;
use Tempest\Debug\Stacktrace\Stacktrace;

final class StacktraceTest extends TestCase
{
    #[Test]
    public function creates_stacktrace_from_throwable(): void
    {
        $exception = $this->createException();
        $stacktrace = Stacktrace::fromThrowable($exception);

        $this->assertSame('Test exception', $stacktrace->message);
        $this->assertSame(RuntimeException::class, $stacktrace->exceptionClass);
        $this->assertNotEmpty($stacktrace->frames);
    }

    #[Test]
    public function exception_frame_includes_snippet_for_existing_file(): void
    {
        $exception = $this->createException();
        $stacktrace = Stacktrace::fromThrowable($exception);

        $exceptionFrame = $stacktrace->frames[0];

        $this->assertInstanceOf(CodeSnippet::class, $exceptionFrame->snippet);
        $this->assertNotEmpty($exceptionFrame->snippet->lines);
    }

    #[Test]
    public function stacktrace_frame_creates_from_array(): void
    {
        $frame = Frame::fromArray([
            'file' => __FILE__,
            'line' => 100,
            'class' => self::class,
            'function' => 'testMethod',
            'type' => '->',
        ]);

        $this->assertSame(__FILE__, $frame->absoluteFile);
        $this->assertSame(100, $frame->line);
        $this->assertSame(self::class, $frame->class);
        $this->assertSame('testMethod', $frame->function);
        $this->assertSame('->', $frame->type);
        $this->assertFalse($frame->isVendor);
    }

    #[Test]
    public function detects_vendor_files(): void
    {
        $vendor = Frame::fromArray([
            'file' => '/path/to/vendor/package/file.php',
            'line' => 10,
        ]);

        $this->assertTrue($vendor->isVendor);

        $app = Frame::fromArray([
            'file' => '/path/to/app/file.php',
            'line' => 10,
        ]);

        $this->assertFalse($app->isVendor);
    }

    #[Test]
    public function extracts_code_snippet_for_non_vendor_files(): void
    {
        $frame = Frame::fromArray([
            'file' => __FILE__,
            'line' => 50,
        ]);

        $this->assertInstanceOf(CodeSnippet::class, $frame->snippet);
        $this->assertNotEmpty($frame->snippet->lines);
        $this->assertSame(50, $frame->snippet->highlightedLine);
    }

    #[Test]
    public function no_snippet_for_vendor_files(): void
    {
        $frame = Frame::fromArray([
            'file' => '/path/to/vendor/package/file.php',
            'line' => 10,
        ]);

        $this->assertNull($frame->snippet);
    }

    #[Test]
    public function code_snippet_extracts_context_lines(): void
    {
        $frame = Frame::fromArray(
            frame: [
                'file' => __FILE__,
                'line' => 50,
            ],
            contextLines: 3,
        );

        $this->assertNotNull($frame->snippet);
        $this->assertGreaterThanOrEqual(47, $frame->snippet->getStartLine());
        $this->assertLessThanOrEqual(53, $frame->snippet->getEndLine());
    }

    #[Test]
    public function gets_relative_file_path(): void
    {
        $frame = Frame::fromArray(
            frame: [
                'file' => '/path/to/project/src/Controller.php',
                'line' => 10,
            ],
            rootPath: '/path/to/project',
        );

        $this->assertSame('/path/to/project/src/Controller.php', $frame->absoluteFile);
        $this->assertSame('src/Controller.php', $frame->relativeFile);
    }

    #[Test]
    public function gets_method_name(): void
    {
        $instance = Frame::fromArray([
            'file' => __FILE__,
            'line' => 10,
            'class' => 'MyClass',
            'function' => 'myMethod',
            'type' => '->',
        ]);

        $this->assertSame('MyClass->myMethod', $instance->getMethodName());

        $static = Frame::fromArray([
            'file' => __FILE__,
            'line' => 10,
            'class' => 'MyClass',
            'function' => 'staticMethod',
            'type' => '::',
        ]);

        $this->assertSame('MyClass::staticMethod', $static->getMethodName());

        $function = Frame::fromArray([
            'file' => __FILE__,
            'line' => 10,
            'function' => 'myFunction',
        ]);

        $this->assertSame('myFunction', $function->getMethodName());
    }

    #[Test]
    public function code_snippet_respects_file_boundaries(): void
    {
        $frame = Frame::fromArray(
            frame: [
                'file' => __FILE__,
                'line' => 2,
            ],
            contextLines: 10,
        );

        $this->assertNotNull($frame->snippet);
        $this->assertSame(1, $frame->snippet->getStartLine());
    }

    #[Test]
    public function detects_vendor_files_with_root_path(): void
    {
        $rootPath = '/path/to/project';

        $vendor = Frame::fromArray(
            frame: [
                'file' => '/different/path/vendor/package/file.php',
                'line' => 10,
            ],
            rootPath: $rootPath,
        );

        $this->assertTrue($vendor->isVendor);

        $app = Frame::fromArray(
            frame: [
                'file' => '/path/to/project/src/Controller.php',
                'line' => 10,
            ],
            rootPath: $rootPath,
        );

        $this->assertFalse($app->isVendor);
    }

    #[Test]
    public function stacktrace_uses_root_path_for_vendor_detection(): void
    {
        $exception = $this->createException();
        $rootPath = dirname(__DIR__, levels: 3); // Get project root

        $stacktrace = Stacktrace::fromThrowable($exception, rootPath: $rootPath);
        $frames = $stacktrace->applicationFrames;

        $this->assertNotEmpty($frames);

        foreach ($frames as $frame) {
            if (str_starts_with($frame->absoluteFile, $rootPath)) {
                $this->assertFalse($frame->isVendor);
            }
        }
    }

    private function createException(): RuntimeException
    {
        return new RuntimeException('Test exception');
    }
}
