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

        [$headerString, $body] = explode(PHP_EOL, $html, 2);

        $email = $this->makeEmail($headerString, $body);

        $this->sendEmail($email);
    }

    public function sendEmail(Email $email): void
    {
        if ($email->from === null) {
            throw new InvalidFromAddress();
        }

        ld($email);
    }

    private function makeEmail(string $headerString, string $body): Email
    {
        $parsedHeaders = [];

        preg_match_all('/(?<header>\w+)="(?<value>.*?)"/', $headerString, $headers);

        foreach ($headers[0] as $i => $line) {
            $header = $headers['header'][$i];
            $value = $headers['value'][$i];

            $parsedHeaders[$header] = $value;
        }

        $parsedHeaders['attachments'] = explode(',', $parsedHeaders['attachments'] ?? '');

        $data = [
            ...$parsedHeaders,
            'body' => $body,
        ];

        return new Email(...$data);
    }
}
