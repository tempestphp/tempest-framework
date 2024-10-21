<?php

declare(strict_types=1);

namespace Tempest\Console;

/**
 * Defines a console command that is specifically for generating files.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class GeneratorCommand extends ConsoleCommand {}
