<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

use function Tempest\Support\str;

final class TextElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $text,
    ) {}

    public function compile(): string
    {
        return str($this->text)
            // Render {{-- --}}
            ->replaceRegex(
                regex: '/{{--(.|\n)*?--}}/',
                replace: '',
            )
            // Render {{
            ->replaceRegex(
                regex: '/{{(?<match>.*?)}}/',
                replace: function (array $matches): string {
                    return sprintf('<?= $this->escape(%s); ?>', $matches['match']);
                },
            )
            // Render {!!
            ->replaceRegex(
                regex: '/{!!(?<match>.*?)!!}/',
                replace: function (array $matches): string {
                    return sprintf('<?= %s ?>', $matches['match']);
                },
            )
            ->toString();
    }
}
