---
title: Mail
description: "Tempest provides a convenient layer built on top of Symfony's excellent mailer component so that you can send emails with ease."
---

## Getting started

Sending emails starts with picking an email transport. Tempest comes with built-in support for SMTP, Amazon SES, and Postmark; but it's trivial to add any other transport you'd like. We'll start with plain SMTP, and explain how to switch to other transports later.

By default, Tempest is configured to use SMTP mailing. You'll need to add these environment variables and the mailer will be ready for use: 

```dotenv
MAIL_SMTP_HOST=mail.my_provider.com
MAIL_SMTP_PORT=587
MAIL_SMTP_USERNAME=my_username@my_provider.com
MAIL_SMTP_PASSWORD=my_password_123
MAIL_SENDER_NAME=Brent
MAIL_SENDER_EMAIL=brendt@stitcher.io
```

Sending an email is done via the {b`\Tempest\Mail\Mailer`}, you can inject it anywhere you'd like:

```php
use Tempest\Mail\Mailer;
use Tempest\Mail\GenericEmail;
 
final class UserEventHandlers
{
    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    #[EventHandler]
    public function onCreated(UserCreated $userCreated): void
    {
        $this->mailer->send(new GenericEmail(
            subject: 'Welcome!',
            to: $userCreated->email,
            html: view(
                __DIR__ . '/mails/welcome.view.php', 
                user: $userCreated->user,
            ),
        ));
    }
}
```

Note that {b`\Tempest\Mail\GenericEmail`} is a default email implementation that can be used on the fly, but a more scalable approach would be to make individual classes for every email:

```php
use Tempest\Mail\Mailer;
use Tempest\Mail\GenericEmail;
 
final class UserEventHandlers
{
    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    #[EventHandler]
    public function onCreated(UserCreated $userCreated): void
    {
        $this->mailer->send(new WelcomeEmail($userCreated->user));
    }
}
```

Here's what that `WelcomeEmail` would look like:

```php
use Tempest\Mail\Email;
use Tempest\Mail\Envelope;
use Tempest\View\View;
use function Tempest\view;

final class WelcomeEmail implements Email
{
    public function __construct(
        private readonly User $user,
    ) {}

    public Envelope $envelope {
        get => new Envelope(
            subject: 'Welcome',
            to: $this->user->email,
        );
    }

    public string|View $html {
        get => view('welcome.view.php', user: $this->user);
    }
}
```

Note how {b`\Tempest\Mail\Envelope`} contains all meta information about an email. Here you can specify the subject and receiver, but also headers, bcc, cc, and more.

## Email content

In the previous examples, we assumed there to be a [view](/docs/essentials/views) attached to an email. Views are flexible since they can contain variable data like the user object, for example. In simple cases though, you might only want to send HTML without it being a view. In that case, you can pass in the HTML like so:

```php
use Tempest\Mail\Email;

final class WelcomeEmail implements Email
{
    // …
    
    public string|View $html {
        get => <<<HTML
        <h1>Thanks for joining!</h1>
        HTML;
    }
}
```

Whenever an email is sent, Tempest will automatically provide a text-only version of that email as well, which will be used by text-only email clients. The text is generated based on your HTML template (by stripping all the HTML tags). However, you also have the option to manually specify the text-only contents of an email, by implementing {b`Tempest\Mail\HasTextContent`}:

```php
use Tempest\Mail\Email;
use Tempest\View\View;
use Tempest\Mail\HasTextContent;

final class WelcomeEmail implements Email, HasTextContent
{
    // …
    
    public string|View|null $text = <<<TXT
    This is the text-only version of this email.
    TXT;
}
```

Note that you can _also_ use a view to render your text-only content. This is especially useful when you have lots of dynamic parts in your text content. Keep in mind that these kinds of views should not contain any HTML:

```php
use Tempest\Mail\Email;
use Tempest\View\View;
use Tempest\Mail\HasTextContent;

final class WelcomeEmail implements Email, HasTextContent
{
    // …
    
    public string|View|null $text {
        get => view('welcome-text.view.php', user: $this->user);
    }
}
```

```html welcome-text.view.php
Hello {{ $user->name }}

Please visit this link to activate your account: {{ $user->activationLink }}.

See you soon!

Tempest
```

## Attachments

If you want your email to have attachments, you can implement the {b`\Tempest\Mail\HasAttachments`} interface:

```php
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\HasAttachments;

final class WelcomeEmail implements Email, HasAttachments
{
    // …

    public array $attachments {
        get => [
            Attachment::fromFilesystem(__DIR__ . '/welcome.pdf')
        ];
    }
}
```

Creating attachments can be done in multiple ways:

- By referencing a file directly on the filesystem (as shown in the previous example);
- By using a [storage drive](/docs/features/file-storage): `Attachment::fromStorage($s3Storage, '/welcome.pdf')`;
- Or by manually passing a closure to a new attachment instance:

```php
use Tempest\Mail\Attachment;

$attachment = new Attachment(function () {
    return Pdf::createFromTemplate('user-pdf.pdf', user: $this->user);
});
```

## Other transports

As mentioned, Tempest has built-in support for SMTP, Amazon SES, and Postmark. It is however trivial to use a range of other transports as well. First let's talk about switching to one of the built-in transports. 

The first step in using any transport is to install the transport-specific driver. You can find a list of all supported transports on [Symfony's documentation](https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport). If we take Postmark as an example, you should install these two dependencies:

```
composer require symfony/postmark-mailer
composer require symfony/http-client
```

Next, create a new mail config file and return an instance of {b`Tempest\Mail\Transports\Postmark\PostmarkConfig`}:

```php app/mail.config.php
use Tempest\Mail\Transports\Postmark\PostmarkConfig;
use function Tempest\env;

return new PostmarkConfig(
    key: env('MAIL_POSTMARK_TOKEN'),
);
```

Note that the Postmark token is the token associated with your Postmark account. A good practice is to also provide a default sender:

```php app/mail.config.php
use Tempest\Mail\EmailAddress;
use Tempest\Mail\Transports\Postmark\PostmarkConfig;
use function Tempest\env;

$defaultSender = null;

if (env('MAIL_SENDER_NAME') && env('MAIL_SENDER_EMAIL')) {
    $defaultSender = new EmailAddress(
        email: env('MAIL_SENDER_EMAIL'),
        name: env('MAIL_SENDER_NAME'),
    );
}

return new PostmarkConfig(
    key: env('MAIL_POSTMARK_TOKEN'),
    defaultSender: $defaultSender,
);
```

Finally, make sure that all environment variables are correctly set, and you're done! Tempest's mailer will now route your emails via Postmark.

## Creating your own transports

While SMTP, Amazon SES, and Postmark are built in, there are a lot of [other transports available](https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport) as well. In order to use one of those, you must create a new config class, specifically for that transport. Here's an example of using Mailgun. First you require the Symfony driver:

```
composer require symfony/mailgun-mailer
```

Then you create a new config class, specifically for that transport:

```php
final class MailgunConfig implements MailerConfig, ProvidesDefaultSender
{
    public string $transport = MailgunApiTransport::class;

    public function __construct(
        public readonly EmailAddress $defaultSender,
        #[SensitiveParameter]
        private readonly string $key,
        #[SensitiveParameter]
        private readonly string $domain,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new MailgunTransportFactory()
            ->create(Dsn::fromString("mailgun+api://{$this->key}:{$this->domain}@default"));
    }
}
```

And finally, use it like so:

```php app/mail.config.php
return new MailgunConfig(
    defaultSender: $defaultSender,
    key: env('MAIL_MAILGUN_KEY'),
    domain: env('MAIL_MAILGUN_DOMAIN'),
);
```

## Testing

Any test class extending from {b`\Tempest\Framework\Testing\IntegrationTest`} will have the {b`\Tempest\Mail\Testing\MailTester`} available:

```php
public function test_welcome_mail()
{
    $this->mailer
        ->send(new WelcomeEmail($this->user))
        ->assertSentTo($this->user->email)
        ->assertAttached('welcome.pdf');
}
```

Note that mails sent within tests using the {b`\Tempest\Mail\Testing\MailTester`} will never be actually sent. Read more about testing [here](/docs/essentials/testing).
