<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/ChildObject.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/ChildObject.php

final class ChildObject
{
    public string $name;

    public ParentObject $parent;

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/ChildObject.php
    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentObject[] */
========
    /** @var \Tempest\Mapper\Tests\Fixtures\ParentObject[] */
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/ChildObject.php
    public array $parentCollection;
}
