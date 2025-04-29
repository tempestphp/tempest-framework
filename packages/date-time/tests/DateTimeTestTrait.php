<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use function date_default_timezone_get;
use function date_default_timezone_set;
use function locale_get_default;
use function locale_set_default;

trait DateTimeTestTrait
{
    private string $timezone;

    private string $locale;

    protected function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
        $this->locale = locale_get_default();

        date_default_timezone_set('Europe/London');
        locale_set_default('en_GB');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
        locale_set_default($this->locale);
    }
}
