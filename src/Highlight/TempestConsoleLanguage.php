<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Console\Highlight\Injections\CommentInjection;
use Tempest\Console\Highlight\Injections\EmphasizeInjection;
use Tempest\Console\Highlight\Injections\ErrorInjection;
use Tempest\Console\Highlight\Injections\H1Injection;
use Tempest\Console\Highlight\Injections\H2Injection;
use Tempest\Console\Highlight\Injections\QuestionInjection;
use Tempest\Console\Highlight\Injections\StrongInjection;
use Tempest\Console\Highlight\Injections\SuccessInjection;
use Tempest\Console\Highlight\Injections\UnderlineInjection;
use Tempest\Highlight\Language;

final readonly class TempestConsoleLanguage implements Language
{
    public function getName(): string
    {
        return 'console';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInjections(): array
    {
        return [
            new QuestionInjection(),
            new EmphasizeInjection(),
            new StrongInjection(),
            new UnderlineInjection(),
            new ErrorInjection(),
            new CommentInjection(),
            new H1Injection(),
            new H2Injection(),
            new SuccessInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [];
    }
}
