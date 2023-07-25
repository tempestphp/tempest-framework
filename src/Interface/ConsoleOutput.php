<?php

namespace Tempest\Interface;

interface ConsoleOutput
{
    public function writeln(string $line): void;

    public function info(string $line): void;

    public function error(string $line): void;

    public function success(string $line): void;
}