<?php

namespace App\Git;

use Tempest\Container\Autowire;
use function Tempest\Support\path;
use Tempest\Support\Filesystem;

#[Autowire]
final readonly class GitClient implements Git
{
    public function __construct(
        private string $path,
    ) {}

    public function isInitialized(): bool
    {
        return Filesystem\exists(path($this->path . '/.git/'));
    }

    public function tryInit(string $remote): void
    {
        if ($this->isInitialized()) {
            return;
        }

        exec("cd {$this->path}; git init && git remote add origin {$remote};", result_code: $result);

        if ($result !== 0) {
            throw new GitOperationFailed('Failed to initialize git repository.');
        }
    }

    public function checkoutBranch(string $branch): void
    {
        exec("cd {$this->path}; git checkout -b {$branch} &> /dev/null", result_code: $result);

        if ($result === 0) {
            return;
        }

        exec("cd {$this->path}; git checkout &> {$branch}", result_code: $result);

        if ($result !== 0) {
            throw new GitOperationFailed('Failed to checkout branch.');
        }
    }

    public function pull(): void
    {
        exec("cd {$this->path}; git pull origin {$this->getCurrentBranch()} &> /dev/null", result_code: $result);

        if ($result !== 0) {
            throw new GitOperationFailed('Failed to pull changes from remote.');
        }
    }

    public function push(): void
    {
        exec("cd {$this->path}; git push origin {$this->getCurrentBranch()} &> /dev/null", result_code: $result);

        if ($result !== 0) {
            throw new GitOperationFailed('Failed to push changes to remote.');
        }
    }

    public function getCurrentBranch(): string
    {
        exec("cd {$this->path}; git branch --show-current", output: $output, result_code: $result);

        if ($result !== 0 || $output === []) {
            throw new GitOperationFailed('Failed get current branch.');
        }

        return $output[0];
    }

    public function status(): string
    {
        exec("cd {$this->path}; git status" , output: $output, result_code: $result);

        if ($result !== 0) {
            throw new GitOperationFailed('Failed to get git status.');
        }

        return implode(PHP_EOL, $output);
    }

    public function commit(string $message): void
    {
        if (str_contains($this->status(), 'nothing to commit')) {
            return;
        }

        exec("cd {$this->path}; git add . && git commit -m \"{$message}\"" , result_code: $result);

        if ($result !== 0) {
            throw new GitOperationFailed('Failed to commit changes.');
        }
    }
}