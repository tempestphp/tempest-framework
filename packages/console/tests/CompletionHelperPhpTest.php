<?php

declare(strict_types=1);

namespace Tempest\Console\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Console\Completion\CompletionCandidate;
use Tempest\Console\Completion\CompletionEngine;
use Tempest\Console\Completion\CompletionInput;
use Tempest\Console\Completion\CompletionInputNormalizer;
use Tempest\Console\Completion\CompletionMetadata;
use Tempest\Console\Completion\CompletionMetadataParser;

final class CompletionHelperPhpTest extends TestCase
{
    private CompletionEngine $engine;
    private CompletionInputNormalizer $inputNormalizer;
    private CompletionMetadataParser $metadataParser;

    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Shell completion is not supported on Windows.');
        }

        $this->engine = new CompletionEngine();
        $this->inputNormalizer = new CompletionInputNormalizer();
        $this->metadataParser = new CompletionMetadataParser();
    }

    #[Test]
    public function entrypoint_is_executable_php_script(): void
    {
        $entrypoint = __DIR__ . '/../bin/tempest-complete';

        $content = file_get_contents($entrypoint);

        $this->assertIsString($content);
        $this->assertStringStartsWith("#!/usr/bin/env php\n<?php\n", $content);
    }

    #[Test]
    public function completes_visible_commands_with_sanitized_descriptions(): void
    {
        $metadata = $this->parseMetadata([
            'commands' => [
                'about' => [
                    'hidden' => false,
                    'description' => "  About\n\tcommand  ",
                    'flags' => [],
                ],
                'cache:clear' => [
                    'hidden' => false,
                    'description' => null,
                    'flags' => [],
                ],
                'hidden:command' => [
                    'hidden' => true,
                    'description' => 'Should not be visible',
                    'flags' => [],
                ],
            ],
        ]);

        $input = $this->inputNormalizer->normalize(['tempest', ''], 1);

        $this->assertInstanceOf(CompletionInput::class, $input);

        $completions = $this->engine->complete($metadata, $input);

        $this->assertSame(
            [
                ['value' => 'about', 'display' => 'about        About command'],
                ['value' => 'cache:clear', 'display' => null],
            ],
            $this->mapCompletions($completions),
        );
    }

    #[Test]
    public function skips_used_non_repeatable_flags_but_keeps_repeatable_flags(): void
    {
        $metadata = $this->parseMetadata([
            'commands' => [
                'deploy' => [
                    'hidden' => false,
                    'description' => null,
                    'flags' => [
                        [
                            'name' => 'force',
                            'flag' => '--force',
                            'aliases' => ['-f'],
                            'description' => 'Force mode',
                            'value_options' => [],
                            'repeatable' => false,
                        ],
                        [
                            'name' => 'tag',
                            'flag' => '--tag=',
                            'aliases' => ['-t'],
                            'description' => 'Tag value',
                            'value_options' => ['alpha', 'beta'],
                            'repeatable' => true,
                        ],
                        [
                            'name' => 'verbose',
                            'flag' => '--verbose',
                            'aliases' => ['-v'],
                            'description' => 'Verbose output',
                            'value_options' => [],
                            'repeatable' => false,
                        ],
                    ],
                ],
            ],
        ]);

        $input = $this->inputNormalizer->normalize(['tempest', 'deploy', '--verbose', ''], 3);

        $this->assertInstanceOf(CompletionInput::class, $input);

        $completions = $this->engine->complete($metadata, $input);
        $mappedCompletions = $this->mapCompletions($completions);

        $this->assertSame(['--force', '--tag='], array_column($mappedCompletions, 'value'));
        $this->assertStringContainsString('--tag=<alpha,beta> / -t', $mappedCompletions[1]['display']);
        $this->assertStringContainsString('Tag value', $mappedCompletions[1]['display']);
    }

    #[Test]
    public function treats_combined_short_flags_as_used(): void
    {
        $metadata = $this->parseMetadata([
            'commands' => [
                'deploy' => [
                    'hidden' => false,
                    'description' => null,
                    'flags' => [
                        [
                            'name' => 'ansi',
                            'flag' => '--ansi',
                            'aliases' => ['-a'],
                            'description' => null,
                            'value_options' => [],
                            'repeatable' => false,
                        ],
                        [
                            'name' => 'force',
                            'flag' => '--force',
                            'aliases' => ['-f'],
                            'description' => null,
                            'value_options' => [],
                            'repeatable' => false,
                        ],
                    ],
                ],
            ],
        ]);

        $input = $this->inputNormalizer->normalize(['tempest', 'deploy', '-af', ''], 3);

        $this->assertInstanceOf(CompletionInput::class, $input);

        $completions = $this->engine->complete($metadata, $input);

        $this->assertSame([], $this->mapCompletions($completions));
    }

    #[Test]
    public function handles_php_passthrough_command_normalization(): void
    {
        $normalized = $this->inputNormalizer->normalize(['/usr/bin/php', 'vendor/bin/tempest', 'cache:clear'], 2);

        $this->assertInstanceOf(CompletionInput::class, $normalized);

        $this->assertSame(
            ['vendor/bin/tempest', 'cache:clear'],
            $normalized->words,
        );

        $this->assertSame(1, $normalized->currentIndex);
    }

    #[Test]
    public function ignores_no_prefixed_long_flags_when_marking_used_flags(): void
    {
        $metadata = $this->parseMetadata([
            'commands' => [
                'deploy' => [
                    'hidden' => false,
                    'description' => null,
                    'flags' => [
                        [
                            'name' => 'ansi',
                            'flag' => '--ansi',
                            'aliases' => ['-a'],
                            'description' => null,
                            'value_options' => [],
                            'repeatable' => false,
                        ],
                    ],
                ],
            ],
        ]);

        $input = $this->inputNormalizer->normalize(['tempest', 'deploy', '--no-ansi', ''], 3);

        $this->assertInstanceOf(CompletionInput::class, $input);

        $completions = $this->engine->complete($metadata, $input);

        $this->assertSame([], $this->mapCompletions($completions));
    }

    private function parseMetadata(array $metadata): CompletionMetadata
    {
        $parsedMetadata = $this->metadataParser->parse($metadata);

        $this->assertInstanceOf(CompletionMetadata::class, $parsedMetadata);

        return $parsedMetadata;
    }

    private function mapCompletions(array $completions): array
    {
        return array_map(
            fn (CompletionCandidate $completion): array => [
                'value' => $completion->value,
                'display' => $completion->display,
            ],
            $completions,
        );
    }
}
