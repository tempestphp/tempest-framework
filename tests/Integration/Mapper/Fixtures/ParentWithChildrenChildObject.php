<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/ParentWithChildrenChildObject.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/ParentWithChildrenChildObject.php

final class ParentWithChildrenChildObject
{
    public string $name;

    public ParentWithChildrenObject $parent;

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/ParentWithChildrenChildObject.php
    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentWithChildrenObject[] */
========
    /** @var \Tempest\Mapper\Tests\Fixtures\ParentWithChildrenObject[] */
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/ParentWithChildrenChildObject.php
    public array $parentCollection;
}
