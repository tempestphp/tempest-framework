<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Console\Highlight\Injections\EmphasizeInjection;
use Tempest\Console\Highlight\Injections\ErrorInjection;
use Tempest\Console\Highlight\Injections\QuestionInjection;
use Tempest\Console\Highlight\Injections\StrongInjection;
use Tempest\Console\Highlight\Injections\UnderlineInjection;
use Tempest\Highlight\Language;

final readonly class ConsoleComponentLanguage implements Language
{
    public function getInjections(): array
    {
        return [
            new QuestionInjection(),
            new EmphasizeInjection(),
            new StrongInjection(),
            new UnderlineInjection(),
            new ErrorInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [];
    }
}
