<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionApplication
{
    public function __construct(
        private CompletionArgumentParser $argumentParser = new CompletionArgumentParser(),
        private CompletionInputNormalizer $inputNormalizer = new CompletionInputNormalizer(),
        private CompletionMetadataFileReader $metadataFileReader = new CompletionMetadataFileReader(),
        private CompletionMetadataParser $metadataParser = new CompletionMetadataParser(),
        private CompletionEngine $engine = new CompletionEngine(),
        private CompletionOutputFormatter $outputFormatter = new CompletionOutputFormatter(),
    ) {}

    public function run(array $args): void
    {
        $parsedArguments = $this->argumentParser->parse($args);

        if (! $parsedArguments instanceof CompletionArguments) {
            return;
        }

        $input = $this->inputNormalizer->normalize($parsedArguments->words, $parsedArguments->currentIndex);

        if (! $input instanceof CompletionInput) {
            return;
        }

        $content = $this->metadataFileReader->read($parsedArguments->metadataPath);

        if (! is_string($content)) {
            return;
        }

        $metadata = $this->metadataParser->parseJson($content);

        if (! $metadata instanceof CompletionMetadata) {
            return;
        }

        $completions = $this->engine->complete($metadata, $input);

        if ($completions === []) {
            return;
        }

        $output = $this->outputFormatter->format($completions);

        if ($output === '') {
            return;
        }

        fwrite(STDOUT, $output);
    }
}
