<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Tempest\ClassVariance\tv;

final class CompoundVariantsAdvancedTest extends TestCase
{
    #[Test]
    public function compound_variant_with_array_conditions(): void
    {
        $component = tv(
            base: [
                'base' => ['component'],
                'content' => ['content-base'],
                'leading' => ['leading-base'],
            ],
            variants: [
                'variant' => [
                    'solid' => ['base' => ['bg-solid']],
                    'outline' => ['base' => ['border-outline']],
                    'soft' => ['base' => ['bg-soft']],
                    'subtle' => ['base' => ['bg-subtle']],
                ],
                'compact' => [
                    'true' => ['content' => ['p-2']],
                    'false' => ['content' => ['p-4']],
                ],
            ],
            compoundVariants: [
                [
                    'variant' => ['solid', 'outline', 'soft', 'subtle'],
                    'compact' => 'false',
                    'class' => [
                        'content' => 'px-4 py-3 rounded-lg min-h-12',
                        'leading' => 'mt-2',
                    ],
                ],
            ],
            defaultVariants: [
                'variant' => 'solid',
                'compact' => 'false',
            ],
        );

        // Default: variant=solid, compact=false — compound matches
        $this->assertSame(
            'content-base p-4 px-4 py-3 rounded-lg min-h-12',
            $component(slot: 'content'),
        );

        $this->assertSame(
            'leading-base mt-2',
            $component(slot: 'leading'),
        );

        // variant=outline, compact=false — compound still matches
        $this->assertSame(
            'content-base p-4 px-4 py-3 rounded-lg min-h-12',
            $component(['variant' => 'outline', 'compact' => 'false'], 'content'),
        );

        // variant=solid, compact=true — compound does NOT match
        $this->assertSame(
            'content-base p-2',
            $component(['variant' => 'solid', 'compact' => 'true'], 'content'),
        );
    }

    #[Test]
    public function compound_variant_with_multiple_array_conditions(): void
    {
        $component = tv(
            base: ['base' => ['component']],
            variants: [
                'color' => [
                    'neutral' => ['base' => 'text-neutral'],
                    'primary' => ['base' => 'text-primary'],
                ],
                'variant' => [
                    'outline' => ['base' => 'border'],
                    'subtle' => ['base' => 'bg-subtle'],
                ],
                'multiple' => [
                    'true' => ['base' => 'multiple'],
                    'false' => ['base' => 'single'],
                ],
            ],
            compoundVariants: [
                [
                    'color' => 'neutral',
                    'multiple' => 'true',
                    'variant' => ['outline', 'subtle'],
                    'class' => ['base' => 'has-focus-visible:ring-2 has-focus-visible:ring-inverted'],
                ],
            ],
            defaultVariants: [
                'color' => 'neutral',
                'variant' => 'outline',
                'multiple' => 'false',
            ],
        );

        // Default: multiple=false — compound does NOT match
        $this->assertSame(
            'component text-neutral border single',
            $component(slot: 'base'),
        );

        // color=neutral, variant=outline, multiple=true — compound matches
        $this->assertSame(
            'component text-neutral border multiple has-focus-visible:ring-2 has-focus-visible:ring-inverted',
            $component(['color' => 'neutral', 'variant' => 'outline', 'multiple' => 'true'], 'base'),
        );

        // color=neutral, variant=subtle, multiple=true — compound matches
        $this->assertSame(
            'component text-neutral bg-subtle multiple has-focus-visible:ring-2 has-focus-visible:ring-inverted',
            $component(['color' => 'neutral', 'variant' => 'subtle', 'multiple' => 'true'], 'base'),
        );

        // color=primary — compound does NOT match
        $this->assertSame(
            'component text-primary border multiple',
            $component(['color' => 'primary', 'variant' => 'outline', 'multiple' => 'true'], 'base'),
        );
    }
}
