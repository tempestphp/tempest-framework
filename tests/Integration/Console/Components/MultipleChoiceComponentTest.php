<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Components\Interactive\MultipleChoiceComponent;
use Tempest\Console\Console;
use Tempest\Console\Terminal\Terminal;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MultipleChoiceComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function without_filtering(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new MultipleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman']);

                $component->input(' ');
                $component->down();
                $component->input(' ');

                $this->assertSame(['Brent', 'Paul'], $component->enter());
            });
    }

    #[Test]
    public function with_filtering(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new MultipleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman']);

                $component->input('/');
                $component->input('P');
                $component->input('a');
                $component->stopFiltering();
                $component->input(' ');

                $this->assertSame(['Paul'], $component->enter());
            });
    }

    #[Test]
    public function list_options_do_not_retain_keys(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new MultipleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman']);

                $component->down();
                $component->input(' ');
                $component->down();
                $component->input(' ');

                $this->assertSame(['Paul', 'Aidan'], $component->enter());
            });
    }

    #[Test]
    public function associative_options_retain_keys(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new MultipleChoiceComponent(label: 'Enter a name', options: [
                    'brent' => 'Brent',
                    'paul' => 'Paul',
                    'aidan' => 'Aidan',
                    'roman' => 'Roman',
                ]);

                $component->down();
                $component->input(' ');
                $component->down();
                $component->input(' ');

                $this->assertSame(['paul' => 'Paul', 'aidan' => 'Aidan'], $component->enter());
            });
    }

    #[Test]
    public function searching_does_not_clear_active(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new MultipleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman']);

                $component->down();
                $component->down();
                $component->input(' ');
                $component->input('/');
                $component->input('a');

                $this->assertSame(['Aidan'], $component->enter());
            });
    }

    #[Test]
    public function multiple_supports_default_value(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $terminal = new Terminal($console);
                $component = new MultipleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman'], default: ['Aidan']);

                $this->assertStringContainsString('Enter a name', $component->render($terminal));
                $component->enter();

                $this->assertSame(['Aidan'], $component->enter());
            });
    }
}
