<?php

namespace Tests\Tempest\Fixtures\Console;

use Dom\HTMLDocument;
use Masterminds\HTML5;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\View\Parser\TempestViewLexer;

use Tempest\View\Parser\TempestViewParser;
use const Dom\HTML_NO_DEFAULT_NS;

final readonly class LexerProfileCommand
{
    use HasConsole;

    #[ConsoleCommand]
    public function __invoke(string $name = 'large'): void
    {
        $html = file_get_contents(__DIR__ . "/{$name}.html");

        $this->parseTempest($html);
//        $this->parseMasterminds($html);
//        $this->parseDom($html);
    }

    private function parseTempest(string $html): void
    {
        $start = microtime(true);
        new TempestViewParser(new TempestViewLexer($html)->lex())->parse();
        $end = microtime(true);

        $time = round(($end - $start) * 1000, 2);
        $this->success('[Tempest] ' . $time . 'ms');
    }

    private function parseDom(string $html): void
    {
        $start = microtime(true);
        HTMLDocument::createFromString($html, LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS);
        $end = microtime(true);

        $time = round(($end - $start) * 1000, 2);
        $this->success('[DOM] ' . $time . 'ms');
    }

    private function parseMasterminds(string $html): void
    {
        $start = microtime(true);
        $html5 = new HTML5();
        $html5->loadHTML($html);

        $end = microtime(true);

        $time = round(($end - $start) * 1000, 2);
        $this->success('[Masterminds] ' . $time . 'ms');
    }
}
