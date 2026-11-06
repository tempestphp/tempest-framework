<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Tempest\Generation\Php\ClassGenerator;
use Tempest\Generation\Php\ClassManipulator;
use Tempest\Generation\Php\Exceptions\MethodWasMissing;
use Tempest\Generation\Php\Exceptions\ValueWasNotExportable;
use Tempest\Generation\Php\Literal;
use Tempest\Generation\Php\PhpFile;
use Tempest\Generation\Tests\Php\Fixtures\ClassFromFile;
use Tempest\Generation\Tests\Php\Fixtures\CreateMigrationsTable;
use Tempest\Generation\Tests\Php\Fixtures\Database\FakeQueryStatement;
use Tempest\Generation\Tests\Php\Fixtures\SampleNamespace\ExampleTrait;
use Tempest\Generation\Tests\Php\Fixtures\SampleNamespace\SampleEnum;
use Tempest\Generation\Tests\Php\Fixtures\TestAttribute;

/**
 * @internal
 */
final class PhpFileTest extends TestCase
{
    use MatchesSnapshots;

    #[Test]
    public function exports_attribute_arguments(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addClassAttribute(TestAttribute::class, [
            'name' => 'example',
            'case' => SampleEnum::FIRST,
            'values' => [1, -2, 'three' => true, 'four' => null],
            'expression' => new Literal('PHP_VERSION_ID >= 80500'),
        ]);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function rejects_values_that_cannot_be_exported(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');

        $this->expectException(ValueWasNotExportable::class);

        $class->addClassAttribute(TestAttribute::class, [new stdClass()]);
    }

    #[Test]
    public function keeps_multiline_strings_in_method_bodies_intact(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('render', body: <<<'PHP'
            $title = 'first line
        second line';

            return <<<HTML
                <h1>{$title}</h1>
                HTML;
        PHP, returnType: 'string');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function adds_members_to_loaded_classes_without_reformatting_them(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->addTrait(ExampleTrait::class);
        $class->addMethod('down', body: 'return null;', returnType: '?' . FakeQueryStatement::class);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function loads_classes_from_reflection(): void
    {
        $class = new ClassManipulator(new ReflectionClass(ClassFromFile::class));

        $this->assertSame(ClassFromFile::class, $class->getClassName());
    }

    #[Test]
    public function throws_when_adding_an_attribute_to_a_missing_method(): void
    {
        $class = new ClassManipulator(ClassFromFile::class);

        $this->expectException(MethodWasMissing::class);

        $class->addMethodAttribute('missing', TestAttribute::class);
    }

    #[Test]
    public function does_not_import_names_that_would_change_existing_references(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        final class Example
        {
            public function names(): array
            {
                return [Thing::class, \Vendor\Thing::class, count([]), \Vendor\count()];
            }
        }
        PHP);

        $code = $file->print();

        $this->assertStringContainsString('return [Thing::class, \Vendor\Thing::class, count([]), \Vendor\count()];', $code);
        $this->assertStringNotContainsString('use ', $code);
        $this->assertSame($code, $file->print());
    }

    #[Test]
    public function reuses_grouped_imports(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        use Vendor\{Thing, Other};

        final class Example {}
        PHP);

        $file->addUse('Vendor\Thing');

        $this->assertSame(1, substr_count($file->print(), 'Thing'));
    }

    #[Test]
    public function keeps_qualified_function_and_constant_names_working_when_the_namespace_changes(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace Old;

        final class Example
        {
            public function call(): mixed
            {
                return Sub\f(Sub\VALUE);
            }
        }
        PHP);

        $file->setNamespace('NewNamespace');

        $this->assertStringContainsString('return \Old\Sub\f(\Old\Sub\VALUE);', $file->print());
    }

    #[Test]
    public function keeps_imports_used_in_doc_comments(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        use Vendor\Thing;

        #[Thing]
        final class Example
        {
            /** @return Thing */
            public function thing(): mixed
            {
                return null;
            }
        }
        PHP);

        $file->getClass()->removeAttribute('Vendor\Thing');

        $this->assertStringContainsString('use Vendor\Thing;', $file->print());
    }

    #[Test]
    public function enables_strict_types_when_declared_as_disabled(): void
    {
        $file = PhpFile::fromCode("<?php\n\ndeclare(strict_types=0);\n\nnamespace App;\n");

        $this->assertFalse($file->hasStrictTypes());

        $file->setStrictTypes();

        $this->assertTrue($file->hasStrictTypes());
        $this->assertStringContainsString('declare(strict_types=1);', $file->print());
    }

    #[Test]
    public function exports_the_minimum_integer(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addClassAttribute(TestAttribute::class, [PHP_INT_MIN]);

        $code = $class->print();

        $this->assertStringContainsString('#[TestAttribute(-9223372036854775807 - 1)]', $code);
    }

    #[Test]
    public function keeps_strings_in_nested_interpolations_intact(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('render', body: "return \"{\$this->format(\"first\n\$value\")}\";", returnType: 'string');

        $this->assertStringContainsString("\"{\$this->format(\"first\n\$value\")}\"", $class->print());
    }

    #[Test]
    public function keeps_tab_indented_heredocs_valid(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('render', body: "return <<<TXT\n\thello\n\tTXT;", returnType: 'string');

        $code = $class->print();

        $this->assertStringContainsString("<<<TXT\n\thello\n\tTXT;", $code);
        $this->assertNotEmpty(token_get_all($code, TOKEN_PARSE));
    }

    #[Test]
    public function does_not_import_names_that_conflict_with_simplified_names(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        final class Example
        {
            public function names(): array
            {
                return [\App\Thing::class, \Vendor\Thing::class];
            }
        }
        PHP);

        $this->assertStringContainsString('return [Thing::class, \Vendor\Thing::class];', $file->print());
    }

    #[Test]
    public function does_not_import_functions_that_conflict_with_declared_functions(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        function target(): void {}

        final class Example
        {
            public function call(): void
            {
                \Vendor\target();
            }
        }
        PHP);

        $code = $file->print();

        $this->assertStringContainsString('\Vendor\target();', $code);
        $this->assertStringNotContainsString('use function', $code);
    }

    #[Test]
    public function keeps_quoted_strings_inside_heredoc_interpolations_intact(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('render', body: "return <<<TXT\n{\$this->format('first\nsecond')}\nTXT;", returnType: 'string');

        $this->assertStringContainsString("'first\nsecond'", $class->print());
    }

    #[Test]
    public function keeps_namespace_imports_used_in_doc_comments(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        use Vendor\Package as Models;

        #[Models\Attr]
        final class Example
        {
            /** @return Models\Thing */
            public function thing(): mixed
            {
                return null;
            }
        }
        PHP);

        $file->getClass()->removeAttribute('Vendor\Package\Attr');

        $this->assertStringContainsString('use Vendor\Package as Models;', $file->print());
    }

    #[Test]
    public function keeps_imports_used_in_added_doc_comments(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        use Vendor\Thing;

        #[Thing]
        final class Example {}
        PHP);

        $file->getClass()->removeAttribute('Vendor\Thing')->setComment('@extends Thing');

        $this->assertStringContainsString('use Vendor\Thing;', $file->print());
    }

    #[Test]
    public function reindents_heredocs_containing_quotes_consistently(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('render', body: "\$value = 'world';\nreturn <<<TXT\n{\$value}\"{\$value}\nafter\nTXT;", returnType: 'string');

        $this->assertStringContainsString("        return <<<TXT\n        {\$value}\"{\$value}\n        after\n        TXT;", $class->print());
    }

    #[Test]
    public function keeps_strings_nested_in_heredoc_interpolations_intact(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('render', body: "return <<<TXT\n{\$this->format(\"outer{\$this->format(\"first\n\$value\")}\")}\nTXT;", returnType: 'string');

        $this->assertStringContainsString("\"first\n\$value\"", $class->print());
    }

    #[Test]
    public function replaces_bodies_of_loaded_methods_with_fully_qualified_signatures(): void
    {
        $file = PhpFile::fromCode(
            "<?php\n\nnamespace App;\n\nfinal class Example\n{\n    public function make(): \\Vendor\\Result\n    {\n        return new \\Vendor\\Result();\n    }\n}\n",
        );
        $file->getClass()->getMethod('make')->setBody('return null;');

        $code = $file->print();

        $this->assertStringContainsString("public function make(): Result\n    {\n        return null;\n    }", $code);
        $this->assertNotEmpty(token_get_all($code, TOKEN_PARSE));
    }

    #[Test]
    public function keeps_global_functions_and_constants_when_moving_code_into_a_namespace(): void
    {
        $file = PhpFile::fromCode("<?php\n\nfinal class Example\n{\n    public function answer(): mixed\n    {\n        return answer(VALUE, true);\n    }\n}\n");
        $file->setNamespace('Changed');

        $this->assertStringContainsString('return \\answer(\\VALUE, true);', $file->print());
    }

    #[Test]
    public function keeps_imports_used_in_comments_of_added_method_bodies(): void
    {
        $file = PhpFile::fromCode(<<<'PHP'
        <?php

        namespace App;

        use Other\Marker;

        final class Example
        {
            #[Marker]
            public function mark(): void {}
        }
        PHP);

        $file->getClass()->getMethod('mark')->removeAttribute('Other\\Marker')->setBody("/** @var Marker \$marker */\n\$marker = null;");

        $this->assertStringContainsString('use Other\\Marker;', $file->print());
    }

    #[Test]
    public function simplifies_names_in_classes_nested_in_added_method_bodies(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('make', body: 'return new class { public function result(): \\Vendor\\Result { return new \\Vendor\\Result(); } };', returnType: 'object');

        $code = $class->print();

        $this->assertStringContainsString('use Vendor\\Result;', $code);
        $this->assertStringContainsString('public function result(): Result { return new Result(); }', $code);
    }

    #[Test]
    public function keeps_whitespace_only_lines_in_heredocs(): void
    {
        $class = new ClassGenerator('Example', namespace: 'App');
        $class->addMethod('render', body: "return <<<TEXT\na\n  \nb\nTEXT;", returnType: 'string');

        $this->assertStringContainsString("        return <<<TEXT\n        a\n          \n        b\n        TEXT;", $class->print());
    }
}
