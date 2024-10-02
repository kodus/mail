<?php

namespace Kodus\Mail\Test\Integration;

use IntegrationTester;
use Kodus\Mail\Address;
use Kodus\Mail\Message;
use Kodus\Mail\SMTP\Authenticator\NoAuthenticator;
use Kodus\Mail\SMTP\SMTPMailService;
use Kodus\Mail\Test\TestMessageFactory;

class MailServiceCest
{
    /**
     * In this test, we connect using a plain socket without login authentication, then
     * attempt to send every kind of test-message implemented by the Test Message Factory.
     */
    public function sendMail(IntegrationTester $I)
    {
        $factory = new TestMessageFactory();

        $client_domain = "localhost";

        $service = new SMTPMailService(
            $I->createSocketConnector(), new NoAuthenticator(), $client_domain
        );

        $messages = $factory->createAllMessageTypes();

        foreach ($messages as $type => $message) {
            $I->amGoingTo("send a message of this type: {$type}");

            $service->send($message);
        }
    }

    public function ensureDotStuffingHappensBeforeQuotePrintableEncode(IntegrationTester $I)
    {
        $text_body = <<<EOT
        Test mail 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890.
        More text.
        EOT;

        $service = new SMTPMailService(
            $I->createSocketConnector(), new NoAuthenticator(), "localhost"
        );

        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus! I like ÆØÅæøå!",
            $text_body,
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->setSender(new Address("someone-else@test.org"));

        $service->send($message);
    }
}
