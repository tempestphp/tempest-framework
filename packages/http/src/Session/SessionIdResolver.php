<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

interface SessionIdResolver
{
    /**
     * Resolves the identifier sent by the client, creating a new one if there is none.
     */
    public function resolve(): SessionId;

    /**
     * Creates a new identifier and sends it to the client, replacing the one it was using.
     *
     * @see SessionRegenerator
     */
    public function issueNewId(): SessionId;
}
