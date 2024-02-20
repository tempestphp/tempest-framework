<?php

declare(strict_types=1);

namespace Tempest;

use Closure;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Exception;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;

final class Tempest
{
    private function __construct(
        private readonly Kernel $kernel,
        private readonly AppConfig $appConfig,
    ) {
    }

    public static function boot(string $root, Closure $createAppConfig): self
    {
        try {
            $dotenv = Dotenv::createUnsafeImmutable($root);
            $dotenv->load();
        } catch (InvalidPathException) {
            die("Missing .env file in {$root}" . PHP_EOL);
        }

        $appConfig = $createAppConfig();

        $kernel = new Kernel($root, $appConfig);

        return new self(
            kernel: $kernel,
            appConfig: $appConfig
        );
    }

    public function detectAutoloader(): self
    {
        $autoloaderPaths = [
            getcwd() . '/vendor/autoload.php',
            getcwd() . '/../autoload.php',
        ];

        $foundAutoloaderPath = null;

        foreach ($autoloaderPaths as $autoloaderPath) {
            if (file_exists($autoloaderPath)) {
                $foundAutoloaderPath = $autoloaderPath;

                break;
            }
        }

        if (! $foundAutoloaderPath) {
            throw new Exception("Could not find autoload.php");
        }

        require_once $autoloaderPath;

        $appPaths = [
            getcwd() . '/app/',
            getcwd() . '/src/',
        ];

        $foundAppPath = null;

        foreach ($appPaths as $appPath) {
            if (is_dir($appPath)) {
                $foundAppPath = $appPath;

                break;
            }
        }

        if (! $foundAppPath) {
            throw new Exception("Could not locate app directory.");
        }

        return $this;
    }

    public function console(): ConsoleApplication
    {
        return new ConsoleApplication(
            args: $_SERVER['argv'],
            container: $this->kernel->init(),
        );
    }

    public function http(): HttpApplication
    {
        return new HttpApplication(
            container: $this->kernel->init(),
        );
    }

    public function kernel(): Kernel
    {
        return $this->kernel;
    }
}
