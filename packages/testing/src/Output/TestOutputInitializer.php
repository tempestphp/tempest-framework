<?php

namespace Tempest\Testing\Output;

use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\Application;

final class TestOutputInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ?TestOutput
    {
        if (! $container->get(Application::class) instanceof ConsoleApplication) {
            return null;
        }

        $argumentBag = $container->get(ConsoleArgumentBag::class);

        $teamcity = $argumentBag->has('teamcity');

        if ($teamcity) {
            $output = $container->get(TeamCityOutput::class);
        } else {
            $output = $container->get(DefaultOutput::class);
        }

        $output->verbose = $argumentBag->has('verbose');

        return $output;
    }
}
