<?php

namespace Tempest\View;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Markdown\Markdown;

final class MarkdownInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Markdown
    {
        return new Markdown();
    }
}
