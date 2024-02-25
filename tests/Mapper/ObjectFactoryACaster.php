<?php

namespace Tests\Tempest\Mapper;

use Tempest\ORM\Caster;

class ObjectFactoryACaster implements Caster
{
	public function cast(mixed $input): string
	{
		return 'casted';
	}
}
