<?php

namespace Tempest\View\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewConfig;

final class FallthroughAttributesTest extends TestCase
{
    private function strip(string $html): string
    {
        return str_replace([' ', PHP_EOL], '', $html);
    }

    private function makeRenderer(string ...$fixtures): TempestViewRenderer
    {
        $config = new ViewConfig();

        foreach ($fixtures as $fixture) {
            $config->addViewComponents(__DIR__ . '/Fixtures/' . $fixture);
        }

        return TempestViewRenderer::make(viewConfig: $config);
    }

    // Single attribute: class

    #[Test]
    public function class_falls_through_when_not_defined_on_root(): void
    {
        $renderer = $this->makeRenderer('x-ft-none.view.php');

        $html = $renderer->render('<x-ft-none class="from-parent" />');

        $this->assertEquals(
            $this->strip('<div class="from-parent"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function class_is_blocked_when_defined_on_root(): void
    {
        $renderer = $this->makeRenderer('x-ft-class.view.php');

        $html = $renderer->render('<x-ft-class class="from-parent" />');

        // Root declares class="base-class"; parent's class backs off entirely.
        $this->assertEquals(
            $this->strip('<div class="base-class"></div>'),
            $this->strip($html),
        );
    }

    // Single attribute: style

    #[Test]
    public function style_falls_through_when_not_defined_on_root(): void
    {
        $renderer = $this->makeRenderer('x-ft-none.view.php');

        $html = $renderer->render('<x-ft-none style="color:red;" />');

        $this->assertEquals(
            $this->strip('<div style="color:red;"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function style_is_blocked_when_defined_on_root(): void
    {
        $renderer = $this->makeRenderer('x-ft-style.view.php');

        $html = $renderer->render('<x-ft-style style="color:red;" />');

        // Root declares style="font-weight:bold;"; parent's style backs off.
        $this->assertEquals(
            $this->strip('<div style="font-weight:bold;"></div>'),
            $this->strip($html),
        );
    }

    // Single attribute: id

    #[Test]
    public function id_falls_through_when_not_defined_on_root(): void
    {
        $renderer = $this->makeRenderer('x-ft-none.view.php');

        $html = $renderer->render('<x-ft-none id="from-parent" />');

        $this->assertEquals(
            $this->strip('<div id="from-parent"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function id_is_blocked_when_defined_on_root(): void
    {
        $renderer = $this->makeRenderer('x-ft-id.view.php');

        $html = $renderer->render('<x-ft-id id="from-parent" />');

        // Root declares id="base-id"; parent's id backs off.
        $this->assertEquals(
            $this->strip('<div id="base-id"></div>'),
            $this->strip($html),
        );
    }

    // Two-of-three: one blocked, one falls through
    //
    // Each test passes two of the three fallthrough attributes from the parent.
    // The component defines one of those two on its root, so only the other falls through.

    #[Test]
    public function style_is_blocked_and_id_falls_through(): void
    {
        // x-ft-style root: style="font-weight:bold;" — blocks style, id is absent → falls through
        $renderer = $this->makeRenderer('x-ft-style.view.php');

        $html = $renderer->render('<x-ft-style id="from-parent" style="color:red;" />');

        $this->assertEquals(
            $this->strip('<div style="font-weight:bold;" id="from-parent"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function class_is_blocked_and_style_falls_through(): void
    {
        // x-ft-class root: class="base-class" — blocks class, style is absent → falls through
        $renderer = $this->makeRenderer('x-ft-class.view.php');

        $html = $renderer->render('<x-ft-class class="from-parent" style="color:red;" />');

        $this->assertEquals(
            $this->strip('<div class="base-class" style="color:red;"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function id_is_blocked_and_class_falls_through(): void
    {
        // x-ft-id root: id="base-id" — blocks id, class is absent → falls through.
        // Attribute order: root attrs come first (id), then appended fallthrough (class).
        $renderer = $this->makeRenderer('x-ft-id.view.php');

        $html = $renderer->render('<x-ft-id class="from-parent" id="from-parent" />');

        $this->assertEquals(
            $this->strip('<div id="base-id" class="from-parent"></div>'),
            $this->strip($html),
        );
    }

    // All three passed at once: varying root definitions

    #[Test]
    public function all_three_fall_through_when_none_defined_on_root(): void
    {
        $renderer = $this->makeRenderer('x-ft-none.view.php');

        $html = $renderer->render('<x-ft-none class="p-class" style="color:red;" id="p-id" />');

        $this->assertEquals(
            $this->strip('<div class="p-class" style="color:red;" id="p-id"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function class_blocked_when_all_three_passed(): void
    {
        // x-ft-class root: class="base-class" — class blocked, style and id fall through
        $renderer = $this->makeRenderer('x-ft-class.view.php');

        $html = $renderer->render('<x-ft-class class="p-class" style="color:red;" id="p-id" />');

        $this->assertEquals(
            $this->strip('<div class="base-class" style="color:red;" id="p-id"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function class_and_style_blocked_when_all_three_passed(): void
    {
        // x-ft-class-style root: class and style both defined — both blocked, id falls through
        $renderer = $this->makeRenderer('x-ft-class-style.view.php');

        $html = $renderer->render('<x-ft-class-style class="p-class" style="color:red;" id="p-id" />');

        $this->assertEquals(
            $this->strip('<div class="base-class" style="font-weight:bold;" id="p-id"></div>'),
            $this->strip($html),
        );
    }

    #[Test]
    public function all_three_blocked_when_all_defined_on_root(): void
    {
        // x-ft-all root: class, style, and id all defined — nothing from parent applied
        $renderer = $this->makeRenderer('x-ft-all.view.php');

        $html = $renderer->render('<x-ft-all class="p-class" style="color:red;" id="p-id" />');

        $this->assertEquals(
            $this->strip('<div class="base-class" style="font-weight:bold;" id="base-id"></div>'),
            $this->strip($html),
        );
    }

    // :apply
    //
    // :apply="$attributes" inside the component template opts out of auto-fallthrough
    // and spreads the full $attributes ImmutableArray onto the targeted element.
    // This includes attributes that are not part of the auto-fallthrough set (width, height).

    #[Test]
    public function apply_spreads_all_attributes_including_non_fallthrough_attrs(): void
    {
        // x-ft-apply template: <div :apply="$attributes"></div>
        // All five parent attributes are in $attributes and spread onto the div.
        $renderer = $this->makeRenderer('x-ft-apply.view.php');

        $html = $renderer->render(
            '<x-ft-apply id="test-id" class="test-class" style="color:red;" width="1em" height="1em" />',
        );

        $this->assertEquals(
            $this->strip('<div id="test-id" class="test-class" style="color:red;" width="1em" height="1em"></div>'),
            $this->strip($html),
        );
    }
}
