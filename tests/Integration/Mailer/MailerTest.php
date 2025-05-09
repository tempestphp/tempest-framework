<?php

namespace Integration\Mailer;

use Tempest\Mailer\Mailer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MailerTest extends FrameworkIntegrationTestCase
{
    public function test_mailer(): void
    {
        $mailer = $this->container->get(Mailer::class);

        $user = (object) [
            'email' => 'brendt@stitcher.io',
            'name' => 'Brent Roose',
            'first_name' => 'Brent',
        ];

        $mailer->send(
            __DIR__ . '/test-mail.view.php',
            user: $user,
            files: [
                __DIR__ . '/Fixtures/attachment.txt',
                __DIR__ . '/test-mail.view.php',
            ],
        );
    }
}
