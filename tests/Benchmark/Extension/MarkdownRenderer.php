<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Extension;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Registry\Config;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Model\TableRow;
use PhpBench\Report\RendererInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class MarkdownRenderer implements RendererInterface
{
    private const array COMPACT_HEADERS = ['Benchmark', 'Set', 'Mem. Peak', 'Time', 'Variability'];

    private const array COMPACT_SOURCE_COLUMNS = ['benchmark', 'subject', 'set', 'mem_peak', 'mode', 'rstdev'];

    private const int COMPACT_TIME_COLUMN_INDEX = 3;

    public function __construct(
        private OutputInterface $output,
        private Printer $printer,
    ) {}

    public function render(Reports $reports, Config $config): void
    {
        $content = $this->renderContent($reports, $this->resolveOutlierMinDiff($config));
        $file = $config['file'];

        if ($file === null) {
            $this->output->write($content);

            return;
        }

        $this->writeFile($file, $content);
    }

    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'file' => null,
            'outlier_min_diff' => null,
        ]);
        $options->setAllowedTypes('file', ['null', 'string']);
        $options->setAllowedTypes('outlier_min_diff', ['null', 'float', 'int']);
    }

    private function renderContent(Reports $reports, ?float $outlierMinDiff): string
    {
        $lines = [];

        foreach ($reports->tables() as $table) {
            array_push($lines, ...$this->renderTable($table, $outlierMinDiff));
        }

        return implode("\n", $lines) . "\n";
    }

    private function renderTable(Table $table, ?float $outlierMinDiff): array
    {
        $lines = [];
        $title = $table->title();

        if ($title !== null && $title !== '') {
            $lines[] = "## {$title}";
            $lines[] = '';
        }

        $columns = $table->columnNames();

        if ($columns === []) {
            return $lines;
        }

        $rows = array_map($this->renderTableRow(...), $table->rows());
        [$columns, $rows, $isCompactTable] = $this->compactAggregateReportTable($columns, $rows);
        $rows = $this->filterOutlierRows($rows, $outlierMinDiff, $isCompactTable);

        if ($rows === [] && $isCompactTable && $outlierMinDiff !== null && $outlierMinDiff > 0.0) {
            $lines[] = sprintf('_No benchmark changes above ±%s%%._', $this->formatPercentage($outlierMinDiff));
            $lines[] = '';

            return $lines;
        }

        $lines[] = $this->renderRow($columns);
        $lines[] = $this->renderSeparatorRow($columns);

        foreach ($rows as $row) {
            $lines[] = $this->renderRow($row);
        }

        $lines[] = '';

        return $lines;
    }

    private function renderRow(array $cells): string
    {
        return '| ' . implode(' | ', $cells) . ' |';
    }

    private function renderSeparatorRow(array $columns): string
    {
        return $this->renderRow(array_map(
            fn (string $column): string => str_repeat('-', max(3, mb_strlen($column))),
            $columns,
        ));
    }

    private function renderTableRow(TableRow $row): array
    {
        return array_values(array_map($this->formatCell(...), iterator_to_array($row)));
    }

    private function compactAggregateReportTable(array $columns, array $rows): array
    {
        $columnIndexes = $this->resolveCompactSourceColumnIndexes($columns);

        if ($columnIndexes === null) {
            return [$columns, $rows, false];
        }

        $rows = array_map(function (array $row) use ($columnIndexes): array {
            $set = trim((string) $row[$columnIndexes['set']]);

            return [
                sprintf('%s(%s)', $row[$columnIndexes['benchmark']], $row[$columnIndexes['subject']]),
                $set === '' ? '-' : $set,
                $row[$columnIndexes['mem_peak']],
                $row[$columnIndexes['mode']],
                $row[$columnIndexes['rstdev']],
            ];
        }, $rows);

        return [self::COMPACT_HEADERS, $rows, true];
    }

    private function filterOutlierRows(array $rows, ?float $outlierMinDiff, bool $isCompactTable): array
    {
        if (! $isCompactTable || $outlierMinDiff === null || $outlierMinDiff <= 0.0) {
            return $rows;
        }

        return array_values(array_filter($rows, function (array $row) use ($outlierMinDiff): bool {
            $diff = $this->extractTrailingPercentage($row[self::COMPACT_TIME_COLUMN_INDEX]);

            if ($diff === null) {
                return true;
            }

            return abs($diff) >= $outlierMinDiff;
        }));
    }

    private function extractTrailingPercentage(string $cell): ?float
    {
        if (preg_match('/([+-]?\d+(?:\.\d+)?)%\s*$/', $cell, $matches) !== 1) {
            return null;
        }

        return (float) $matches[1];
    }

    private function resolveOutlierMinDiff(Config $config): ?float
    {
        if (! $config->offsetExists('outlier_min_diff')) {
            return null;
        }

        $value = $config['outlier_min_diff'];

        return $value === null ? null : (float) $value;
    }

    private function formatPercentage(float $value): string
    {
        return rtrim(rtrim(sprintf('%.2f', $value), '0'), '.');
    }

    private function resolveCompactSourceColumnIndexes(array $columns): ?array
    {
        $columnIndexes = array_flip($columns);

        if (array_any(self::COMPACT_SOURCE_COLUMNS, fn ($column) => ! array_key_exists($column, $columnIndexes))) {
            return null;
        }

        return [
            'benchmark' => $columnIndexes['benchmark'],
            'subject' => $columnIndexes['subject'],
            'set' => $columnIndexes['set'],
            'mem_peak' => $columnIndexes['mem_peak'],
            'mode' => $columnIndexes['mode'],
            'rstdev' => $columnIndexes['rstdev'],
        ];
    }

    private function formatCell(Node $node): string
    {
        return str_replace('|', '\\|', trim($this->printer->print($node)));
    }

    private function writeFile(string $file, string $content): void
    {
        $this->createDirectory(dirname($file));

        if (file_put_contents($file, $content) === false) {
            throw new RuntimeException(sprintf('Could not write to file "%s"', $file));
        }

        $this->output->writeln("Written markdown report to: {$file}");
    }

    private function createDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, 0o777, true) && ! is_dir($directory)) {
            throw new RuntimeException(sprintf('Could not create directory "%s"', $directory));
        }
    }
}
