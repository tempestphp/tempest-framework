<?php

declare(strict_types=1);

namespace Tempest\Testing\BypassMock;

use ParseError;
use php_user_filter;

final class Filter extends php_user_filter
{
    public const NAME = 'bypass.mock';

    private mixed $bucket;
    private string $data;
    private array $tokens;

    public function onCreate(): bool
    {
        $this->data = '';
        $this->tokens = Bypass::getTokens();

        return true;
    }

    public function filter($in, $out, &$consumed, $closing): int
    {
        $consumed = 0;

        // read data from $in and set data properties
        while($bucket = stream_bucket_make_writeable($in)) {
            $this->data .= $bucket->data;
            $this->bucket = $bucket;
        }

        if (! $closing) {
            return PSFS_FEED_ME;
        }

        $consumed += strlen($this->data);

        // filter out tokens
        $filter = $this->modifyCode($this->data);

        $this->bucket->data = $filter;
        $this->bucket->datalen = strlen($this->data);

        if(! empty($this->bucket->data)) {
            stream_bucket_append($out, $this->bucket);
        }

        return PSFS_PASS_ON;
    }

    private function modifyCode(string $code): string
    {
        foreach ($this->tokens as $text) {
            if (stripos($code, $text) !== false) {
                return $this->removeTokens($code);
            }
        }

        return $code;
    }

    private function removeTokens(string $code): string
    {
        try {
            $tokens = token_get_all($code, TOKEN_PARSE);
        } catch (ParseError $e) {
            return $code;
        }

        $code = '';
        foreach ($tokens as $token) {
            $code .= is_array($token)
                ? (isset($this->tokens[$token[0]]) ? '' : $token[1])
                : $token;
        }

        return $code;
    }
}
