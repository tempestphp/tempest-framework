<?php

namespace Tempest\Testing {
    use Tempest\Testing\Testers\Tester;

    function test(mixed $subject = null): Tester
    {
        return new Tester($subject);
    }
}
