<?php

namespace Integration\Framework\Commands;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MetaViewComponentCommandTest extends FrameworkIntegrationTestCase
{
    public function test_show_meta_for_all_components(): void
    {
        $this->console
            ->call('meta:view-component')
            ->assertSuccess()
            ->assertSee('"x-with-header"')
            ->assertSee('"x-with-variable"')
            ->assertSee('        "slots": [
            "other",
            "default"
        ],
')
            ->assertNotSee('$this');
    }

    public function test_show_meta_for_view_component(): void
    {
        $this->console
            ->call('meta:view-component x-view-component-with-named-slots')
            ->assertSuccess()
            ->assertSee('x-view-component-with-named-slots.view.php')
            ->assertSee('"name": "x-view-component-with-named-slots",')
            ->assertSee(<<<'JSON'
                "variables": [
                    {
                        "type": "string",
                        "name": "$title",
                        "attributeName": "title",
                        "description": null
                    },
                    {
                        "type": "\\Tests\\Tempest\\Fixtures\\Modules\\Books\\Models\\Book",
                        "name": "$book",
                        "attributeName": "book",
                        "description": "Any kind of book will work"
                    },
                    {
                        "type": "string",
                        "name": "$dataFoo",
                        "attributeName": "data-foo",
                        "description": null
                    }
                ]
            JSON)
            ->assertSee(<<<'JSON'
                "slots": [
                    "default",
                    "foo",
                    "bar"
                ],
            JSON);
    }
}
