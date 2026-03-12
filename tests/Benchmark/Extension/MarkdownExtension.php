<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Extension;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extension\ReportExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MarkdownExtension implements ExtensionInterface
{
    public function configure(OptionsResolver $resolver): void
    {
    }

    public function load(Container $container): void
    {
        $container->register(
            MarkdownRenderer::class,
            function (Container $container) {
                return new MarkdownRenderer(
                    $container->get(ConsoleExtension::SERVICE_OUTPUT_STD),
                    $container->get(ExpressionExtension::SERVICE_PLAIN_PRINTER),
                );
            },
            [
                ReportExtension::TAG_REPORT_RENDERER => [
                    'name' => 'markdown',
                ],
            ],
        );
    }
}
