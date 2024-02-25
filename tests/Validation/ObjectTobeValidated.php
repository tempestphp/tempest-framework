<?php

namespace Tests\Tempest\Validation;

use Tempest\Validation\IsValidated;
use Tempest\Validation\Rules\Length;

class ObjectTobeValidated implements IsValidated
{
	public function __construct(
		#[Length(min: 2, max: 3)]
		public string $name,
	)
	{
	}
}
