<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;

/**
 * Marks a primary key property to automatically generate UUID v7 values when the model is saved.
 * This must be applied to `PrimaryKey` properties that would use UUIDs instead of auto-incrementing integers.
 *
 * **Example**
 * ```php
 * final class User
 * {
 *     #[Uuid]
 *     public PrimaryKey $uuid;
 *
 *     public function __construct(
 *         public string $name,
 *     ) {}
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Uuid
{
}
