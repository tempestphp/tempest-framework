<?php

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MetaViewComponentCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function show_meta_for_all_components(): void
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

    #[Test]
    public function show_meta_for_view_component(): void
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

    #[Test]
    public function show_variables_declared_in_one_line_docblock(): void
    {
        $this->console
            ->call('meta:view-component x-with-variable')
            ->assertSuccess()
            ->assertSee(<<<'JSON'
                "variables": [
                    {
                        "type": "string",
                        "name": "$variable",
                        "attributeName": "variable",
                        "description": null
                    }
                ]
            JSON);
    }

    #[Test]
    public function show_description_of_one_line_docblock_variable(): void
    {
        $this->console
            ->call('meta:view-component x-submit')
            ->assertSuccess()
            ->assertSee(<<<'JSON'
                "variables": [
                    {
                        "type": "null|string",
                        "name": "$label",
                        "attributeName": "label",
                        "description": "The submit button's label"
                    }
                ]
            JSON);
    }

    #[Test]
    public function ignore_one_line_docblock_typing_a_local_assignment(): void
    {
        $this->console
            ->call('meta:view-component x-input')
            ->assertSuccess()
            ->assertSee('"name": "$default",')
            ->assertNotSee('$formSession')
            ->assertNotSee('$validator');
    }
}
