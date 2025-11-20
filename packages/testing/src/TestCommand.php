<?php

namespace Tempest\Testing;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Testing\Exceptions\TestHasFailed;

final class TestCommand
{
    use HasConsole;

    public function __construct(
        private readonly TestsConfig $testConfig,
        private readonly Container $container,
    ) {}

    #[ConsoleCommand]
    public function __invoke(
        #[ConsoleArgument(description: 'Only run tests matching the given fuzzy filter')]
        ?string $filter = null,
        #[ConsoleArgument(description: 'Show all output, including succeeding tests')]
        bool $all = false,
    ): void {
        $successCount = 0;
        $failedCount = 0;

        foreach ($this->testConfig->tests as $test) {
            $testName = $test->getDeclaringClass()->getName() . ':' . $test->getName();

            if ($filter && ! str_contains($testName, $filter)) {
                if ($all) {
                    $this->info('skipped', $testName);
                }

                continue;
            }

            try {
                $this->container->invoke($test);

                if ($all) {
                    $this->success('check', $testName);
                }

                $successCount += 1;
            } catch (TestHasFailed $testHasFailed) {
                $message = sprintf(
                    <<<'TXT'
                    %s
                    %s
                    TXT,
                    $testHasFailed->reason,
                    $testHasFailed->location
                );

                $this->error($message, $testName);

                $failedCount += 1;
            }
        }

        $this->writeln();

        if ($successCount) {
            $this->success("{$successCount} successful tests");
        }

        if ($failedCount) {
            $this->error("{$failedCount} failed tests");
        }

        if ($failedCount === 0 && $successCount === 0) {
            $this->info("No tests were run");
        }
    }
}