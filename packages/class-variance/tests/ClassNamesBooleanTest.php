<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Tempest\ClassVariance\tv;

final class ClassNamesBooleanTest extends TestCase
{
    #[Test]
    public function class_prop_with_boolean_value_ignored(): void
    {
        $component = tv(
            base: ['base' => ['component']],
            variants: [
                'variant' => [
                    'primary' => ['base' => 'bg-blue-500'],
                ],
            ],
            defaultVariants: ['variant' => 'primary'],
        );

        $this->assertSame('component bg-blue-500', $component(['class' => true], 'base'));
        $this->assertSame('component bg-blue-500', $component(['class' => false], 'base'));
        $this->assertSame('component bg-blue-500', $component(['className' => true], 'base'));
        $this->assertSame('component bg-blue-500', $component(['className' => false], 'base'));
    }

    #[Test]
    public function class_prop_normal_strings_still_work(): void
    {
        $component = tv(
            base: ['base' => ['component']],
            variants: [
                'variant' => [
                    'primary' => ['base' => 'bg-blue-500'],
                ],
            ],
            defaultVariants: ['variant' => 'primary'],
        );

        $this->assertSame(
            'component bg-blue-500 custom-class',
            $component(['class' => 'custom-class'], 'base'),
        );

        $this->assertSame(
            'component bg-blue-500 another-class',
            $component(['className' => 'another-class'], 'base'),
        );

        // Two unknown classes with the same prefix: tv() keeps both (unknown classes are never deduplicated)
        $this->assertSame(
            'component bg-blue-500 custom-1 custom-2',
            $component(['class' => 'custom-1 custom-2'], 'base'),
        );
    }

    #[Test]
    public function slot_based_class_with_boolean_in_array(): void
    {
        $component = tv(
            base: [
                'base' => ['component-base'],
                'label' => ['component-label'],
            ],
            variants: [
                'variant' => [
                    'primary' => [
                        'base' => ['bg-blue-500'],
                        'label' => ['text-white'],
                    ],
                ],
            ],
            defaultVariants: ['variant' => 'primary'],
        );

        // Boolean in slot class array ignored; only the matching slot's value applied
        $this->assertSame(
            'component-base bg-blue-500',
            $component(['class' => ['base' => true, 'label' => 'extra']], 'base'),
        );

        $this->assertSame(
            'component-label text-white extra',
            $component(['class' => ['base' => true, 'label' => 'extra']], 'label'),
        );
    }

    #[Test]
    public function empty_string_and_whitespace_still_filtered(): void
    {
        $component = tv(base: ['base' => ['component']]);

        $this->assertSame('component', $component(['class' => ''], 'base'));
        $this->assertSame('component', $component(['class' => '   '], 'base'));
        $this->assertSame('component', $component(['class' => false], 'base'));
    }
}
