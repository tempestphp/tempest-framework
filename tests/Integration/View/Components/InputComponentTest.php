<?php

namespace Tests\Tempest\Integration\View\Components;

use Tempest\Http\Session\FormSession;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Validator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class InputComponentTest extends FrameworkIntegrationTestCase
{
    public function test_simple_input(): void
    {
        $html = $this->view->render('<x-input name="name" />');

        $this->assertStringContainsString('<label for="name">Name</label>', $html);
        $this->assertStringContainsString('<input type="text" name="name" id="name"', $html);
    }

    public function test_with_label(): void
    {
        $html = $this->view->render('<x-input name="name" label="Test" />');

        $this->assertStringContainsString('<label for="name">Test</label>', $html);
    }

    public function test_with_id(): void
    {
        $html = $this->view->render('<x-input name="name" id="test" />');

        $this->assertStringContainsString('<label for="test">', $html);
        $this->assertStringContainsString('id="test"', $html);
    }

    public function test_with_type(): void
    {
        $html = $this->view->render('<x-input name="name" type="email" />');

        $this->assertStringContainsString('type="email"', $html);
    }

    public function test_input_original(): void
    {
        $this->get(FormSession::class)->setOriginalValues([
            'name' => 'original',
            'other' => 'other',
        ]);

        $html = $this->view->render('<x-input name="name" />');

        $this->assertStringContainsString('value="original"', $html);
    }

    public function test_textarea(): void
    {
        $html = $this->view->render('<x-input name="name" type="textarea" />');

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringNotContainsString('<input', $html);
    }

    public function test_textarea_original(): void
    {
        $this->get(FormSession::class)->setOriginalValues([
            'name' => 'original',
            'other' => 'other',
        ]);

        $html = $this->view->render('<x-input name="name" type="textarea" />');

        $this->assertStringContainsString('>original</textarea>', $html);
    }

    public function test_error_message(): void
    {
        $failingRules = [
            'name' => [
                new FailingRule(new IsString()),
                new FailingRule(new HasLength(min: 5)),
            ],
            'other' => [
                new FailingRule(new IsInteger()),
            ],
        ];

        $this->get(FormSession::class)->setErrors($failingRules);

        $html = $this->view->render('<x-input name="name" />');

        $validator = $this->container->get(Validator::class);

        $this->assertStringContainsString($validator->getErrorMessage(new IsString()), $html);
        $this->assertStringContainsString($validator->getErrorMessage(new HasLength(min: 5)), $html);
        $this->assertStringNotContainsString($validator->getErrorMessage(new IsInteger()), $html);
    }
}
