<?php

namespace App\Git;

final class FakeGit implements Git
{
    public array $commands = [];
    private string $branch = 'test';

    public function __construct(
        private readonly string $path,
    ) {}

    public function isInitialized(): bool
    {
        return true;
    }

    public function tryInit(string $remote): void
    {
        $this->commands[] = 'git init';
    }

    public function checkoutBranch(string $branch): void
    {
        $this->branch = $branch;
        $this->commands[] = 'git checkout -b ' . $branch;
    }

    public function pull(): void
    {
        $this->commands[] = 'git pull origin ' . $this->getCurrentBranch();
    }

    public function push(): void
    {
        $this->commands[] = 'git push origin ' . $this->getCurrentBranch();
    }

    public function getCurrentBranch(): string
    {
        return 'test';
    }

    public function status(): string
    {
        return '';
    }

    public function commit(string $message): void
    {
        $this->commands[] = 'git add . && git commit -m "' . $message . '"';
    }
}