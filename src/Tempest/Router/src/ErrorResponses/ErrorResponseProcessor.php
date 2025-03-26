<?php

namespace Tempest\Router\ErrorResponses;

use Tempest\Http\Status;
use Tempest\Router\Response;
use Tempest\Router\ResponseProcessor;
use Tempest\View\GenericView;
use Tempest\View\View;

final class ErrorResponseProcessor implements ResponseProcessor
{
    public function process(Response $response): Response
    {
        return match ($response->status) {
            Status::INTERNAL_SERVER_ERROR, Status::NOT_FOUND, Status::FORBIDDEN, Status::UNAUTHORIZED => $response->setBody($this->renderErrorView($response->status)),
            default => $response,
        };
    }

    private function renderErrorView(Status $status): View
    {
        return new GenericView(__DIR__ . '/error.view.php', [
            'css' => $this->getCss(),
            'status' => $status->value,
            'title' => $status->description(),
            'message' => match ($status) {
                Status::INTERNAL_SERVER_ERROR => 'An unexpected server error occurred',
                Status::NOT_FOUND => 'This page could not be found',
                Status::FORBIDDEN => 'You do not have permission to access this page',
                Status::UNAUTHORIZED => 'You must be authenticated in to access this page',
                default => $status->description(),
            },
        ]);
    }

    private function getCss(): string
    {
        return file_get_contents(__DIR__ . '/style.css');
    }
}
