<?php

namespace Tests\Tempest\Integration\Validator;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\Locale;
use Tempest\Intl\Translator;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\Rules;
use Tempest\Validation\TranslationKey;
use Tempest\Validation\Validator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class TranslationKeyTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function translates_property_key(): void
    {
        $this->container->get(Catalog::class)->add(Locale::default(), 'validation_error.has_length.book_title', 'The length of the title of the book is invalid.');
        $validator = $this->container->get(Validator::class);

        try {
            $validator->validateObject(new ValidatableObject(title: 'foo'));
            $this->fail('Expected `ValidationFailed` exception was not thrown.');
        } catch (ValidationFailed $validationFailed) {
            $this->assertArrayHasKey('title', $validationFailed->failingRules);
            $this->assertSame('book_title', $validationFailed->failingRules['title'][0]->key);
            $this->assertSame('foo', $validationFailed->failingRules['title'][0]->value);
            $this->assertNull($validationFailed->failingRules['title'][0]->field);
            $this->assertSame('The length of the title of the book is invalid.', $validator->getErrorMessage($validationFailed->failingRules['title'][0]));
        }
    }
}

final class ValidatableObject
{
    public function __construct(
        #[Rules\HasLength(min: 5, max: 50)]
        #[TranslationKey('book_title')]
        public string $title,
    ) {}
}
