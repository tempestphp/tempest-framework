<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\DeclareItem;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt;
use PhpParser\Token;
use Tempest\Generation\Php\Exceptions\ClassWasMissing;
use Tempest\Support\Filesystem;

/**
 * A PHP file that is either created from scratch or loaded from existing code.
 * Code that is loaded keeps its formatting, except for the parts that are changed.
 */
final class PhpFile
{
    private static ?Printer $printer = null;

    private bool $simplifyImplements = true;

    private bool $simplifyNamesInBodies = true;

    /**
     * @param Stmt[] $stmts
     * @param Stmt[]|null $originalStmts
     * @param Token[]|null $tokens
     * @param array<string, true> $importsUsedOnLoad
     */
    private function __construct(
        private array $stmts,
        private readonly ?array $originalStmts = null,
        private readonly ?array $tokens = null,
        private readonly array $importsUsedOnLoad = [],
    ) {}

    public static function create(?string $namespace = null): self
    {
        return new self($namespace ? [new Stmt\Namespace_(new Name($namespace))] : []);
    }

    public static function fromCode(string $code): self
    {
        [$originalStmts, $tokens] = SourceParser::parse($code);

        $cloner = new Cloner();
        $stmts = $cloner->cloneStatements($originalStmts);

        return new self($stmts, $originalStmts, $tokens, $cloner->importKeys);
    }

    public static function fromPath(string $path): self
    {
        return self::fromCode(Filesystem\read_file($path));
    }

    public function getNamespace(): ?string
    {
        return $this->findNamespace()?->name?->toString();
    }

    public function setNamespace(string $namespace): self
    {
        $existing = $this->findNamespace();

        if ($existing !== null) {
            $existing->name = new Name($namespace);

            return $this;
        }

        $declares = array_filter($this->stmts, static fn (Stmt $stmt) => $stmt instanceof Stmt\Declare_);
        $stmts = array_filter($this->stmts, static fn (Stmt $stmt) => ! $stmt instanceof Stmt\Declare_);

        $this->stmts = [...$declares, new Stmt\Namespace_(new Name($namespace), array_values($stmts))];

        return $this;
    }

    public function hasStrictTypes(): bool
    {
        $declaration = $this->findStrictTypesDeclaration();

        return $declaration?->value instanceof Int_ && $declaration->value->value === 1;
    }

    public function setStrictTypes(bool $strictTypes = true): self
    {
        $declaration = $this->findStrictTypesDeclaration();

        if ($strictTypes && $declaration !== null) {
            $declaration->value = new Int_(1);
        } elseif ($strictTypes) {
            $position = $this->hasComment() ? 1 : 0;

            array_splice($this->stmts, $position, 0, [
                new Stmt\Declare_([new DeclareItem('strict_types', new Int_(1))]),
                Printer::blankLine(),
            ]);
        } elseif ($declaration !== null) {
            $this->removeDeclaration($declaration);
        }

        return $this;
    }

    public function setComment(?string $comment): self
    {
        if ($this->hasComment()) {
            array_shift($this->stmts);
        }

        if ($comment !== null) {
            array_unshift($this->stmts, new Stmt\Nop(['comments' => [DocComment::from($comment)]]));
        }

        return $this;
    }

    /**
     * Imports a class, using the given alias to refer to it.
     */
    public function addUse(string $name, ?string $alias = null): self
    {
        $stmts = &$this->body();

        $imports = Imports::from($stmts);
        $imports->add(Stmt\Use_::TYPE_NORMAL, ltrim($name, '\\'), $alias);
        $imports->insertInto($stmts);

        return $this;
    }

    public function hasClass(?string $name = null): bool
    {
        return $this->findClass($name) !== null;
    }

    public function getClass(?string $name = null): PhpClass
    {
        return new PhpClass($this->findClass($name) ?? throw new ClassWasMissing($name));
    }

    public function addClass(string $name): PhpClass
    {
        $class = new Stmt\Class_($name);
        $stmts = &$this->body();

        array_push($stmts, ...$stmts === [] ? [$class] : [Printer::blankLine(), $class]);

        return new PhpClass($class);
    }

    /**
     * Whether fully qualified names of implemented interfaces are imported.
     */
    public function simplifyImplements(bool $simplify = true): self
    {
        $this->simplifyImplements = $simplify;

        return $this;
    }

    /**
     * Whether fully qualified names in method bodies are imported.
     */
    public function simplifyNamesInBodies(bool $simplify = true): self
    {
        $this->simplifyNamesInBodies = $simplify;

        return $this;
    }

    public function print(): string
    {
        $this->resolveNames();

        self::$printer ??= new Printer();

        $originalStmts = $this->originalStmts;
        $tokens = $this->tokens;

        if ($originalStmts !== null && $tokens !== null) {
            return self::$printer->printChangedFile($this->stmts, $originalStmts, $tokens);
        }

        return self::$printer->printNewFile($this->stmts);
    }

    private function resolveNames(): void
    {
        $imports = Imports::from($this->body());

        $simplifier = new NameSimplifier(
            namespace: $this->getNamespace(),
            imports: $imports,
            declaredClasses: $this->declaredClasses(),
            simplifyImplements: $this->simplifyImplements,
            simplifyBodies: $this->simplifyNamesInBodies,
        );

        $simplifier->simplify($this->stmts);

        $stmts = &$this->body();

        $imports->insertInto($stmts);

        if (Imports::hasUnused($stmts, $simplifier->usedImports)) {
            Imports::removeUnused($stmts, $this->importsUsedOnLoad, $simplifier->usedImports, $this->comments());
        }
    }

    /**
     * @return Stmt[]
     */
    private function &body(): array
    {
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof Stmt\Namespace_) {
                return $stmt->stmts;
            }
        }

        return $this->stmts;
    }

    private function findNamespace(): ?Stmt\Namespace_
    {
        return array_find($this->stmts, static fn (Stmt $stmt) => $stmt instanceof Stmt\Namespace_);
    }

    private function findClass(?string $name): ?Stmt\Class_
    {
        foreach ($this->body() as $stmt) {
            if (! $stmt instanceof Stmt\Class_ || $stmt->name === null) {
                continue;
            }

            if ($name === null || $stmt->name->toLowerString() === strtolower($name)) {
                return $stmt;
            }
        }

        return null;
    }

    /**
     * @return array<string, true>
     */
    private function declaredClasses(): array
    {
        $classes = [];

        foreach ($this->body() as $stmt) {
            if (! $stmt instanceof Stmt\ClassLike || $stmt->name === null) {
                continue;
            }

            $classes[$stmt->name->toLowerString()] = true;
        }

        return $classes;
    }

    private function findStrictTypesDeclaration(): ?DeclareItem
    {
        foreach ($this->stmts as $stmt) {
            if (! $stmt instanceof Stmt\Declare_) {
                continue;
            }

            foreach ($stmt->declares as $declare) {
                if ($declare->key->toLowerString() === 'strict_types') {
                    return $declare;
                }
            }
        }

        return null;
    }

    private function removeDeclaration(DeclareItem $declaration): void
    {
        foreach ($this->stmts as $index => $stmt) {
            if (! $stmt instanceof Stmt\Declare_ || ! in_array($declaration, $stmt->declares, strict: true)) {
                continue;
            }

            $stmt->declares = array_values(array_filter($stmt->declares, static fn (DeclareItem $item) => $item !== $declaration));

            if ($stmt->declares === []) {
                array_splice($this->stmts, $index, 1);
            }

            return;
        }
    }

    /**
     * The comments in the original code, and the ones that were added to the file, its classes, or method bodies.
     */
    private function comments(): string
    {
        $comments = '';

        foreach ($this->tokens ?? [] as $token) {
            if (! $token->is([T_COMMENT, T_DOC_COMMENT])) {
                continue;
            }

            $comments .= $token->text . "\n";
        }

        foreach ([...$this->stmts, ...$this->body()] as $stmt) {
            foreach ($stmt->getComments() as $comment) {
                $comments .= $comment->getText() . "\n";
            }

            if (! $stmt instanceof Stmt\Class_) {
                continue;
            }

            foreach ($stmt->getMethods() as $method) {
                $comments .= $method->getAttribute(MethodBody::ATTRIBUTE)?->comments();
            }
        }

        return $comments;
    }

    private function hasComment(): bool
    {
        $first = $this->stmts[0] ?? null;

        return $first instanceof Stmt\Nop && ! Printer::isBlankLine($first) && $first->getComments() !== [];
    }
}
