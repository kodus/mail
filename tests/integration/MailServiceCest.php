<?php

namespace Kodus\Mail\Test\Integration;

use IntegrationTester;
use Kodus\Mail\Address;
use Kodus\Mail\Message;
use Kodus\Mail\SMTP\SMTPMailService;

class MailServiceCest
{
    /**
     * In this test, we connect using a plain socket and plain login authentication.
     */
    public function sendMail(IntegrationTester $I)
    {
        $client_domain = "localhost";

        $service = new SMTPMailService(
            $I->createSocketConnector(), $I->createLoginAuthenticator(), $client_domain
        );

        $message = new Message(
            new Address("to-foo@test.org"),
            new Address("from-bar@test.org"),
            "Hello, Bar!",
            "Hey Bar!\n\nIt's been a long time!\n\nHow you been, bro?\n\n"
        );

        $service->send($message);
    }
}
