<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use PHPUnit\Framework\Assert;

final readonly class TestConsoleHelper
{
    public function __construct(
        private TestConsoleOutput $output,
        private TestConsoleComponentRenderer $componentRenderer,
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
                PHP_EOL . PHP_EOL . $this->output->getTextWithoutFormatting() . PHP_EOL,
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
                PHP_EOL . PHP_EOL . $this->output->getTextWithoutFormatting() . PHP_EOL,
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

    public function write(string $text): self
    {
        $this->componentRenderer->write($text);

        return $this->renderCurrentComponent();
    }

    public function up(): self
    {
        $this->componentRenderer->up();

        return $this->renderCurrentComponent();
    }

    public function down(): self
    {
        $this->componentRenderer->down();

        return $this->renderCurrentComponent();
    }

    public function left(): self
    {
        $this->componentRenderer->left();

        return $this->renderCurrentComponent();
    }

    public function right(): self
    {
        $this->componentRenderer->right();

        return $this->renderCurrentComponent();
    }

    public function enter(): self
    {
        $this->componentRenderer->enter();

        return $this->renderCurrentComponent();
    }

    public function backspace(): self
    {
        $this->componentRenderer->backspace();

        return $this->renderCurrentComponent();
    }

    public function delete(): self
    {
        $this->componentRenderer->delete();

        return $this->renderCurrentComponent();
    }

    public function space(): self
    {
        $this->componentRenderer->space();

        return $this->renderCurrentComponent();
    }

    private function renderCurrentComponent(): self
    {
        $this->output->write($this->componentRenderer->renderCurrentComponent());

        return $this;
    }
}
