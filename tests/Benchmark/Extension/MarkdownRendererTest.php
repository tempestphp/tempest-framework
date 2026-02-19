<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Extension;

use PhpBench\Expression\Printer\BareValuePrinter;
use PhpBench\Registry\Config;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Builder\TableBuilder;
use PhpBench\Report\Model\Reports;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class MarkdownRendererTest extends TestCase
{
    #[Test]
    public function it_renders_a_compact_aggregate_report_table(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());

        $table = TableBuilder::create()
            ->withTitle('Benchmark Results')
            ->addRowArray([
                'benchmark' => 'ContainerBench',
                'subject' => 'benchAutowireSimple',
                'set' => '',
                'revs' => 1000,
                'its' => 5,
                'mem_peak' => '3.952mb 0.00%',
                'mode' => '4.187μs +0.17%',
                'rstdev' => '±2.05% +108.06%',
            ])
            ->build();

        $reports = Reports::fromReport(ReportBuilder::create()->addObject($table)->build());

        $renderer->render($reports, new Config('markdown', ['file' => null]));

        $this->assertSame(<<<'MARKDOWN'
        ## Benchmark Results

        | Benchmark | Set | Mem. Peak | Time | Variability |
        | --------- | --- | --------- | ---- | ----------- |
        | ContainerBench(benchAutowireSimple) | - | 3.952mb 0.00% | 4.187μs +0.17% | ±2.05% +108.06% |


        MARKDOWN,

        $output->fetch());
    }

    #[Test]
    public function it_keeps_non_aggregate_table_columns_unchanged(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());

        $table = TableBuilder::create()
            ->addRowArray([
                'name' => 'Example',
                'value' => '123',
            ])
            ->build();

        $reports = Reports::fromReport(ReportBuilder::create()->addObject($table)->build());

        $renderer->render($reports, new Config('markdown', ['file' => null]));

        $this->assertSame(<<<'MARKDOWN'
        | name | value |
        | ---- | ----- |
        | Example | 123 |


        MARKDOWN, $output->fetch());
    }

    #[Test]
    public function it_can_filter_compact_rows_by_minimum_time_difference(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());

        $table = TableBuilder::create()
            ->withTitle('Benchmark Results')
            ->addRowArray([
                'benchmark' => 'ContainerBench',
                'subject' => 'benchAutowireSimple',
                'set' => '',
                'revs' => 1000,
                'its' => 5,
                'mem_peak' => '3.952mb 0.00%',
                'mode' => '4.187μs +0.17%',
                'rstdev' => '±2.05% +1.00%',
            ])
            ->addRowArray([
                'benchmark' => 'ContainerBench',
                'subject' => 'benchAutowireNested',
                'set' => '',
                'revs' => 1000,
                'its' => 5,
                'mem_peak' => '3.952mb +0.10%',
                'mode' => '14.791μs +1.55%',
                'rstdev' => '±0.69% +0.50%',
            ])
            ->build();

        $reports = Reports::fromReport(ReportBuilder::create()->addObject($table)->build());

        $renderer->render($reports, new Config('markdown', ['file' => null, 'outlier_min_diff' => 1.0]));

        $this->assertSame(<<<'MARKDOWN'
        ## Benchmark Results

        | Benchmark | Set | Mem. Peak | Time | Variability |
        | --------- | --- | --------- | ---- | ----------- |
        | ContainerBench(benchAutowireNested) | - | 3.952mb +0.10% | 14.791μs +1.55% | ±0.69% +0.50% |


        MARKDOWN,

        $output->fetch());
    }

    #[Test]
    public function it_shows_an_informative_message_when_no_outliers_match(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());

        $table = TableBuilder::create()
            ->withTitle('Benchmark Results')
            ->addRowArray([
                'benchmark' => 'ContainerBench',
                'subject' => 'benchAutowireSimple',
                'set' => '',
                'revs' => 1000,
                'its' => 5,
                'mem_peak' => '3.952mb 0.00%',
                'mode' => '4.187μs +0.17%',
                'rstdev' => '±2.05% +1.00%',
            ])
            ->addRowArray([
                'benchmark' => 'ContainerBench',
                'subject' => 'benchAutowireNested',
                'set' => '',
                'revs' => 1000,
                'its' => 5,
                'mem_peak' => '3.952mb +0.10%',
                'mode' => '14.791μs +1.55%',
                'rstdev' => '±0.69% +0.50%',
            ])
            ->build();

        $reports = Reports::fromReport(ReportBuilder::create()->addObject($table)->build());

        $renderer->render($reports, new Config('markdown', ['file' => null, 'outlier_min_diff' => 5.0]));

        $this->assertSame(<<<'MARKDOWN'
        ## Benchmark Results

        _No benchmark changes above ±5%._


        MARKDOWN,

        $output->fetch());
    }
}
