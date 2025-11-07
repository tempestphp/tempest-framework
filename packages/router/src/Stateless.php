<?php

namespace Tempest\Router;

use Attribute;

/**
 * Mark a route handler as stateless, causing all cookie- and session-related middleware to be skipped.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Stateless
{
}
