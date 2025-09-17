<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\CanOpenInEditor;
use Tempest\Console\Components\ComponentState;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Support\Filesystem;

use function Tempest\env;

/**
 * @mixin InteractiveConsoleComponent
 * @phpstan-require-implements InteractiveConsoleComponent
 */
trait OpensInEditor
{
    private function supportsOpeningInEditor(): bool
    {
        // @phpstan-ignore function.alreadyNarrowedType
        return is_subclass_of(static::class, CanOpenInEditor::class) && (bool) $this->getEditorCommand();
    }

    private function getEditorCommand(): ?string
    {
        return env('TEMPEST_EDITOR') ?? env('EDITOR') ?? null;
    }

    public function openInEditor(?string $text): string
    {
        if (! $this->supportsOpeningInEditor()) {
            return $text;
        }

        $previousState = $this->getState();
        $this->setState(ComponentState::BLOCKED);

        $editor = $this->getEditorCommand();
        $tempFile = tempnam(sys_get_temp_dir(), '.TEMPEST_INPUT');

        Filesystem\write_file($tempFile, $text ?? '');

        if (passthru(escapeshellcmd("{$editor} " . escapeshellarg($tempFile))) === false) {
            // TODO: failed. handle that
            return $text;
        }

        $updatedText = Filesystem\read_file($tempFile);
        unlink($tempFile);

        $this->setState($previousState);

        return $updatedText;
    }
}
