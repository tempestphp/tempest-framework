<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Tempest\Console\CanOpenInEditor;
use Tempest\Console\Components\Concerns\HasErrors;
use Tempest\Console\Components\Concerns\HasTextInputRenderer;
use Tempest\Console\Components\Concerns\OpensInEditor;
use Tempest\Console\Components\Concerns\RendersControls;
use Tempest\Console\Components\Renderers\TextInputRenderer;
use Tempest\Console\Components\Static\StaticTextBoxComponent;
use Tempest\Console\Components\TextBuffer;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\HasStaticComponent;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;
use Tempest\Console\StaticConsoleComponent;
use Tempest\Console\Terminal\Terminal;

final class TextInputComponent implements InteractiveConsoleComponent, HasCursor, HasStaticComponent, CanOpenInEditor
{
    use HasErrors;
    use HasTextInputRenderer;
    use OpensInEditor;
    use RendersControls;

    public function __construct(
        public string $label,
        public null|int|string $default = null,
        public ?string $placeholder = null,
        public ?string $hint = null,
        bool $multiline = false,
    ) {
        $this->multiline = $multiline;
        $this->buffer = new TextBuffer($default);
        $this->renderer = new TextInputRenderer($multiline);
    }

    public function render(Terminal $terminal): string
    {
        return $this->renderer->render(
            terminal: $terminal,
            state: $this->state,
            buffer: $this->buffer,
            label: $this->label,
            placeholder: $this->placeholder,
            hint: $this->hint,
        );
    }

    private function getControls(): array
    {
        return [
            ...($this->multiline ? ['enter' => 'newline'] : []),
            ...($this->multiline && $this->supportsOpeningInEditor() ? ['ctrl+b' => 'open in editor'] : []),
            ...($this->multiline ? ['alt+enter' => 'confirm'] : ['enter' => 'confirm']),
            'ctrl+c' => 'cancel',
        ];
    }

    public function getStaticComponent(): StaticConsoleComponent
    {
        return new StaticTextBoxComponent(
            label: $this->label,
            default: $this->default,
        );
    }

    public function getCursorPosition(Terminal $terminal): Point
    {
        return $this->renderer->getCursorPosition($terminal, $this->buffer);
    }

    public function cursorVisible(): bool
    {
        return true;
    }

    #[HandlesKey(Key::CTRL_B)]
    public function editor(): void
    {
        $result = $this->openInEditor($this->buffer->text);

        $this->setText($this->multiline ? $result : rtrim(str_replace(["\n", "\r\n"], ' ', $result)));
    }
}
