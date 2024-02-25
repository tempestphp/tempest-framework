<?php

declare(strict_types=1);

arch('src')
    ->expect('Tempest\Validation\Rules')
    ->toUseStrictTypes()
    ->toHaveAttribute(Attribute::class)
    ->toImplement(Tempest\Validation\Rule::class)
    ->toBeFinal()
    ->toBeReadonly();
