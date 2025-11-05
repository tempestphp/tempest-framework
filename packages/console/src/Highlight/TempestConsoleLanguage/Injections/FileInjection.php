<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\TempestConsoleLanguage\Injections;

use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Injection;
use Tempest\Highlight\ParsedInjection;
use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Support\Filesystem;

use function Tempest\root_path;
use function Tempest\Support\str;

final readonly class FileInjection implements Injection
{
    public function parse(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(preg_replace_callback(
            subject: $content,
            pattern: '/(?<match>\<file=(?<quote>[\"\'])(?<file>.+)\k<quote>\s*\/?>)/',
            callback: function (array $matches) {
                $href = $matches['file'];
                $exists = Filesystem\normalize_path($href) !== null;
                $file = $exists
                    ? str(Filesystem\normalize_path($href))->replace('\\', '/')->stripStart(root_path())->stripStart('/')
                    : $href;

                return TerminalStyle::UNDERLINE((string) $file);
            },
        ));
    }
}
