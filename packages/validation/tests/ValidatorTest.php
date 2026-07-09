<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\HasErrorMessage;
use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsEmail;
use Tempest\Validation\Rules\IsEnum;
use Tempest\Validation\Rules\IsFloat;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsNotNull;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Tests\Fixtures\ObjectToBeValidated;
use Tempest\Validation\Tests\Fixtures\ObjectWithBoolProp;
use Tempest\Validation\Tests\Fixtures\ObjectWithEnumProp;
use Tempest\Validation\Tests\Fixtures\ObjectWithFloatProp;
use Tempest\Validation\Tests\Fixtures\ObjectWithIntProp;
use Tempest\Validation\Tests\Fixtures\ObjectWithObjectProperty;
use Tempest\Validation\Tests\Fixtures\ObjectWithSkipValidation;
use Tempest\Validation\Tests\Fixtures\ObjectWithStringProperty;
use Tempest\Validation\Tests\Fixtures\ValidateObjectA;
use Tempest\Validation\Validator;

/**
 * @internal
 */
final class ValidatorTest extends TestCase
{
    private Validator $validator {
        get => new Validator(new NullTranslator());
    }

    public function test_validate(): void
    {
        try {
            $this->validator->validateObject(new ObjectToBeValidated(name: 'a'));
            $this->fail('Expected ValidationFailed to be thrown.');
        } catch (ValidationFailed $e) {
            $this->assertArrayHasKey('name', $e->failingRules);
            $this->assertCount(1, $e->failingRules['name']);
            $this->assertInstanceOf(HasLength::class, $e->failingRules['name'][0]->rule);
            $this->assertSame('name', $e->failingRules['name'][0]->field);
            $this->assertSame('a', $e->failingRules['name'][0]->value);
            $this->assertSame(ObjectToBeValidated::class, $e->targetClass);
        }
    }

    public function test_validate_passes_with_valid_object(): void
    {
        $this->validator->validateObject(new ObjectToBeValidated(name: 'ab'));

        $this->expectNotToPerformAssertions();
    }

    public function test_validate_value_for_property_sets_field_and_value(): void
    {
        $property = new ClassReflector(ValidateObjectA::class)->getProperty('title');

        $failingRules = $this->validator->validateValueForProperty($property, 123);

        $this->assertCount(1, $failingRules);
        $this->assertSame('title', $failingRules[0]->field);
        $this->assertSame(123, $failingRules[0]->value);
    }

    public function test_validate_value(): void
    {
        $failingRules = $this->validator->validateValue('a', [new IsEmail()]);

        $this->assertCount(1, $failingRules);
    }

    public function test_closure_fails_with_false_response(): void
    {
        $failingRules = $this->validator->validateValue('a', fn (mixed $_) => false);

        $this->assertCount(1, $failingRules);
    }

    public function test_closure_fails_with_string_response(): void
    {
        $failingRules = $this->validator->validateValue('a', fn (mixed $_) => 'I expected b');

        $rule = $failingRules[0]->rule;

        $this->assertCount(1, $failingRules);
        $this->assertInstanceOf(HasErrorMessage::class, $rule);
        $this->assertSame('I expected b', $rule->getErrorMessage());
    }

    public function test_get_error_message_uses_custom_error_message_from_failing_rule(): void
    {
        $failingRules = $this->validator->validateValue('a', fn (mixed $_) => 'I expected b');

        $this->assertSame('I expected b', $this->validator->getErrorMessage($failingRules[0]));
    }

    public function test_get_error_message_does_not_require_translator_for_custom_error_message(): void
    {
        $validator = new Validator();
        $failingRules = $validator->validateValue('a', fn (mixed $_) => 'I expected b');

        $this->assertSame('I expected b', $validator->getErrorMessage($failingRules[0]));
    }

    public function test_validation_failed_can_be_created_from_messages(): void
    {
        $validationFailed = ValidationFailed::withMessages([
            'credential' => 'Passkey not valid',
            'email' => [
                'Email is already taken',
                'Email domain is not allowed',
            ],
        ]);

        $this->assertSame(
            [
                'credential' => ['Passkey not valid'],
                'email' => ['Email is already taken', 'Email domain is not allowed'],
            ],
            $validationFailed->errorMessages,
        );
        $this->assertCount(1, $validationFailed->failingRules['credential']);
        $this->assertCount(2, $validationFailed->failingRules['email']);
        $this->assertSame('Passkey not valid', $this->validator->getErrorMessage($validationFailed->failingRules['credential'][0]));
        $this->assertSame('Email is already taken', $this->validator->getErrorMessage($validationFailed->failingRules['email'][0]));
        $this->assertSame('Email domain is not allowed', $this->validator->getErrorMessage($validationFailed->failingRules['email'][1]));
    }

    public function test_validation_failed_message_includes_error_messages(): void
    {
        $validationFailed = ValidationFailed::withMessages([
            'credential' => 'Passkey not valid',
            'email' => [
                'Email is already taken',
                'Email domain is not allowed',
            ],
        ]);

        $this->assertSame(
            implode(PHP_EOL, [
                'Validation failed.',
                '- credential: Passkey not valid',
                '- email: Email is already taken; Email domain is not allowed',
            ]),
            $validationFailed->getMessage(),
        );
    }

    public function test_validation_failed_message_omits_error_messages_when_empty(): void
    {
        $validationFailed = new ValidationFailed(failingRules: []);

        $this->assertSame('Validation failed.', $validationFailed->getMessage());
    }

    public function test_validation_failed_provides_error_messages_as_context(): void
    {
        $validationFailed = ValidationFailed::withMessages([
            'email' => 'Email is already taken',
        ]);

        $this->assertSame(['errors' => ['email' => ['Email is already taken']]], $validationFailed->context());
        $this->assertSame([], new ValidationFailed(failingRules: [])->context());
    }

    public function test_closure_passes_with_null_response(): void
    {
        $validator = $this->validator;
        $validator->validateValue('a', fn (mixed $_) => null);

        $this->expectNotToPerformAssertions();
    }

    public function test_closure_passes_with_true_response(): void
    {
        $validator = $this->validator;
        $validator->validateValue('a', fn (mixed $_) => true);

        $this->expectNotToPerformAssertions();
    }

    public function test_closure_passes(): void
    {
        $validator = $this->validator;

        $validator->validateValue('a', fn (mixed $value) => $value === 'a');

        $validator->validateValue('a', fn (mixed $value) => $value === 'a');

        $this->expectNotToPerformAssertions();
    }

    public function test_nested_property_validation(): void
    {
        $validator = $this->validator;

        $class = new ClassReflector(ValidateObjectA::class);

        $failingRules = $validator->validateValuesForClass($class, []);

        $this->assertCount(7, $failingRules);
        $this->assertInstanceOf(IsNotNull::class, $failingRules['b'][0]->rule);
        $this->assertInstanceOf(IsString::class, $failingRules['title'][0]->rule);

        $failingRules = $validator->validateValuesForClass($class, [
            'b' => [
                'name' => '',
            ],
        ]);

        $this->assertArrayNotHasKey('b', $failingRules);
        $this->assertCount(1, $failingRules['b.c']);
        $this->assertCount(1, $failingRules['b.name']);
        $this->assertCount(1, $failingRules['b.age']);

        $failingRules = $validator->validateValuesForClass($class, [
            'b' => [
                'c' => [
                    'name' => '',
                ],
            ],
        ]);

        $this->assertCount(1, $failingRules['b.c.name']);

        $failingRules = $validator->validateValuesForClass($class, [
            'title' => 'test',
            'b' => [
                'name' => 'test',
                'age' => 1,
                'c' => [
                    'name' => 'test',
                    'email' => 'brendt@stitcher.io',
                ],
            ],
        ]);

        $this->assertEmpty($failingRules);
    }

    public function test_nested_property_validation_with_dotted_keys(): void
    {
        $validator = $this->validator;

        $class = new ClassReflector(ValidateObjectA::class);

        $failingRules = $validator->validateValuesForClass($class, [
            'title' => 'test',
            'b.name' => 'test',
            'b.age' => 1,
            'b.c.name' => 'test',
            'b.c.email' => 'brendt@stitcher.io',
        ]);

        $this->assertEmpty($failingRules);

        $failingRules = $validator->validateValuesForClass($class, [
            'title' => 'test',
            'b.age' => 1,
        ]);

        $this->assertCount(4, $failingRules);
    }

    public function test_validation_infers_string_rule_from_property_type(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithStringProperty::class, ['prop' => (object) []]);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsString::class, $failingRules['prop'][0]->rule);
    }

    public function test_validation_infers_int_rule_from_property_type(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithIntProp::class, ['prop' => 'a']);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsInteger::class, $failingRules['prop'][0]->rule);
    }

    public function test_validation_infers_float_rule_from_property_type(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithFloatProp::class, ['prop' => 'a']);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsFloat::class, $failingRules['prop'][0]->rule);
    }

    public function test_validation_infers_bool_rule_from_property_type(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithBoolProp::class, ['prop' => 'a']);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsBoolean::class, $failingRules['prop'][0]->rule);
    }

    public function test_validation_infers_enum_rule_from_property_type(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithEnumProp::class, ['prop' => 'a']);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsEnum::class, $failingRules['prop'][0]->rule);
    }

    public function test_validation_infers_not_null_from_scalar_property_type(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithStringProperty::class, ['prop' => null]);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsString::class, $failingRules['prop'][0]->rule);
    }

    public function test_validation_infers_not_null_from_property_type(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithObjectProperty::class, ['prop' => null]);

        $this->assertCount(1, $failingRules['prop']);
        $this->assertInstanceOf(IsNotNull::class, $failingRules['prop'][0]->rule);
    }

    public function test_skip_validation_attribute(): void
    {
        $failingRules = $this->validator->validateValuesForClass(ObjectWithSkipValidation::class, []);

        $this->assertEmpty($failingRules);
    }

    public function test_validate_values_some_invalid(): void
    {
        $failingRules = $this->validator->validateValues(
            [
                'name' => '',
                'email' => 'invalid-email',
                'age' => 0,
            ],
            [
                'name' => [new IsString(), new IsNotNull()],
                'email' => [new IsEmail()],
                'age' => [new IsInteger(), new IsNotNull()],
            ],
        );

        $this->assertCount(1, $failingRules);
        $this->assertInstanceOf(IsEmail::class, $failingRules['email'][0]->rule);
    }

    public function test_validate_values_all_valid(): void
    {
        $failingRules = $this->validator->validateValues(
            [
                'name' => '',
                'email' => 'foo@bar.baz',
                'age' => 0,
            ],
            [
                'name' => [new IsString(), new IsNotNull()],
                'email' => [new IsEmail()],
                'age' => [new IsInteger(), new IsNotNull()],
            ],
        );

        $this->assertCount(0, $failingRules);
    }
}
