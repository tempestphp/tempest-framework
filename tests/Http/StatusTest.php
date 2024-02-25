<?php

declare(strict_types=1);

use Tempest\Http\Status;
use Tests\Tempest\TestCase;

uses(TestCase::class);

function descriptionToStatus(string $description): Status
{
	$description = strtoupper(
		str_replace([' ', '-'], '_', $description)
	);

	return Status::{$description};
}

test('status code', function (int $code, string $description) {
	$status = Status::code($code);

	expect($status)->toBe(descriptionToStatus($description));

	expect($status->description())->toBe($description);

	if ($code >= 100 && $code < 200) {
		expect($status->isInformational())->toBeTrue();
		expect($status->isSuccessful())->toBeFalse();
		expect($status->isRedirect())->toBeFalse();
		expect($status->isClientError())->toBeFalse();
		expect($status->isServerError())->toBeFalse();
	}

	if ($code >= 200 && $code < 300) {
		expect($status->isInformational())->toBeFalse();
		expect($status->isSuccessful())->toBeTrue();
		expect($status->isRedirect())->toBeFalse();
		expect($status->isClientError())->toBeFalse();
		expect($status->isServerError())->toBeFalse();
	}

	if ($code >= 300 && $code < 400) {
		expect($status->isInformational())->toBeFalse();
		expect($status->isSuccessful())->toBeFalse();
		expect($status->isRedirect())->toBeTrue();
		expect($status->isClientError())->toBeFalse();
		expect($status->isServerError())->toBeFalse();
	}

	if ($code >= 400 && $code < 500) {
		expect($status->isInformational())->toBeFalse();
		expect($status->isSuccessful())->toBeFalse();
		expect($status->isRedirect())->toBeFalse();
		expect($status->isClientError())->toBeTrue();
		expect($status->isServerError())->toBeFalse();
	}

	if ($code >= 500 && $code < 600) {
		expect($status->isInformational())->toBeFalse();
		expect($status->isSuccessful())->toBeFalse();
		expect($status->isRedirect())->toBeFalse();
		expect($status->isClientError())->toBeFalse();
		expect($status->isServerError())->toBeTrue();
	}
})->with([
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
]);
