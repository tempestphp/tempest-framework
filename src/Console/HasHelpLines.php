<?php

declare(strict_types=1);

namespace Tempest\Console;

interface HasHelpLines
{

    /**
     * @return string[]
     */
    public function getHelpLines(): array;

}
