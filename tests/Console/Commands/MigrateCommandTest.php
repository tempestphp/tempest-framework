<?php

declare(strict_types=1);

use Tests\Tempest\TestCase;

uses(TestCase::class);

test('migrate command', function () {
    $output = $this->console('migrate')->asText();

    $this->assertStringContainsString('create_migrations_table', $output);
});
