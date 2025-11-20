<?php

namespace Tempest\Testing;

final class TestFoo
{
    #[Test]
    public function a(): void
    {
        test(true)->is(false);
    }

    #[Test]
    public function b(): void
    {
        test(true)->is(false);
    }

    #[Test]
    public function c(): void
    {
        test(true)->is(true);
    }

    #[Test]
    public function d(): void
    {
        test(true)->is(true);
    }

    #[Test]
    public function e(): void
    {
        test(true)->is(false);
    }
}