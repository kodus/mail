<?php

namespace Kodus\Mail\Test\Unit;

use Kodus\Mail\Address;
use Kodus\Mail\MailServiceLogger;
use Kodus\Mail\Message;
use Kodus\Mail\Passive\PassiveMailService;
use Kodus\Mail\Test\MockLogger;
use RuntimeException;
use UnitTester;

class MailServiceLoggerCest
{
    public function logSuccessfulSendAttempt(UnitTester $I)
    {
        $logger = new MockLogger();

        $service = new MailServiceLogger(
            $logger,
            new PassiveMailService()
        );

        $service->send(new Message(
            new Address("foo@test.org", "Mister Foo"),
            new Address("bar@test.org"),
            "Greetings, Friend!",
            "How you been, bro?"
        ));

        $I->assertSame(
            ['message sent via Kodus\Mail\Passive\PassiveMailService to: Mister Foo <foo@test.org> from: <bar@test.org> with subject: Greetings, Friend!'],
            $logger->records
        );
    }

    public function logFailedSendAttempt(UnitTester $I)
    {
        $logger = new MockLogger();

        $service = new MailServiceLogger(
            $logger,
            new TotallyIncompetentMessageService()
        );

        $message = new Message(
            new Address("foo@test.org", "Mister Foo"),
            new Address("bar@test.org"),
            "Greetings, Friend!",
            "How you been, bro?"
        );

        try {
            $service->send($message);
        } catch (RuntimeException $e) {
            // expected
        }

        $I->assertSame(
            ['ERROR: message not sent [RuntimeException: OMG! everything is broken!] via Kodus\Mail\Test\Unit\TotallyIncompetentMessageService to: Mister Foo <foo@test.org> from: <bar@test.org> with subject: Greetings, Friend!'],
            $logger->records
        );
    }
}
