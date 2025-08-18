<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ViewComponentAttributeMergeTest extends FrameworkIntegrationTestCase
{
    public function test_merges_plain_class_attribute(): void
    {
        $this->registerViewComponent('x-test-btn', '<button class="base-class">
            <x-slot />
        </button>');

        $html = $this->render('<x-test-btn class="custom-class">Click</x-test-btn>');

        $this->assertStringContainsString('class="base-class custom-class"', $html);
    }

    public function test_merges_expression_class_attribute(): void
    {
        $this->registerViewComponent('x-test-btn2', '<button class="base-class">
            <x-slot />
        </button>');

        $html = $this->render(
            '<x-test-btn2 :class="$customClass">Click</x-test-btn2>',
            customClass: 'dynamic-class',
        );

        $this->assertStringContainsString('class="base-class dynamic-class"', $html);
    }

    public function test_merges_both_plain_and_expression_class(): void
    {
        $this->registerViewComponent('x-test-btn3', '<button class="base-class">
            <x-slot />
        </button>');

        $html = $this->render(
            '<x-test-btn3 class="plain-class" :class="$dynamicClass">Click</x-test-btn3>',
            dynamicClass: 'dynamic-class',
        );

        $this->assertStringContainsString('class="base-class plain-class dynamic-class"', $html);
    }

    public function test_merges_plain_style_attribute(): void
    {
        $this->registerViewComponent('x-test-btn4', '<button style="color: blue;">
            <x-slot />
        </button>');

        $html = $this->render('<x-test-btn4 style="font-weight: bold;">Click</x-test-btn4>');

        $this->assertStringContainsString('style="color: blue; font-weight: bold;"', $html);
    }

    public function test_merges_expression_style_attribute(): void
    {
        $this->registerViewComponent('x-test-btn5', '<button style="color: blue;">
            <x-slot />
        </button>');

        $html = $this->render(
            '<x-test-btn5 :style="$customStyle">Click</x-test-btn5>',
            customStyle: 'font-weight: bold;',
        );

        $this->assertStringContainsString('style="color: blue; font-weight: bold;"', $html);
    }

    public function test_replaces_id_with_expression(): void
    {
        $this->registerViewComponent('x-test-btn6', '<button id="default-id">
            <x-slot />
        </button>');

        $html = $this->render(
            '<x-test-btn6 :id="$customId">Click</x-test-btn6>',
            customId: 'dynamic-id',
        );

        $this->assertStringContainsString('id="dynamic-id"', $html);
        $this->assertStringNotContainsString('id="default-id"', $html);
    }

    public function test_combines_component_and_usage_expression_classes(): void
    {
        $this->registerViewComponent('x-test-btn7', '<button class="base" :class="$isActive ? \'active\' : \'\'">
            <x-slot />
        </button>');

        $html = $this->render(
            '<x-test-btn7 :class="$isDanger ? \'danger\' : \'\'">Click</x-test-btn7>',
            isActive: true,
            isDanger: true,
        );

        $this->assertStringContainsString('class="base active danger"', $html);
    }

    public function test_complex_usage_scenario(): void
    {
        $this->registerViewComponent('x-act-btn', '<button class="bg-gray-100 px-2 py-1">
            <x-slot />
        </button>');

        $html = $this->render('<x-act-btn class="text-red-200">Click Me</x-act-btn>');

        $this->assertStringContainsString('class="bg-gray-100 px-2 py-1 text-red-200"', $html);
    }
}
