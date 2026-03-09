<?php

namespace Tempest\Intl\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Intl\MessageFormat\Parser\MessageFormatParser;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\Matcher;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexMessage;
use Tempest\Intl\MessageFormat\Parser\Node\Declaration\InputDeclaration;
use Tempest\Intl\MessageFormat\Parser\Node\Declaration\LocalDeclaration;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\VariableExpression;
use Tempest\Intl\MessageFormat\Parser\Node\Literal\Literal;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Pattern;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Text;
use Tempest\Intl\MessageFormat\Parser\Node\SimpleMessage;

final class ParserTest extends TestCase
{
    public function test_simple(): void
    {
        $ast = new MessageFormatParser('Hello, world!')->parse();

        $this->assertInstanceOf(SimpleMessage::class, $ast);
        $this->assertInstanceOf(Pattern::class, $ast->pattern);
        $this->assertInstanceOf(Text::class, $ast->pattern->elements[0]);
        $this->assertSame('Hello, world!', $ast->pattern->elements[0]->value);
    }

    public function test_local_declaration(): void
    {
        /** @var ComplexMessage $ast */
        $ast = new MessageFormatParser(<<<'MF2'
        .local $time = {$launch_date :datetime style=|medium|}
        Launch time: {$time}
        MF2)->parse();

        $this->assertInstanceOf(ComplexMessage::class, $ast);
        $this->assertInstanceOf(Text::class, $ast->pattern->elements[0]);
        $this->assertInstanceOf(VariableExpression::class, $ast->pattern->elements[1]);
        $this->assertInstanceOf(LocalDeclaration::class, $ast->declarations[0]);

        $expression = $ast->declarations[0]->expression;
        $this->assertInstanceOf(VariableExpression::class, $expression);

        $this->assertSame('time', $ast->declarations[0]->variable->name->name);
        $this->assertSame('datetime', $ast->declarations[0]->expression->function->identifier->name);
        $this->assertSame('style', $ast->declarations[0]->expression->function->options[0]->identifier->name);
        $this->assertSame('medium', $ast->declarations[0]->expression->function->options[0]->value->value);
        $this->assertSame('launch_date', $expression->variable->name->name);
    }

    public function test_input_declaration(): void
    {
        /** @var ComplexMessage $ast */
        $ast = new MessageFormatParser(<<<'MF2'
        .input {$numDays :number select=exact}
        .match $numDays
        1  {{{$numDays} one}}
        2  {{{$numDays} two}}
        3 {{{$numDays} three}}
        MF2)->parse();

        $this->assertInstanceOf(ComplexMessage::class, $ast);
        $this->assertInstanceOf(InputDeclaration::class, $ast->declarations[0]);
        $this->assertSame('numDays', $ast->declarations[0]->expression->variable->name->name);

        $this->assertInstanceOf(Matcher::class, $ast->body);

        $firstVariant = $ast->body->variants[0]->pattern->pattern;
        $this->assertInstanceOf(VariableExpression::class, $firstVariant->elements[0]);
        $this->assertInstanceOf(Text::class, $firstVariant->elements[1]);
        $this->assertSame('numDays', $firstVariant->elements[0]->variable->name->name);
        $this->assertSame(' one', $firstVariant->elements[1]->value);

        $secondVariant = $ast->body->variants[1]->pattern->pattern;
        $this->assertInstanceOf(VariableExpression::class, $secondVariant->elements[0]);
        $this->assertInstanceOf(Text::class, $secondVariant->elements[1]);
        $this->assertSame('numDays', $secondVariant->elements[0]->variable->name->name);
        $this->assertSame(' two', $secondVariant->elements[1]->value);

        $thirdVariant = $ast->body->variants[2]->pattern->pattern;
        $this->assertInstanceOf(VariableExpression::class, $thirdVariant->elements[0]);
        $this->assertInstanceOf(Text::class, $thirdVariant->elements[1]);
        $this->assertSame('numDays', $thirdVariant->elements[0]->variable->name->name);
        $this->assertSame(' three', $thirdVariant->elements[1]->value);
    }

    public function test_function_with_option_quoted_literal(): void
    {
        $ast = new MessageFormatParser(<<<'MF2'
        Today is {$today :datetime pattern=|yyyy/MM/dd|}.
        MF2)->parse();

        $this->assertInstanceOf(SimpleMessage::class, $ast);
        $this->assertInstanceOf(VariableExpression::class, $ast->pattern->elements[1]);

        $function = $ast->pattern->elements[1]->function;
        $this->assertNotNull($function);

        $optionValue = $function->options[0]->value;
        $this->assertInstanceOf(Literal::class, $optionValue);
        $this->assertSame('yyyy/MM/dd', $optionValue->value);
    }
}
