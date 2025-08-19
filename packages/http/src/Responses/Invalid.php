<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Support\Json;
use Tempest\Validation\Rule;
use Tempest\Validation\Validator;

use function Tempest\get;
use function Tempest\Support\arr;

final class Invalid implements Response
{
    use IsResponse;

    public Validator $validator {
        get => get(Validator::class);
    }

    private ?string $errorBag;

    public function __construct(
        Request $request,
        /** @var \Tempest\Validation\Rule[][] $failingRules */
        array $failingRules = [],
        ?string $errorBag = null,
    ) {
        $this->errorBag = $errorBag;
        if ($referer = $request->headers['referer'] ?? null) {
            $this->addHeader('Location', $referer);
            $this->status = Status::FOUND;
        } else {
            $this->status = Status::BAD_REQUEST;
        }

        $session = get(Session::class);
        $session->flashValidationErrors($failingRules, $this->errorBag);
        $session->flashOriginalValues($request->body, $this->errorBag);
        $this->addHeader(
            'x-validation',
            Json\encode(
                arr($failingRules)->map(fn (array $failingRulesForField) => arr($failingRulesForField)->map(
                    fn (Rule $rule) => $this->validator->getErrorMessage($rule),
                )->toArray())->toArray(),
            ),
        );

        if ($this->errorBag !== null && $this->errorBag !== Session::DEFAULT_ERROR_BAG) {
            $this->addHeader('x-validation-bag', $this->errorBag);
        }
    }

    public function withErrorBag(string $bagName): self
    {
        $this->errorBag = $bagName;

        return $this;
    }
}
