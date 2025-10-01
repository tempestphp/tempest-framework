<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Intl\Translator;
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

    public function __construct(
        Request $request,
        /** @var \Tempest\Validation\Rule[][] $failingRules */
        array $failingRules = [],
    ) {
        if ($referer = $request->headers['referer'] ?? null) {
            $this->addHeader('Location', $referer);
            $this->status = Status::FOUND;
        } else {
            $this->status = Status::BAD_REQUEST;
        }

        $this->flash(Session::VALIDATION_ERRORS, $failingRules);
        $this->flash(Session::ORIGINAL_VALUES, $request->body);
        $this->addHeader(
            'x-validation',
            Json\encode(
                arr($failingRules)->map(
                    fn (array $failingRulesForField) => arr($failingRulesForField)->map(
                        fn (Rule $rule) => $this->validator->getErrorMessage($rule),
                    )->toArray(),
                )->toArray(),
            ),
        );
    }
}
