<?php

namespace Tempest\Database;

interface HasTrailingStatements
{
    public array $trailingStatements { get; }
}