<?php

namespace App\Git;

interface Git
{
    public function isInitialized(): bool;

    public function tryInit(string $remote): void;

    public function checkoutBranch(string $branch): void;

    public function pull(): void;

    public function push(): void;

    public function getCurrentBranch(): string;

    public function status(): string;

    public function commit(string $message): void;
}