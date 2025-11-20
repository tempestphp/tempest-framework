<?php

namespace Tempest\Testing
{
    function test(mixed $subject): Tester
    {
        return new Tester($subject);
    }
}