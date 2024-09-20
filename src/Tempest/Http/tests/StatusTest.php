<?php

declare(strict_types=1);

namespace Tempest\Http\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Status;

/**
 * @internal
 */
final class StatusTest extends TestCase
{
    private function descriptionToStatus(string $description): Status
    {
        $description = strtoupper(
            str_replace([' ', '-'], '_', $description)
        );

        return Status::{$description};
    }

    #[DataProvider('provide_status_code_cases')]
    public function test_status_code(int $code, string $description): void
    {
        $status = Status::code($code);

        $this->assertSame(
            $this->descriptionToStatus($description),
            $status
        );

        $this->assertSame($description, $status->description());

        if ($code >= 100 && $code < 200) {
            $this->assertTrue($status->isInformational());
            $this->assertFalse($status->isSuccessful());
            $this->assertFalse($status->isRedirect());
            $this->assertFalse($status->isClientError());
            $this->assertFalse($status->isServerError());
        }

        if ($code >= 200 && $code < 300) {
            $this->assertFalse($status->isInformational());
            $this->assertTrue($status->isSuccessful());
            $this->assertFalse($status->isRedirect());
            $this->assertFalse($status->isClientError());
            $this->assertFalse($status->isServerError());
        }

        if ($code >= 300 && $code < 400) {
            $this->assertFalse($status->isInformational());
            $this->assertFalse($status->isSuccessful());
            $this->assertTrue($status->isRedirect());
            $this->assertFalse($status->isClientError());
            $this->assertFalse($status->isServerError());
        }

        if ($code >= 400 && $code < 500) {
            $this->assertFalse($status->isInformational());
            $this->assertFalse($status->isSuccessful());
            $this->assertFalse($status->isRedirect());
            $this->assertTrue($status->isClientError());
            $this->assertFalse($status->isServerError());
        }

        if ($code >= 500 && $code < 600) {
            $this->assertFalse($status->isInformational());
            $this->assertFalse($status->isSuccessful());
            $this->assertFalse($status->isRedirect());
            $this->assertFalse($status->isClientError());
            $this->assertTrue($status->isServerError());
        }
    }

    public static function provide_status_code_cases(): iterable
    {
        return [
            [100, 'Continue'],
            [101, 'Switching Protocols'],
            [102, 'Processing'],
            [103, 'Early Hints'],

            [200, 'OK'],
            [201, 'Created'],
            [202, 'Accepted'],
            [203, 'Non-Authoritative Information'],
            [204, 'No Content'],
            [205, 'Reset Content'],
            [206, 'Partial Content'],
            [207, 'Multi-Status'],
            [208, 'Already Reported'],
            [226, 'IM Used'],

            [300, 'Multiple Choices'],
            [301, 'Moved Permanently'],
            [302, 'Found'],
            [303, 'See Other'],
            [304, 'Not Modified'],
            [305, 'Use Proxy'],
            [307, 'Temporary Redirect'],
            [308, 'Permanent Redirect'],

            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [402, 'Payment Required'],
            [403, 'Forbidden'],
            [404, 'Not Found'],
            [405, 'Method Not Allowed'],
            [406, 'Not Acceptable'],
            [407, 'Proxy Authentication Required'],
            [408, 'Request Timeout'],
            [409, 'Conflict'],
            [410, 'Gone'],
            [411, 'Length Required'],
            [412, 'Precondition Failed'],
            [413, 'Content Too Large'],
            [414, 'URI Too Long'],
            [415, 'Unsupported Media Type'],
            [416, 'Range Not Satisfiable'],
            [417, 'Expectation Failed'],
            [421, 'Misdirected Request'],
            [422, 'Unprocessable Content'],
            [423, 'Locked'],
            [424, 'Failed Dependency'],
            [425, 'Too Early'],
            [426, 'Upgrade Required'],
            [428, 'Precondition Required'],
            [429, 'Too Many Requests'],
            [431, 'Request Header Fields Too Large'],
            [451, 'Unavailable For Legal Reasons'],

            [500, 'Internal Server Error'],
            [501, 'Not Implemented'],
            [502, 'Bad Gateway'],
            [503, 'Service Unavailable'],
            [504, 'Gateway Timeout'],
            [505, 'HTTP Version Not Supported'],
            [506, 'Variant Also Negotiates'],
            [507, 'Insufficient Storage'],
            [508, 'Loop Detected'],
            [510, 'Not Extended'],
            [511, 'Network Authentication Required'],
        ];
    }
}
