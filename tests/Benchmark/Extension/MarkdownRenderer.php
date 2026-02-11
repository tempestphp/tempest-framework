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
    public function __construct(
        private OutputInterface $output,
        private Printer $printer,
    ) {}

    public function render(Reports $reports, Config $config): void
    {
        $content = $this->renderContent($reports);
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
        ]);
        $options->setAllowedTypes('file', ['null', 'string']);
    }

    private function renderContent(Reports $reports): string
    {
        $lines = [];

        foreach ($reports->tables() as $table) {
            array_push($lines, ...$this->renderTable($table));
        }

        return implode("\n", $lines) . "\n";
    }

    private function renderTable(Table $table): array
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

        $lines[] = $this->renderRow($columns);
        $lines[] = $this->renderSeparatorRow($columns);

        foreach ($table as $row) {
            $lines[] = $this->renderDataRow($row);
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

    private function renderDataRow(TableRow $row): string
    {
        $cells = array_map($this->formatCell(...), iterator_to_array($row));

        return $this->renderRow($cells);
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
