<?php

declare(strict_types=1);

namespace Tempest\Http\Json;

use Psr\Http\Message\ServerRequestInterface;
use Tempest\Http\ContentType;

final readonly class JsonParserServerRequest
{
    public function __invoke(ServerRequestInterface $request): ServerRequestInterface
    {
        if (! in_array(ContentType::JSON->value, $request->getHeader(ContentType::HEADER))) {
            return $request;
        }

        $parsedBody = json_decode($request->getBody()->getContents(), true) ?? [];

        return $request->withParsedBody($parsedBody);
    }
}
