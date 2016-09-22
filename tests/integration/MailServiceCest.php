<?php

namespace Kodus\Mail\Test\Integration;

use IntegrationTester;
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
}
