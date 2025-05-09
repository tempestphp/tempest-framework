<?php

namespace Tempest\Mailer;

use Tempest\View\ViewRenderer;
use function Tempest\view;

final class Mailer
{
    public function __construct(
        private MailConfig $config,
        private ViewRenderer $renderer,
    ) {}

    public function send(string $path, ...$params): void
    {
        $html = $this->renderer->render(view($path, ...$params));

        [$header, $body] = explode(PHP_EOL, $html, 2);

        $email = $this->makeEmail($header, $body);

        $this->sendEmail($email);
    }

    public function sendEmail(Email $email): void
    {
        if ($email->from === null) {
            throw new InvalidFromAddress();
        }

        ld($email);
    }

    private function makeEmail(string $header, string $body): Email
    {
        $parsedHeaders = [];

        $headers = explode(';', $header);

        foreach ($headers as $header) {
            [$name, $value] = explode(':', $header);

            $parsedHeaders[trim($name)] = trim($value);
        }

        $data = [
            ...$parsedHeaders,
            'body' => $body,
        ];

        return new Email(...$data);
    }
}