<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Tempest\ClassVariance\cv;
use function Tempest\ClassVariance\tv;

final class CvWithSlotTest extends TestCase
{
    #[Test]
    public function tv_resolves_slots_with_variants_and_compound_variants(): void
    {
        $button = tv(
            base: [
                'base' => ['font-semibold', 'border', 'rounded'],
                'label' => [''],
            ],
            variants: [
                'color' => [
                    'primary' => [
                        'base' => ['bg-blue-500', 'border-transparent', 'hover:bg-blue-600'],
                        'label' => ['text-white'],
                    ],
                    'secondary' => [
                        'base' => ['bg-white', 'border-gray-400', 'hover:bg-gray-100'],
                        'label' => ['text-black'],
                    ],
                ],
                'size' => [
                    'small' => [
                        'base' => ['py-1', 'px-2'],
                        'label' => ['text-sm'],
                    ],
                    'medium' => [
                        'base' => ['py-2', 'px-4'],
                        'label' => ['text-base'],
                    ],
                ],
            ],
            compoundVariants: [
                [
                    'color' => 'primary',
                    'size' => 'medium',
                    'class' => ['label' => 'uppercase'],
                ],
            ],
            defaultVariants: [
                'color' => 'primary',
                'size' => 'medium',
            ],
        );

        $this->assertSame(
            'font-semibold border rounded bg-blue-500 border-transparent hover:bg-blue-600 py-2 px-4',
            $button(slot: 'base'),
        );

        $this->assertSame(
            'text-white text-base uppercase',
            $button(slot: 'label'),
        );

        $this->assertSame(
            'font-semibold border rounded bg-white border-gray-400 hover:bg-gray-100 py-1 px-2',
            $button(props: ['color' => 'secondary', 'size' => 'small'], slot: 'base'),
        );

        $this->assertSame(
            'text-black text-sm',
            $button(props: ['color' => 'secondary', 'size' => 'small'], slot: 'label'),
        );

        // class takes priority over className for the requested slot
        $this->assertSame(
            'font-semibold border rounded bg-white border-gray-400 hover:bg-gray-100 py-1 px-2 focus:ring-2',
            $button(props: ['class' => 'focus:ring-2', 'className' => 'focus:ring-4', 'color' => 'secondary', 'size' => 'small'], slot: 'base'),
        );

        $this->assertSame(
            'font-semibold border rounded bg-white border-gray-400 hover:bg-gray-100 py-1 px-2 focus:ring-2',
            $button(props: ['className' => 'focus:ring-2', 'color' => 'secondary', 'size' => 'small'], slot: 'base'),
        );

        // Unknown slot returns empty string
        $this->assertSame('', $button(slot: 'fooBarBaz'));
    }

    #[Test]
    public function implicit_slot_inference_for_single_slot_base(): void
    {
        $component = tv(base: ['base' => 'rounded bg-white']);

        // No slot argument — single slot inferred automatically
        $this->assertSame('rounded bg-white', $component());
    }

    #[Test]
    public function plain_string_base_targets_base_slot(): void
    {
        $component = tv(
            base: 'font-bold text-sm',
            variants: ['size' => ['lg' => 'text-lg']],
        );

        // tv() is Tailwind-aware: text-sm and text-lg conflict (both font-size group), text-lg wins
        $this->assertSame('font-bold text-lg', $component(['size' => 'lg'], 'base'));
        $this->assertSame('font-bold text-lg', $component(['size' => 'lg']));
        // Non-base slot returns empty for plain-string base
        $this->assertSame('', $component(['size' => 'lg'], 'label'));
    }

    #[Test]
    public function multi_slot_base_without_slot_throws(): void
    {
        $component = cv(base: ['base' => 'a', 'label' => 'b']);

        $this->expectException(InvalidArgumentException::class);
        $component(); // no slot — ambiguous
    }

    #[Test]
    public function plain_string_variant_value_implicitly_targets_base_slot(): void
    {
        $component = tv(
            base: [
                'base' => 'component',
                'label' => 'label-base',
            ],
            variants: [
                'size' => [
                    // plain string — implicit base
                    'sm' => 'text-sm',
                    // explicit slot map
                    'lg' => ['base' => 'text-lg', 'label' => 'text-lg-label'],
                ],
            ],
        );

        // Plain 'sm' string only affects base slot
        $this->assertSame('component text-sm', $component(['size' => 'sm'], 'base'));
        $this->assertSame('label-base', $component(['size' => 'sm'], 'label'));

        // Explicit slot map affects both slots
        $this->assertSame('component text-lg', $component(['size' => 'lg'], 'base'));
        $this->assertSame('label-base text-lg-label', $component(['size' => 'lg'], 'label'));
    }

    #[Test]
    public function cv_plain_string_base_targets_base_slot(): void
    {
        // cv() with non-Tailwind class names — separator heuristic only
        $component = cv(
            base: 'btn size-base',
            variants: ['size' => ['lg' => 'size-lg']],
        );

        // size-base and size-lg share 'size' prefix → size-lg replaces size-base
        $this->assertSame('btn size-lg', $component(['size' => 'lg'], 'base'));
        // Non-base slot returns empty for plain-string base
        $this->assertSame('', $component(['size' => 'lg'], 'label'));
    }
}
