<?php

declare(strict_types=1);

namespace Tempest;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Exception;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;

final readonly class Tempest
{
    private function __construct(
        private Kernel $kernel,
        private AppConfig $appConfig,
        private string $projectRoot,
    ) {
    }

    public static function setupEnv(string $dir): void
    {
        try {
            $dotenv = Dotenv::createUnsafeImmutable($dir);
            $dotenv->load();
        } catch (InvalidPathException) {
            die("Missing .env file in {$dir}" . PHP_EOL);
        }
    }

    public static function boot(AppConfig $appConfig): self
    {
        $projectRoot = $appConfig->appPath . '/..';

        $kernel = new Kernel($appConfig);

        return new self($kernel, $appConfig, $projectRoot);
    }

    public function detectAutoloader(): self
    {
        $autoloaderPaths = [
            $this->projectRoot . '/vendor/autoload.php',
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
            $this->projectRoot . '/app/',
            getcwd() . '/app/',
            $this->projectRoot . '/src/',
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
