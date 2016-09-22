<?php

namespace Kodus\Mail\Test\Integration;

use IntegrationTester;
use Kodus\Mail\SMTP\Connector\SocketConnector;

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

        $quoted_body = quoted_printable_encode("Hey, Bar!\r\n\r\nIt's me! Foo!\r\n\r\nHow you been man?\r\n\r\n.\r\n\r\n.foo!\r\n\r\nhehehe :-)\r\n\r\n");

        $mime_message = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: blip@test.org
From: blub@test.org
Subject: Hey, Foo!
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}
MIME;

        $client->sendMail(
            "from-foo@test.org",
            ["to-bar@test.org"],
            function ($resource) use ($mime_message) {
                fwrite($resource, $mime_message);
            }
        );
    }
}
