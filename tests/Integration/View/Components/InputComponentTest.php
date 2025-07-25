<?php

namespace Integration\View\Components;

use Tempest\Http\Session\Session;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Rules\Length;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class InputComponentTest extends FrameworkIntegrationTestCase
{
    public function test_simple_input(): void
    {
        $html = $this->render('<x-input name="name" />');

        $this->assertSnippetsMatch(
            expected: <<<'HTML'
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" type="text">
            </div>
            HTML,
            actual: $html,
        );
    }

    public function test_with_label(): void
    {
        $html = $this->render('<x-input name="name" label="Test" />');

        $this->assertStringContainsString('<label for="name">Test</label>', $html);
    }

    public function test_with_id(): void
    {
        $html = $this->render('<x-input name="name" id="test" />');

        $this->assertStringContainsString('<label for="test">', $html);
        $this->assertStringContainsString('id="test"', $html);
    }

    public function test_with_type(): void
    {
        $html = $this->render('<x-input name="name" type="email" />');

        $this->assertStringContainsString('type="email"', $html);
    }

    public function test_input_original(): void
    {
        $this->get(Session::class)->set(Session::ORIGINAL_VALUES, [
            'name' => 'original',
            'other' => 'other',
        ]);

        $html = $this->render('<x-input name="name" />');

        $this->assertStringContainsString('value="original"', $html);
    }

    public function test_textarea(): void
    {
        $html = $this->render('<x-input name="name" type="textarea" />');

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringNotContainsString('<input', $html);
    }

    public function test_textarea_original(): void
    {
        $this->get(Session::class)->set(Session::ORIGINAL_VALUES, [
            'name' => 'original',
            'other' => 'other',
        ]);

        $html = $this->render('<x-input name="name" type="textarea" />');

        $this->assertStringContainsString('>original</textarea>', $html);
    }

    public function test_error_message(): void
    {
        $failingRules = [
            'name' => [
                new IsString(),
                new Length(min: 5),
            ],
            'other' => [
                new IsInteger(),
            ]
        ];

        $this->get(Session::class)->set(Session::VALIDATION_ERRORS, $failingRules);

        $html = $this->render('<x-input name="name" />');

        $this->assertStringContainsString(new IsString()->message(), $html);
        $this->assertStringContainsString(new Length(min: 5)->message(), $html);
        $this->assertStringNotContainsString(new IsInteger()->message(), $html);
    }
}