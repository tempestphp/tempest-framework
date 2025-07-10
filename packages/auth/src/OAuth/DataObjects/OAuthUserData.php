<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\DataObjects;

use Tempest\Mapper\MapFrom;
use function Tempest\map;

final readonly class OAuthUserData
{
    public function __construct(
        public int|string $id,
        public string $nickname,
        public string $name,
        public string $email,
        public string $avatar,
    ) {}

    public static function from(array $data): self
    {
        return map($data)->to(self::class);
    }
}