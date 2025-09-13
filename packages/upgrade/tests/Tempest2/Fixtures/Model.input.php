<?php

use Tempest\Database\Id;

final class Model
{
    public Id $id;

    public function test()
    {
        return $this->id->id;
    }
}
