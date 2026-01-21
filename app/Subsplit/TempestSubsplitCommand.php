<?php

namespace App\Subsplit;

use App\Git\Git;
use App\Git\GitOperationFailed;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Container;
use Tempest\Support\Filesystem;
use App\Git\GenericGit;
use function Tempest\root_path;

final class TempestSubsplitCommand
{
    use HasConsole;

    public function __construct(
        private Container $container,
    ) {}

    #[ConsoleCommand(middleware: [ForceMiddleware::class])]
    public function __invoke(?string $package = null): void
    {
        $packages = Package::all();

        if ($package) {
            $packages = $packages->filter(fn (Package $search) => $search->name === $package)->values();
        }

        if ($packages->isEmpty()) {
            $this->error('No packages found');
            return;
        }

        $total = $packages->count();

        $currentBranch = $this->container->get(Git::class, path: root_path())->getCurrentBranch();

        $this->confirm("Subsplitting on `{$currentBranch}`, continue?");

        foreach ($packages as $i => $package) {
            $this->info("{$i}/{$total} <em>tempest/{$package->name}</em>");

            try {
                $git = $this->container->get(Git::class, path: $package->buildPath);

                Filesystem\ensure_directory_exists($package->buildPath);

                if ( ! $git->isInitialized()) {
                    $git->tryInit($package->remote);
                    $this->writeln(' - New remote initialized');
                }

                $this->writeln(" - Checking out <em>{$currentBranch}</em> branch");
                $git->checkoutBranch($currentBranch);

                $this->writeln(' - Pulling latest changes');

                try {
                    $git->pull();
                } catch (GitOperationFailed) {
                    // Remote branch does not exist yet
                }

                // Don't we have a recursive directory copy function somewhere??
                exec("cp -R {$package->sourcePath} {$package->buildPath}");
                $this->writeln(" - Copied updated contents from <em>{$package->sourcePath}</em> to <em>{$package->buildPath}</em>");

                // TODO: Validate composer, LICENSE, README, and what else?

                $this->writeln(" - Committing changes");
                $git->commit("chore: subsplit");

                $this->writeln(" - Pushing changes");
                $git->push();

                $this->success('Done');
            } catch (GitOperationFailed $e) {
                $this->error($e->getMessage());
                continue;
            }
        }
    }
}