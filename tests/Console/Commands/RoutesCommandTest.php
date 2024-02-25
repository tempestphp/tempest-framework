<?php

declare(strict_types=1);

use App\Modules\Posts\PostController;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('migrate command', function () {
    $output = $this->console('routes')->asText();

    $this->assertStringContainsString('/create-post', $output);
    $this->assertStringContainsString(PostController::class, $output);
});
