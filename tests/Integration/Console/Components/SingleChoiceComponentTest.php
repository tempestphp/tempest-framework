<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Components\Interactive\SingleChoiceComponent;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SingleChoiceComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function without_filtering(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new SingleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman']);

                $this->assertSame('Brent', $component->enter());
                $component->down();
                $this->assertSame('Paul', $component->enter());
            });
    }

    #[Test]
    public function with_filtering(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new SingleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman']);

                $component->input('/');
                $component->input('P');
                $component->input('a');
                $component->stopFiltering();

                $this->assertSame('Paul', $component->enter());
            });
    }

    #[Test]
    public function associative_options_returns_keys(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new SingleChoiceComponent(label: 'Enter a name', options: [
                    'brent' => 'Brent',
                    'paul' => 'Paul',
                    'aidan' => 'Aidan',
                    'roman' => 'Roman',
                ]);

                $component->down();
                $this->assertSame('paul', $component->enter());

                $component->down();
                $this->assertSame('aidan', $component->enter());
            });
    }

    #[Test]
    public function searching_does_not_clear_active(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new SingleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman']);

                $component->down();
                $component->down();
                $component->input(' ');
                $component->input('/');
                $component->input('a');

                $this->assertSame('Aidan', $component->enter());
            });
    }

    #[Test]
    public function default(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (): void {
                $component = new SingleChoiceComponent(label: 'Enter a name', options: ['Brent', 'Paul', 'Aidan', 'Roman'], default: 'Paul');

                $component->tab();

                $this->assertSame('Paul', $component->enter());
            });
    }
}
