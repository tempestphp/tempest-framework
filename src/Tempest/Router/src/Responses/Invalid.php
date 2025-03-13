<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Session\Session;
use Tempest\Validation\Rule;
use function Tempest\Support\arr;

final class Invalid implements Response
{
    use IsResponse;

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
        $this->addHeader('x-validation', json_encode(arr($failingRules)
            ->map(fn (array $failingRulesForField) => arr($failingRulesForField)->map(fn (Rule $rule) => $rule->message()))
            ->toArray()
        ));
    }
}
