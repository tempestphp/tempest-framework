<?php

namespace Tempest\Core;

use Tempest\Support\Regex;

final class EnvironmentInsightsProvider implements InsightsProvider
{
    public string $name = 'Environment';

    public function __construct(
        private readonly AppConfig $appConfig,
    ) {}

    public function getInsights(): array
    {
        return [
            'Tempest version' => Kernel::VERSION,
            'PHP version' => PHP_VERSION,
            'Composer version' => $this->getComposerVersion(),
            'Operating system' => $this->getOperatingSystem(),
            'Environment' => $this->appConfig->environment->value,
            'Application URL' => $this->appConfig->baseUri ?: new Insight('Not set', Insight::ERROR),
        ];
    }

    private function getComposerVersion(): Insight|string
    {
        if (! function_exists('shell_exec')) {
            return 'shell_exec disabled';
        }

        $output = shell_exec('composer --version --no-ansi 2>&1');

        if (! $output) {
            return new Insight('Not installed', Insight::ERROR);
        }

        return \Tempest\Support\Regex\get_match(
            subject: $output,
            pattern: "/Composer version (?<version>\S+)/",
            match: 'version',
            default: new Insight('Unknown', Insight::ERROR),
        );
    }

    private function getOperatingSystem(): string
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            if ($version = shell_exec('sw_vers -productVersion')) {
                return "macOS {$version}";
            }

            return 'macOS ' . php_uname('r');
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $version = php_uname('r');

            return match (substr($version, 0, strcspn($version, ' '))) {
                '5.1' => 'Windows XP',
                '5.2' => 'Windows XP',
                '6.0' => 'Windows Vista',
                '6.1' => 'Windows 7',
                '6.2' => 'Windows 8',
                '6.3' => 'Windows 8.1',
                '10.0' => 'Windows 10',
                default => "Windows {$version}",
            };
        }

        if (PHP_OS_FAMILY === 'Linux') {
            $version = php_uname('r');

            if (function_exists('shell_exec') && ($output = shell_exec('lsb_release -a 2>/dev/null'))) {
                $version = Regex\get_match($output, "/Description:\s+(?<version>.*)/", match: 'version', default: php_uname('r'));
            }

            return "Linux {$version}";
        }

        return PHP_OS_FAMILY;
    }
}
