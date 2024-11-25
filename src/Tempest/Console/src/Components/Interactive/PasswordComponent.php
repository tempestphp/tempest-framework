<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Tempest\Console\Components\Concerns\HasErrors;
use Tempest\Console\Components\Concerns\HasTextInputRenderer;
use Tempest\Console\Components\Concerns\RendersControls;
use Tempest\Console\Components\Renderers\TextInputRenderer;
use Tempest\Console\Components\TextBuffer;
use Tempest\Console\HasCursor;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Point;
use Tempest\Console\Terminal\Terminal;

final class PasswordComponent implements InteractiveConsoleComponent, HasCursor
{
    use RendersControls;
    use HasErrors;
    use HasTextInputRenderer;

    public function __construct(
        public string $label = 'Password',
    ) {
        $this->buffer = new TextBuffer();
        $this->renderer = new TextInputRenderer(maximumLines: 1);
    }

    public function render(Terminal $terminal): string
    {
        $password = $this->buffer->text;

        $this->buffer->setText(str_repeat('*', mb_strlen($password)));

        $render = $this->renderer->render(
            terminal: $terminal,
            state: $this->state,
            buffer: $this->buffer,
            label: $this->label,
            placeholder: null,
        );

        $this->buffer->setText($password);

        return $render;
    }

    public function getCursorPosition(Terminal $terminal): Point
    {
        return $this->renderer->getCursorPosition($terminal, $this->buffer);
    }

    public function cursorVisible(): bool
    {
        return true;
    }
}
