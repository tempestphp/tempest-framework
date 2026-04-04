<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Tempest\ClassVariance\cv;
use function Tempest\ClassVariance\tv;

final class CvTest extends TestCase
{
    #[Test]
    public function tv_resolves_defaults_and_compound_variants(): void
    {
        $button = tv(
            base: ['font-semibold', 'border', 'rounded'],
            variants: [
                'variant' => [
                    'primary' => ['bg-blue-500', 'text-white', 'border-transparent', 'hover:bg-blue-600'],
                    'secondary' => ['bg-white', 'text-gray-800', 'border-gray-400', 'hover:bg-gray-100'],
                ],
                'size' => [
                    'small' => ['text-sm', 'py-1', 'px-2'],
                    'medium' => ['text-base', 'py-2', 'px-4'],
                ],
            ],
            compoundVariants: [
                [
                    'variant' => 'primary',
                    'size' => 'medium',
                    'class' => 'uppercase',
                ],
            ],
            defaultVariants: [
                'variant' => 'primary',
                'size' => 'medium',
            ],
        );

        $this->assertSame(
            'font-semibold border rounded bg-blue-500 text-white border-transparent hover:bg-blue-600 text-base py-2 px-4 uppercase',
            $button(),
        );

        $this->assertSame(
            'font-semibold border rounded bg-white text-gray-800 border-gray-400 hover:bg-gray-100 text-sm py-1 px-2',
            $button(['variant' => 'secondary', 'size' => 'small']),
        );
    }

    #[Test]
    public function class_prop_appended_and_takes_priority_over_classname(): void
    {
        $button = tv(
            base: ['font-semibold', 'border', 'rounded'],
            variants: [
                'variant' => [
                    'secondary' => ['bg-white', 'text-gray-800', 'border-gray-400', 'hover:bg-gray-100'],
                ],
                'size' => [
                    'small' => ['text-sm', 'py-1', 'px-2'],
                ],
            ],
            defaultVariants: ['variant' => 'secondary', 'size' => 'small'],
        );

        // When both class and className are provided, class takes priority (className ignored)
        $this->assertSame(
            'font-semibold border rounded bg-white text-gray-800 border-gray-400 hover:bg-gray-100 text-sm py-1 px-2 focus:ring-2',
            $button(['class' => 'focus:ring-2', 'className' => 'focus:ring-4', 'variant' => 'secondary', 'size' => 'small']),
        );

        // className used when class is absent
        $this->assertSame(
            'font-semibold border rounded bg-white text-gray-800 border-gray-400 hover:bg-gray-100 text-sm py-1 px-2 focus:ring-2',
            $button(['className' => 'focus:ring-2', 'variant' => 'secondary', 'size' => 'small']),
        );
    }

    #[Test]
    public function cv_separator_deduplication(): void
    {
        // cv() uses separator heuristic: classes sharing a prefix-before-'-' conflict.
        // Last class in the group wins — this is the intentional cv() behaviour.
        $component = cv(
            base: 'btn',
            variants: [
                'size' => [
                    'sm' => 'size-sm',
                    'lg' => 'size-lg',
                ],
                'intent' => [
                    'primary' => 'intent-primary',
                    'danger' => 'intent-danger',
                ],
            ],
            defaultVariants: ['size' => 'sm', 'intent' => 'primary'],
        );

        $this->assertSame('btn size-sm intent-primary', $component());
        $this->assertSame('btn size-lg intent-danger', $component(['size' => 'lg', 'intent' => 'danger']));

        // Passing class= with a class that shares prefix with a base class: last wins
        $this->assertSame('btn size-lg intent-primary', $component(['class' => 'size-lg']));
    }
}
