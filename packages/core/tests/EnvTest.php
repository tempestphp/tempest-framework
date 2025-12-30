<?php

namespace Tempest\Core\Tests;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Core\EnvironmentVariableValidationFailed;
use Tempest\Intl\Catalog\GenericCatalog;
use Tempest\Intl\GenericTranslator;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;
use Tempest\Intl\Translator;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsNotNull;
use Tempest\Validation\Rules\IsNumeric;
use Tempest\Validation\Validator;

use function Tempest\env;

final class EnvTest extends TestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        if (! interface_exists(Translator::class)) {
            $this->markTestSkipped('`tempest/intl` is required for this test.');
        }

        if (! class_exists(Validator::class)) {
            $this->markTestSkipped('`tempest/validation` is required for this test.');
        }

        $container = new GenericContainer();
        $container->singleton(Translator::class, new GenericTranslator(
            config: new IntlConfig(currentLocale: Locale::ENGLISH, fallbackLocale: Locale::ENGLISH),
            catalog: new GenericCatalog([
                'en' => [
                    'validation_error' => [
                        'is_numeric' => '{{{$field} must be a numeric value}}',
                    ],
                ],
            ]),
            formatter: new MessageFormatter(),
        ));

        GenericContainer::setInstance($container);
    }

    #[Test]
    #[TestWith([null, null])]
    #[TestWith(['', null])]
    #[TestWith(['null', null])]
    #[TestWith([false, null])]
    #[TestWith(['FALSE', false])]
    #[TestWith(['false', false])]
    #[TestWith(['TRUE', true])]
    #[TestWith(['true', true])]
    #[TestWith(['foo', 'foo'])]
    #[TestWith(['FOO', 'FOO'])]
    #[TestWith([1, '1'])]
    public function basic(mixed $value, mixed $expected): void
    {
        putenv("_ENV_TESTING_KEY={$value}");

        $this->assertSame($expected, env('_ENV_TESTING_KEY'));
    }

    #[Test]
    #[TestWith([null, 'fallback', 'fallback'])]
    #[TestWith([false, 'fallback', 'fallback'])]
    #[TestWith(['', 'fallback', 'fallback'])]
    #[TestWith(['false', 'fallback', false])]
    #[TestWith(['true', 'fallback', true])]
    #[TestWith([false, '', ''])]
    #[TestWith([null, '', ''])]
    #[TestWith(['', '', ''])]
    #[TestWith([false, false, false])]
    #[TestWith([null, false, false])]
    #[TestWith(['', false, false])]
    public function default(mixed $value, mixed $default, mixed $expected): void
    {
        putenv("_ENV_TESTING_KEY={$value}");

        $this->assertSame($expected, env('_ENV_TESTING_KEY', default: $default));
    }

    #[Test]
    public function fails_with_failing_rules(): void
    {
        $this->expectException(EnvironmentVariableValidationFailed::class);
        $this->expectExceptionMessageMatches('*_ENV_TESTING_KEY must be a numeric value*');

        putenv('_ENV_TESTING_KEY=foo');
        env('_ENV_TESTING_KEY', rules: [new IsNumeric()]);
    }

    #[Test]
    #[TestWith([null, null])]
    #[TestWith(['', null])]
    #[TestWith([false, null])]
    public function default_taken_into_account(mixed $value, mixed $default): void
    {
        $this->expectException(EnvironmentVariableValidationFailed::class);

        putenv("_ENV_TESTING_KEY={$value}");
        env('_ENV_TESTING_KEY', default: $default, rules: [new IsNotNull()]);
    }

    #[Test]
    public function can_pass(): void
    {
        putenv('_ENV_TESTING_KEY=true');

        $this->assertSame(true, env('_ENV_TESTING_KEY', rules: [new IsBoolean()]));
    }
}
