<?php

declare(strict_types=1);

namespace Tests\Tempest\Architecture;

use Attribute;
use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

class ArchitectureTest
{
    public function test_validation_rules_implement_rule_interface(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Tempest\Validation\Rules'))
            ->shouldImplement()
            ->classes(Selector::classname(\Tempest\Validation\Rule::class));
    }

    public function test_validation_rules_are_attributes(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Tempest\Validation\Rules'))
            ->shouldApplyAttribute()
            ->classes(Selector::classname(Attribute::class));
    }

    public function test_validation_rules_are_final(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Tempest\Validation\Rules'))
            ->shouldBeFinal();
    }

    public function test_validation_rules_are_readonly(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Tempest\Validation\Rules'))
            ->shouldBeReadonly();
    }

    public function test_unit_tests_do_not_rely_on_application_classes(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Tests\Tempest\Unit'))
            ->shouldNotDependOn()
            ->classes(
                Selector::inNamespace('Tests\Tempest\Integration'),
                Selector::inNamespace('Tempest\Testing'),
            );
    }

    public function test_integration_tests_do_not_rely_on_unit_test_fixtures(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Tests\Tempest\Integration'))
            ->shouldNotDependOn()
            ->classes(
                Selector::inNamespace('Tests\Tempest\Unit'),
            );
    }
}
