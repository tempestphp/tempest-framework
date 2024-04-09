<?php

declare(strict_types=1);

namespace Tempest\Console\Testing\Console;

use PHPUnit\Framework\Assert;

final readonly class TestConsoleHelper
{
    public function __construct(
        private TestConsoleOutput $output,
    ) {
    }

    public function print(): self
    {
        echo $this->output->getTextWithoutFormatting();

        return $this;
    }

    public function printFormatted(): self
    {
        echo $this->output->getTextWithFormatting();

        return $this;
    }

    public function assertContains(string $text): self
    {
        Assert::assertStringContainsString(
            $text,
            $this->output->getTextWithoutFormatting(),
            sprintf(
                'Failed to assert that console output included text: %s. These lines were printed: %s',
                $text,
                PHP_EOL.PHP_EOL . $this->output->getTextWithoutFormatting() . PHP_EOL,
            ),
        );

        return $this;
    }

    public function assertDoesNotContain(string $text): self
    {
        Assert::assertStringNotContainsString(
            $text,
            $this->output->getTextWithoutFormatting(),
            sprintf(
                'Failed to assert that console output did not include text: %s. These lines were printed: %s',
                $text,
                PHP_EOL.PHP_EOL . $this->output->getTextWithoutFormatting() . PHP_EOL,
            ),
        );

        return $this;
    }

    public function assertContainsFormattedText(string $text): self
    {
        Assert::assertStringContainsString(
            $text,
            $this->output->getTextWithFormatting(),
            sprintf(
                'Failed to assert that console output included formatted text: %s. These lines were printed: %s',
                $text,
                PHP_EOL . $this->output->getTextWithFormatting(),
            ),
        );

        return $this;
    }

    public function assertContainsError(string $line): self
    {
        Assert::assertContainsEquals(
            $line,
            $this->output->getErrorLines(),
            sprintf(
                'Failed to assert that console output included error: %s. These lines were printed: %s',
                $line,
                PHP_EOL . implode(PHP_EOL, $this->output->getErrorLines()),
            ),
        );

        return $this;
    }

    public function assertContainsInfo(string $line): self
    {
        Assert::assertContainsEquals(
            $line,
            $this->output->getInfoLines(),
            sprintf(
                'Failed to assert that console output included info: %s. These lines were printed: %s',
                $line,
                PHP_EOL . implode(PHP_EOL, $this->output->getInfoLines()),
            ),
        );

        return $this;
    }

    public function assertContainsSuccess(string $line): self
    {
        Assert::assertContainsEquals(
            $line,
            $this->output->getSuccessLines(),
            sprintf(
                'Failed to assert that console output included success message: %s. These lines were printed: %s',
                $line,
                PHP_EOL . implode(PHP_EOL, $this->output->getSuccessLines()),
            ),
        );

        return $this;
    }
}
