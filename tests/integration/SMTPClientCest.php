<?php

namespace Kodus\Mail\Test\Integration;

use IntegrationTester;
use Kodus\Mail\Test\MockLogger;

class SMTPClientCest
{
    /**
     * In this test, we use the SMTP Client at a low level, without involving the
     * Mail Service or MIME Writer - we connect using a plain socket without authentication.
     */
    public function sendSMTPMessage(IntegrationTester $I)
    {
        $connector = $I->createSocketConnector();

        $client = $connector->connect("localhost");

        $logger = new MockLogger();

        $client->setLogger($logger);

        $quoted_body = quoted_printable_encode("Hey, Bar!\r\n\r\nIt's me! Foo!\r\n\r\nHow you been man?\r\n\r\n.\r\n\r\n.foo!\r\n\r\nhehehe :-)\r\n\r\n");

        $mime_message = <<<EOT
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?utf-8?q?Rasmus =C3=A5h Schultz?= <blip@test.org>
From: blub@test.org
Subject: =?UTF-8?Q?Hey, Rasmus! I like =C3=86=C3=98=C3=85=C3=A6=C3=B8=C3=A5!?=
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}
EOT;

        $client->sendMail(
            "from-foo@test.org",
            ["to-bar@test.org"],
            function ($resource) use ($mime_message) {
                fwrite($resource, $mime_message);
            }
        );

        $expected = [
            'S: MAIL FROM:<from-foo@test.org>',
            '/R: 250.*/',
            'S: RCPT TO:<to-bar@test.org>',
            '/R: 250.*/',
            'S: DATA',
            '/R: 354.*/',
            "S: \r\n.",
            '/R: 250.*/',
        ];

        $records = $logger->records;

        foreach ($expected as $index => $entry) {
            if (substr($entry, 0, 1) === "/") {
                $I->assertRegExp($entry, $records[$index]);
            } else {
                $I->assertSame($entry, $records[$index]);
            }
        }
    }
}
