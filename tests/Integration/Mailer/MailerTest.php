<?php

namespace Integration\Mailer;

use Tempest\Mailer\Mailer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MailerTest extends FrameworkIntegrationTestCase
{
    public function test_mailer(): void
    {
        $mailer = $this->container->get(Mailer::class);

        $mailer->send(
            __DIR__ . '/test-mail.view.php',
            from: 'sender@tempestphp.com',
            to: 'brendt@stitcher.io',
            subject: 'Hello there',
            name: 'Brent',
        );
    }
}