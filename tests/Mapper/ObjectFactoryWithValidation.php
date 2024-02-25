<?php

namespace Tests\Tempest\Mapper;

use Tempest\ORM\IsModel;
use Tempest\ORM\Model;
use Tempest\Validation\Rules\Length;

class ObjectFactoryWithValidation implements Model
{
	use IsModel;

	#[Length(min: 2)]
	public string $prop;
}
