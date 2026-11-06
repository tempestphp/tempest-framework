<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Generation;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\Php\ClassGenerator;
use Tempest\Generation\Php\ClassManipulator;
use Tempest\Generation\Php\DataObjects\StubFile;

final class ClassGenerationBench
{
    #[Iterations(5)]
    #[Revs(200)]
    #[Warmup(2)]
    public function benchGenerateClass(): void
    {
        $class = new ClassGenerator('CreateBooksTable', namespace: 'App\\Database');
        $class->setFinal();
        $class->setReadOnly();
        $class->addImplement('Tempest\\Database\\MigratesUp');
        $class->addMethod(
            'up',
            body: "return new \\Tempest\\Database\\QueryStatements\\CreateTableStatement('books')\n    ->primary()\n    ->text('title');",
            returnType: 'Tempest\\Database\\QueryStatement',
        );
        $class->addMethod('getName', body: "return '2026-10-27_create_books_table';", returnType: 'string');
        $class->print();
    }

    #[Iterations(5)]
    #[Revs(200)]
    #[Warmup(2)]
    public function benchManipulateStub(): void
    {
        $class = new ClassManipulator(__DIR__ . '/../../../packages/auth/src/Installer/oauth/OAuthControllerStub.php');
        $class->setNamespace('App\\Authentication\\OAuth');
        $class->setClassName('GithubController');
        $class->removeClassAttribute(SkipDiscovery::class);
        $class->print();
    }

    #[Iterations(5)]
    #[Revs(20)]
    #[Warmup(1)]
    public function benchManipulateLargeClass(): void
    {
        $class = new ClassManipulator(__DIR__ . '/../../../packages/container/src/GenericContainer.php');
        $class->setNamespace('App\\Container');
        $class->setClassName('Container');
        $class->print();
    }

    #[Iterations(5)]
    #[Revs(200)]
    #[Warmup(2)]
    public function benchDetectStubFileTypes(): void
    {
        StubFile::from(__DIR__ . '/../../../packages/console/src/Stubs/CommandStub.php');
        StubFile::from(__DIR__ . '/../../../packages/console/src/Stubs/database.config.stub.php');
    }
}
