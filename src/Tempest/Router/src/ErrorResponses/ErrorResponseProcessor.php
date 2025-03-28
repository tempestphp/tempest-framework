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
        if ($response->status->isClientError() || $response->status->isServerError()) {
            $response->setBody($this->renderErrorView($response->status));
        }

        return $response;
    }

    private function renderErrorView(Status $status): View
    {
        return new GenericView(__DIR__ . '/error.view.php', [
            'css' => $this->getCss(),
            'status' => $status->value,
            'title' => $status->description(),
            'message' => match ($status) {
                Status::INTERNAL_SERVER_ERROR => 'An unexpected server error occurred',
                Status::NOT_FOUND => 'This page could not be found on the server',
                Status::FORBIDDEN => 'You do not have permission to access this page',
                Status::UNAUTHORIZED => 'You must be authenticated in to access this page',
                default => null,
            },
        ]);
    }

    private function getCss(): string
    {
        return file_get_contents(__DIR__ . '/style.css');
    }
}
