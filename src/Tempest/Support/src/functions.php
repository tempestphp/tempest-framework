<?php


namespace Tempest\Support {
    function str(string $string = ''): StringHelper
    {
        return new StringHelper($string);
    }
}