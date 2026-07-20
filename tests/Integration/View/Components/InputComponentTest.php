<?php

namespace Tests\Tempest\Integration\View\Components;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Session\FormSession;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Validator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class InputComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function simple_input(): void
    {
        $html = $this->view->render('<x-input name="name" />');

        $this->assertStringContainsString('<label for="name">Name</label>', $html);
        $this->assertStringContainsString('<input type="text" name="name" id="name"', $html);
    }

    #[Test]
    public function with_label(): void
    {
        $html = $this->view->render('<x-input name="name" label="Test" />');

        $this->assertStringContainsString('<label for="name">Test</label>', $html);
    }

    #[Test]
    public function with_id(): void
    {
        $html = $this->view->render('<x-input name="name" id="test" />');

        $this->assertStringContainsString('<label for="test">', $html);
        $this->assertStringContainsString('id="test"', $html);
    }

    #[Test]
    public function with_type(): void
    {
        $html = $this->view->render('<x-input name="name" type="email" />');

        $this->assertStringContainsString('type="email"', $html);
    }

    #[Test]
    public function input_original(): void
    {
        $this->get(FormSession::class)->setOriginalValues([
            'name' => 'original',
            'other' => 'other',
        ]);

        $html = $this->view->render('<x-input name="name" />');

        $this->assertStringContainsString('value="original"', $html);
    }

    #[Test]
    public function textarea(): void
    {
        $html = $this->view->render('<x-input name="name" type="textarea" />');

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringNotContainsString('<input', $html);
    }

    #[Test]
    public function textarea_original(): void
    {
        $this->get(FormSession::class)->setOriginalValues([
            'name' => 'original',
            'other' => 'other',
        ]);

        $html = $this->view->render('<x-input name="name" type="textarea" />');

        $this->assertStringContainsString('>original</textarea>', $html);
    }

    #[Test]
    public function error_message(): void
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
