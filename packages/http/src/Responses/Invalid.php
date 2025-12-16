<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\SensitiveField;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr;
use Tempest\Support\Json;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Validator;

use function Tempest\get;

final class Invalid implements Response
{
    use IsResponse;

    public Validator $validator {
        get => get(Validator::class);
    }

    /**
     * @param class-string|null $targetClass
     */
    public function __construct(
        Request $request,
        /** @var \Tempest\Validation\FailingRule[][] $failingRules */
        array $failingRules = [],
        ?string $targetClass = null,
    ) {
        if ($referer = $request->headers['referer'] ?? null) {
            $this->addHeader('Location', $referer);
            $this->status = Status::FOUND;
        } else {
            $this->status = Status::UNPROCESSABLE_CONTENT;
        }

        $this->flash(Session::VALIDATION_ERRORS, $failingRules);
        $this->flash(Session::ORIGINAL_VALUES, $this->filterSensitiveFields($request, $targetClass));
        $this->addHeader('x-validation', value: Json\encode(
            Arr\map_iterable($failingRules, fn (array $failingRulesForField, string $field) => Arr\map_iterable(
                array: $failingRulesForField,
                map: fn (FailingRule $rule) => $this->validator->getErrorMessage($rule, $field),
            )),
        ));
    }

    /**
     * @param class-string|null $targetClass
     */
    private function filterSensitiveFields(Request $request, ?string $targetClass): array
    {
        $body = $request->body;

        if ($targetClass === null) {
            return $body;
        }

        $reflector = new ClassReflector($targetClass);

        foreach ($reflector->getPublicProperties() as $property) {
            if ($property->hasAttribute(SensitiveField::class)) {
                unset($body[$property->getName()]);
            }
        }

        return $body;
    }
}
