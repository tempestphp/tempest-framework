<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Reflection;

/**
 * Serves synthetic PHP sources from memory so the cold-parse benchmark does
 * no tmp-file IO. `parse-bench://<key>` resolves to content registered via
 * {@see self::setContent()}.
 */
final class ColdParseStreamWrapper
{
    private const string PROTOCOL = 'parse-bench';

    /** @var array<string, string> */
    private static array $contents = [];

    /** @var resource|null */
    public $context;

    private string $content = '';

    private int $position = 0;

    public static function pathFor(string $key): string
    {
        return self::PROTOCOL . '://' . $key;
    }

    public static function setContent(string $path, string $content): void
    {
        self::register();

        self::$contents[$path] = $content;
    }

    private static function register(): void
    {
        if (in_array(self::PROTOCOL, stream_get_wrappers(), true)) {
            return;
        }

        stream_wrapper_register(self::PROTOCOL, self::class);
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->content = self::$contents[$path] ?? '';
        $this->position = 0;

        return isset(self::$contents[$path]);
    }

    public function stream_read(int $count): string
    {
        $chunk = substr($this->content, $this->position, $count);
        $this->position += strlen($chunk);

        return $chunk;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    /** @return array<string, mixed> */
    public function stream_stat(): array
    {
        return ['size' => strlen($this->content)];
    }

    public function stream_close(): void {}
}
