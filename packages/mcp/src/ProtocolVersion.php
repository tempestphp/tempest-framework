<?php

declare(strict_types=1);

namespace Tempest\Mcp;

enum ProtocolVersion: string
{
    case VERSION_2025_11_25 = '2025-11-25';
    case VERSION_2025_06_18 = '2025-06-18';
    case VERSION_2025_03_26 = '2025-03-26';
    case VERSION_2024_11_05 = '2024-11-05';

    public const ProtocolVersion LATEST = self::VERSION_2025_11_25;

    /**
     * @return ProtocolVersion[]
     */
    public static function supported(): array
    {
        return self::cases();
    }

    /**
     * Returns the requested version when supported, or the latest supported version otherwise.
     */
    public static function negotiate(?string $requested): self
    {
        if ($requested === null) {
            return self::LATEST;
        }

        return self::tryFrom($requested) ?? self::LATEST;
    }
}
