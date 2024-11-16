<?php

declare(strict_types=1);

namespace Tempest\Support {

    use Stringable;

    /**
     * Creates an instance of {@see StringHelper} using the given `$string`.
     */
    function str(Stringable|string|null $string = ''): StringHelper
    {
        return new StringHelper($string);
    }

    /**
     * Creates an instance of {@see ArrayHelper} using the given `$input`. If `$input` is not an array, it will be wrapped in one.
     */
    function arr(mixed $input = []): ArrayHelper
    {
        return new ArrayHelper($input);
    }
}
