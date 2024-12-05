<?php

declare(strict_types=1);

namespace Tempest\Mailer\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Mailer\Components\Address\Address;
use Tempest\Mailer\Components\Address\ImmutableAddressCollection;
use Tempest\Mailer\Email;
use Tempest\Mailer\EmailBuilder;

/**
 * @internal
 */
class EmailBuilderTest extends TestCase
{
    public function test_adding_recipients()
    {
        $builder = new EmailBuilder()
            ->to('jim.halpert@dundermifflinpaper.biz')
            ->to(new Address('dwight.schrute@dundermifflinpaper.biz'));

        $this->assertEqualsCanonicalizing(
            [
                new Address('jim.halpert@dundermifflinpaper.biz'),
                new Address('dwight.schrute@dundermifflinpaper.biz'),
            ],
            $builder->to->all(),
        );
    }

    public function test_adding_cc()
    {
        $builder = new EmailBuilder()
            ->cc('jim.halpert@dundermifflinpaper.biz')
            ->cc(new Address('michael.scott@dundermifflinpaper.biz'));

        $this->assertEqualsCanonicalizing(
            [
                new Address('jim.halpert@dundermifflinpaper.biz'),
                new Address('michael.scott@dundermifflinpaper.biz'),
            ],
            $builder->cc->all(),
        );
    }

    public function test_adding_bcc()
    {
        $builder = new EmailBuilder()
            ->bcc('michael.scott@dundermifflinpaper.biz')
            ->bcc(new Address('dwight.schrute@dundermifflinpaper.biz'));

        $this->assertEqualsCanonicalizing(
            [
                new Address('michael.scott@dundermifflinpaper.biz'),
                new Address('dwight.schrute@dundermifflinpaper.biz'),
            ],
            $builder->bcc->all(),
        );
    }

    public function test_adding_subject()
    {
        $builder = new EmailBuilder()->subject('Test Subject');

        $this->assertSame('Test Subject', $builder->subject);
    }

    public function test_making_an_email()
    {
        $email = new EmailBuilder()
            ->to('jim.halpert@dundermifflinpaper.biz')
            ->subject("You're fired!")
            ->make();

        $this->assertEquals(
            new Email(
                recipients: new ImmutableAddressCollection([
                    new Address('jim.halpert@dundermifflinpaper.biz'),
                ]),
                cc: new ImmutableAddressCollection([]),
                bcc: new ImmutableAddressCollection(),
                subject: 'You\'re fired!',
            ),
            $email,
        );
    }
}
