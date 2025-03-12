<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rules\Email;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsFloat;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Rules\NotNull;
use Tempest\Validation\Tests\Fixtures\ObjectToBeValidated;
use Tempest\Validation\Tests\Fixtures\ObjectWithBoolProp;
use Tempest\Validation\Tests\Fixtures\ObjectWithFloatProp;
use Tempest\Validation\Tests\Fixtures\ObjectWithIntProp;
use Tempest\Validation\Tests\Fixtures\ObjectWithSkipValidation;
use Tempest\Validation\Tests\Fixtures\ObjectWithStringProperty;
use Tempest\Validation\Tests\Fixtures\ValidateObjectA;
use Tempest\Validation\Validator;

/**
 * @internal
 */
final class ValidatorTest extends TestCase
{
    public function test_validate(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validator();

        $validator->validateObject(new ObjectToBeValidated(name: 'a'));
    }

    public function test_validate_value(): void
    {
        $failingRules = new Validator()->validateValue('a', [new Email()]);

        $this->assertCount(1, $failingRules);
    }

    public function test_closure_fails_with_false_response(): void
    {
        $failingRules = new Validator()->validateValue('a', function (mixed $_) {
            return false;
        });

        $this->assertCount(1, $failingRules);
    }

    public function test_closure_fails_with_string_response(): void
    {
        $failingRules = new Validator()->validateValue('a', function (mixed $_) {
            return 'I expected b';
        });

        $this->assertCount(1, $failingRules);
        $this->assertSame('I expected b', $failingRules[0]->message());
    }

    public function test_closure_passes_with_null_response(): void
    {
        $validator = new Validator();
        $validator->validateValue('a', function (mixed $_) {
            return null;
        });

        $this->expectNotToPerformAssertions();
    }

    public function test_closure_passes_with_true_response(): void
    {
        $validator = new Validator();
        $validator->validateValue('a', function (mixed $_) {
            return true;
        });

        $this->expectNotToPerformAssertions();
    }

    public function test_closure_passes(): void
    {
        $validator = new Validator();

        $validator->validateValue('a', function (mixed $value) {
            return $value === 'a';
        });

        $validator->validateValue('a', function (mixed $value) {
            if ($value === 'a') {
                return true;
            }

            return false;
        });

        $this->expectNotToPerformAssertions();
    }

    public function test_nested_property_validation(): void
    {
        $validator = new Validator();

        $class = new ClassReflector(ValidateObjectA::class);

        $failingRules = $validator->validateValuesForClass($class, []);

        $this->assertCount(2, $failingRules);
        $this->assertInstanceOf(NotNull::class, $failingRules['b'][0]);
        $this->assertInstanceOf(NotNull::class, $failingRules['title'][0]);
        $this->assertInstanceOf(IsString::class, $failingRules['title'][1]);

        $failingRules = $validator->validateValuesForClass($class, [
            'b' => [
                'name' => '',
            ],
        ]);

        $this->assertArrayNotHasKey('b', $failingRules);
        $this->assertCount(1, $failingRules['b.c']);
        $this->assertCount(1, $failingRules['b.name']);
        $this->assertCount(2, $failingRules['b.age']);

        $failingRules = $validator->validateValuesForClass($class, [
            'b' => [
                'c' => [
                    'name' => '',
                ],
            ],
        ]);

        $this->assertArrayNotHasKey('b.c', $failingRules);
        $this->assertCount(1, $failingRules['b.c.name']);

        $failingRules = $validator->validateValuesForClass($class, [
            'title' => 'test',
            'b' => [
                'name' => 'test',
                'age' => 1,
                'c' => [
                    'name' => 'test',
                ],
            ],
        ]);

        $this->assertEmpty($failingRules);
    }

    public function test_nested_property_validation_with_dotted_keys(): void
    {
        $validator = new Validator();

        $class = new ClassReflector(ValidateObjectA::class);

        $failingRules = $validator->validateValuesForClass($class, [
            'title' => 'test',
            'b.name' => 'test',
            'b.age' => 1,
            'b.c.name' => 'test',
        ]);

        $this->assertEmpty($failingRules);

        $failingRules = $validator->validateValuesForClass($class, [
            'title' => 'test',
            'b.age' => 1,
        ]);

        $this->assertCount(2, $failingRules);
    }

    public function test_validation_infers_string_rule_from_property_type(): void
    {
        $failingRules = new Validator()->validateValuesForClass(ObjectWithStringProperty::class, ['prop' => (object) []]);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsString::class, $failingRules['prop'][0]);
    }

    public function test_validation_infers_int_rule_from_property_type(): void
    {
        $failingRules = new Validator()->validateValuesForClass(ObjectWithIntProp::class, ['prop' => 'a']);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsInteger::class, $failingRules['prop'][0]);
    }

    public function test_validation_infers_float_rule_from_property_type(): void
    {
        $failingRules = new Validator()->validateValuesForClass(ObjectWithFloatProp::class, ['prop' => 'a']);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsFloat::class, $failingRules['prop'][0]);
    }

    public function test_validation_infers_bool_rule_from_property_type(): void
    {
        $failingRules = new Validator()->validateValuesForClass(ObjectWithBoolProp::class, ['prop' => 'a']);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsBoolean::class, $failingRules['prop'][0]);
    }

    public function test_validation_infers_not_null_from_property_type(): void
    {
        $failingRules = new Validator()->validateValuesForClass(ObjectWithStringProperty::class, ['prop' => null]);

        $this->assertCount(2, $failingRules['prop']);
        $this->assertInstanceOf(NotNull::class, $failingRules['prop'][0]);
    }

    public function test_skip_validation_attribute(): void
    {
        $failingRules = new Validator()->validateValuesForClass(ObjectWithSkipValidation::class, []);

        $this->assertEmpty($failingRules);
    }
}
