<?php

namespace Tests\Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tests\Tempest\Console\TestCase;

class StaticProgressBarComponentTest extends TestCase
{
    public function test_progress_bar(): void
    {
        $this->console
            ->call(function (Console $console) {
                $output = $console->progressBar(
                    ['a', 'b', 'c'],
                    fn (string $input) => $input . $input,
                );

                $console->write(json_encode($output));
            })
            ->assertContains(<<<TXT
[==========>                    ] (1/3)
[====================>          ] (2/3)
[===============================] (3/3)
["aa","bb","cc"]
TXT,
            );
    }
}
