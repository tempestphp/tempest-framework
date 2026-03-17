<?php

declare(strict_types=1);

namespace Tempest\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;

final class ImportClassNamesRector extends AbstractRector implements ConfigurableRectorInterface
{
    private ?string $currentNamespace = null;

    private ?string $currentFilePath = null;

    /** @var list<string> */
    private array $excludedClasses = [];

    public function __construct(
        private readonly NameImporter $nameImporter,
        private readonly UseImportsResolver $useImportsResolver,
    ) {
    }

    /**
     * @param array{importShortClasses?: bool, excludedClasses?: list<string>} $configuration
     */
    public function configure(array $configuration): void
    {
        SimpleParameterProvider::setParameter(
            Option::IMPORT_SHORT_CLASSES,
            $configuration['importShortClasses'] ?? true,
        );

        $this->excludedClasses = array_map(
            static fn (string $class): string => ltrim($class, '\\'),
            $configuration['excludedClasses'] ?? [],
        );
    }

    public function getNodeTypes(): array
    {
        return [FullyQualified::class];
    }

    public function refactor(Node $node): ?Name
    {
        if (! $node instanceof FullyQualified) {
            return null;
        }

        if ($node->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === true) {
            return null;
        }

        if ($node->getAttribute(AttributeKey::IS_CONSTFETCH_NAME) === true) {
            return null;
        }

        if ($this->isAlreadyShortInSource($node)) {
            return null;
        }

        if (in_array($node->toString(), $this->excludedClasses, true)) {
            return null;
        }

        $currentUses = $this->useImportsResolver->resolve();

        $imported = $this->nameImporter->importName($node, $this->file, $currentUses);

        if ($imported instanceof Name) {
            return $imported;
        }

        return $this->shortenSameNamespaceName($node, $currentUses);
    }

    /**
     * @param array<Use_|GroupUse> $currentUses
     */
    private function shortenSameNamespaceName(FullyQualified $node, array $currentUses): ?Name
    {
        $namespaceName = $this->resolveCurrentNamespace();

        if ($namespaceName === null) {
            return null;
        }

        $fqcn = $node->toString();
        $shortName = $node->getLast();
        $fqcnNamespace = substr($fqcn, 0, -(strlen($shortName) + 1));

        if ($fqcnNamespace !== $namespaceName) {
            return null;
        }

        if ($this->hasConflictingUseStatement($shortName, $fqcn, $currentUses)) {
            return null;
        }

        return new Name($shortName);
    }

    private function isAlreadyShortInSource(FullyQualified $node): bool
    {
        $startTokenPos = $node->getStartTokenPos();
        $oldTokens = $this->file->getOldTokens();
        if (! isset($oldTokens[$startTokenPos])) {
            return false;
        }
        $originalText = $oldTokens[$startTokenPos]->text;
        return ! str_contains($originalText, '\\');
    }

    private function resolveCurrentNamespace(): ?string
    {
        $filePath = $this->file->getFilePath();

        if ($this->currentFilePath === $filePath) {
            return $this->currentNamespace;
        }

        $this->currentFilePath = $filePath;
        $this->currentNamespace = null;

        $fileNode = $this->file->getFileNode();

        if ($fileNode === null) {
            return null;
        }

        $namespace = $fileNode->getNamespace();

        if ($namespace?->name !== null) {
            $this->currentNamespace = $namespace->name->toString();
        }

        return $this->currentNamespace;
    }

    /**
     * @param array<Use_|GroupUse> $currentUses
     */
    private function hasConflictingUseStatement(string $shortName, string $fqcn, array $currentUses): bool
    {
        foreach ($currentUses as $use) {
            foreach ($use->uses as $useUse) {
                $importedName = $use instanceof GroupUse
                    ? $use->prefix . '\\' . $useUse->name->toString()
                    : $useUse->name->toString();

                $alias = $useUse->alias?->toString() ?? $useUse->name->getLast();

                if ($alias === $shortName && $importedName !== $fqcn) {
                    return true;
                }
            }
        }

        return false;
    }
}
