<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

interface SessionManager
{
    public function create(SessionId $id): Session;

    public function resolve(SessionId $id): ?Session;

    public function destroy(SessionId $id): void;

    public function persist(Session $session): void;

    public function cleanup(): void;
}
