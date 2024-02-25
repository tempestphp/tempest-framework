<?php

namespace Tests\Tempest\ORM;

use Tempest\ORM\IsModel;
use Tempest\ORM\Model;

class Foo implements Model
{
	use IsModel;

	public string $bar;
}
