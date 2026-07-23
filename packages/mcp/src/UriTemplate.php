<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Stringable;

final class UriTemplate implements Stringable
{
    /** @var string[] */
    public readonly array $variableNames;

    /** @var string[] */
    private readonly array $orderedVariableNames;

    private ?string $pattern = null;

    public function __construct(
        public readonly string $template,
    ) {
        preg_match_all('/\{(\w+)}/', $template, $matches);

        $this->orderedVariableNames = $matches[1];
        $this->variableNames = array_values(array_unique($matches[1]));
    }

    public function isTemplated(): bool
    {
        return $this->variableNames !== [];
    }

    /**
     * Matches the given URI against this template, returning the extracted template variables, or `null` when the URI does not match.
     *
     * @return array<string, string>|null
     */
    public function match(string $uri): ?array
    {
        $this->pattern ??= $this->compilePattern();

        if (! preg_match($this->pattern, $uri, $matches)) {
            return null;
        }

        $variables = [];

        foreach ($this->orderedVariableNames as $index => $name) {
            $variables[$name] = rawurldecode($matches[$index + 1] ?? '');
        }

        return $variables;
    }

    public function __toString(): string
    {
        return $this->template;
    }

    private function compilePattern(): string
    {
        $segments = preg_split('/(\{\w+})/', $this->template, flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $pattern = '';

        foreach ($segments as $segment) {
            $pattern .= preg_match('/^\{\w+}$/', $segment)
                ? '([^/]+)'
                : preg_quote($segment, '#');
        }

        return "#^{$pattern}$#";
    }
}
