<?php

namespace Kodus\Mail\Test\Unit;

use Kodus\Mail\Address;
use Kodus\Mail\Message;
use Kodus\Mail\MIMEWriter;
use UnitTester;

class MIMEWriterCest
{
    const TEXT_BODY                  = "It's me! Rasmus!\n\nI love danish characters, look: æøåÆØÅ! whoa!\n\nTake care, friend.";
    const TEXT_BODY_QUOTED_PRINTABLE = "It's me! Rasmus!\n\nI love danish characters, look: =C3=A6=C3=B8=C3=A5=C3=86=C3=98=C3=85! whoa!\n\nTake care, friend.";

    const HTML_BODY                  = "<strong>It's me! Rasmus!</strong>\n\nI love danish characters, look: æøåÆØÅ! whoa!\n\nTake care, friend.";
    const HTML_BODY_QUOTED_PRINTABLE = "<strong>It's me! Rasmus!</strong>\n\nI love danish characters, look: =C3=A6=C3=B8=C3=A5=C3=86=C3=98=C3=85! whoa!\n\nTake care, friend.";

    public function writePlainTextMessage(UnitTester $I)
    {
        $message = new Message(
            new Address("rasc@jfmedier.dk", "Rasmus åh Schultz"),
            new Address("rasmus@mindplay.dk"),
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $quoted_body = self::TEXT_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?utf-8?Q?Rasmus =C3=A5h Schultz <rasc@jfmedier.dk>?=
From: rasmus@mindplay.dk
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeHTMLMessage(UnitTester $I)
    {
        $message = new Message(
            new Address("rasc@jfmedier.dk", "Rasmus åh Schultz"),
            new Address("rasmus@mindplay.dk"),
            "Hey, Rasmus!",
            null,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $quoted_body = self::HTML_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?utf-8?Q?Rasmus =C3=A5h Schultz <rasc@jfmedier.dk>?=
From: rasmus@mindplay.dk
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeTextAndHTMLMessage(UnitTester $I)
    {
        $message = new Message(
            new Address("rasc@jfmedier.dk", "Rasmus Schultz"),
            new Address("rasmus@mindplay.dk"),
            "Hey, Rasmus!",
            self::TEXT_BODY,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $quoted_text = self::TEXT_BODY_QUOTED_PRINTABLE;
        $quoted_html = self::HTML_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: Rasmus Schultz <rasc@jfmedier.dk>
From: rasmus@mindplay.dk
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary="++++alternative-aaafaf7c286e771e9bd71eacae9f26ae36c9c650++++"

--++++alternative-aaafaf7c286e771e9bd71eacae9f26ae36c9c650++++
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_text}
--++++alternative-aaafaf7c286e771e9bd71eacae9f26ae36c9c650++++
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_html}
--++++alternative-aaafaf7c286e771e9bd71eacae9f26ae36c9c650++++--

MIME;


//        echo $this->toMIME($message);die();


        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    /**
     * @param Message $message
     *
     * @return string
     */
    private function toMIME(Message $message)
    {
        $temp = fopen("php://temp", "rw+");

        $writer = new MockMIMEWriter($temp);

        $writer->writeMessage($message);

        rewind($temp);

        $mime = stream_get_contents($temp);

        fclose($temp);

        return $mime;
    }
}
