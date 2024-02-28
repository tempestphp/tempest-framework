<?php

declare(strict_types=1);

namespace Tempest\Testing\Console;

use PHPUnit\Framework\Assert;

final readonly class TestConsoleHelper
{
    public function __construct(
        private TestConsoleOutput $output,
    ) {
    }

    public function assertContains(string $text): self
    {
        Assert::assertStringContainsString(
            $text,
            $this->output->getTextWithoutFormatting(),
            sprintf('Failed to assert that console output included text: %s.', $text)
        );

        return $this;
    }

    public function assertContainsFormattedText(string $text): self
    {
        Assert::assertStringContainsString(
            $text,
            $this->output->getTextWithFormatting(),
            sprintf('Failed to assert that console output included formatted text: %s.', $text)
        );

        return $this;
    }

    public function assertContainsError(string $line): self
    {
        Assert::assertContainsEquals(
            $line,
            $this->output->getErrorLines(),
            sprintf('Failed to assert that console output included error: %s.', $line)
        );

        return $this;
    }

    public function assertContainsInfo(string $line): self
    {
        Assert::assertContainsEquals(
            $line,
            $this->output->getInfoLines(),
            sprintf('Failed to assert that console output included info: %s', $line)
        );

        return $this;
    }

    public function assertContainsSuccess(string $line): self
    {
        Assert::assertContainsEquals(
            $line,
            $this->output->getSuccessLines(),
            sprintf('Failed to assert that console output included success: %s', $line)
        );

        return $this;
    }
}
