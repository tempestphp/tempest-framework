<?php

declare(strict_types=1);

namespace Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\ExitCode;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

class UpgradeCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function no_upgrades_for_v1(): void
    {
        $this->console
            ->call('upgrade:tempest', ['version' => 'v1.0.0', 'dry-run' => true])
            ->assertContains(' No rectors found for this version')
            ->assertExitCode(ExitCode::SUCCESS);
    }

    #[Test]
    public function upgrade_to_v2(): void
    {
        $this->console
            ->call('upgrade:tempest', ['version' => 'v2.0.0', 'dry-run' => true])
            ->assertContains('vendor/bin/rector --no-ansi --no-progress-bar --no-diffs --dry-run --only="Rector\Renaming\Rector\Name\RenameClassRector"')
            ->assertContains('[OK] Rector is done!')
            ->dump()
            ->assertExitCode(ExitCode::SUCCESS);
    }
}
