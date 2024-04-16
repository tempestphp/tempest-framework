<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\ConsoleComponent;
use Tempest\Console\Cursor;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\Key;
use Tempest\Console\Point;

final class PasswordComponent implements ConsoleComponent, HasCursor
{
    public string $password = '';

    public function __construct(
        public string $label = 'Password',
    ) {
    }

    public function render(): string
    {
        $output = "<question>{$this->label}</question> ";
        $output .= str_repeat('*', strlen($this->password));

        return $output . PHP_EOL . PHP_EOL . "Press <em>enter</em> to confirm, press <em>ctrl+c</em> to cancel" . PHP_EOL;
    }

    #[HandlesKey(Key::BACKSPACE)]
    public function backspace(): void
    {
        $this->password = substr($this->password, 0, strlen($this->password) - 1);
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): string
    {
        return $this->password;
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        preg_match('/[\w\s]+/', $key, $matches);

        if (($matches[0] ?? null) !== $key) {
            return;
        }

        $this->password .= $key;
    }

    public function placeCursor(Cursor $cursor): void
    {
        $cursor->place(new Point(
            x: $cursor->getPosition()->x + strlen($this->password) + strlen($this->label) + 3,
            y: $cursor->getPosition()->y - 3,
        ));
    }
}
