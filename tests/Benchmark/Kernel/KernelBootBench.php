<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Kernel;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Core\FrameworkKernel;

final class KernelBootBench
{
    private string $root;

    public function __construct()
    {
        $this->root = dirname(__DIR__, 3);
    }

    #[Iterations(5)]
    #[Revs(10)]
    #[Warmup(2)]
    public function benchBoot(): void
    {
        FrameworkKernel::boot(root: $this->root);
    }
}
