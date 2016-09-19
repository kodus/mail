<?php

namespace Kodus\Mail\Test\Unit;

use Kodus\Mail\Address;
use Kodus\Mail\Attachment;
use Kodus\Mail\Message;
use UnitTester;

class MIMEWriterCest
{
    const TEXT_BODY                  = "It's me! Rasmus!\n\nI love danish characters, look: æøåÆØÅ! whoa!\n\nTake care, friend.";
    const TEXT_BODY_QUOTED_PRINTABLE = "It's me! Rasmus!\n\nI love danish characters, look: =C3=A6=C3=B8=C3=A5=C3=86=C3=98=C3=85! whoa!\n\nTake care, friend.";
    const TEXT_BODY_BASE64           = "SXQncyBtZSEgUmFzbXVzIQoKSSBsb3ZlIGRhbmlzaCBjaGFyYWN0ZXJzLCBsb29rOiDDpsO4w6XD\nhsOYw4UhIHdob2EhCgpUYWtlIGNhcmUsIGZyaWVuZC4=";

    const HTML_BODY                  = "<strong>It's me! Rasmus!</strong>\n\nI love danish characters, look: æøåÆØÅ! whoa!\n\nTake care, friend.";
    const HTML_BODY_QUOTED_PRINTABLE = "<strong>It's me! Rasmus!</strong>\n\nI love danish characters, look: =C3=A6=C3=B8=C3=A5=C3=86=C3=98=C3=85! whoa!\n\nTake care, friend.";

    public function writeTextMessage(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $quoted_body = self::TEXT_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?UTF-8?Q?Rasmus =C3=A5h Schultz?= <blip@test.org>
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeTextMessageWithAttachment(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addAttachment(new Attachment(file_get_contents(__DIR__ . "/kitten.jpg"), "kitten.jpg", "image/jpeg"));

        $encoded_attachment = file_get_contents(__DIR__ . "/kitten.base64.txt");

        $quoted_body = self::TEXT_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?UTF-8?Q?Rasmus =C3=A5h Schultz?= <blip@test.org>
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++"

This is a multipart message in MIME format.

--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: image/jpeg
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="kitten.jpg"

{$encoded_attachment}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++--

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeHTMLMessage(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            null,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $quoted_body = self::HTML_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?UTF-8?Q?Rasmus =C3=A5h Schultz?= <blip@test.org>
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeHTMLMessageWithAttachment(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            null,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addAttachment(new Attachment(file_get_contents(__DIR__ . "/kitten.jpg"), "kitten.jpg", "image/jpeg"));

        $encoded_attachment = file_get_contents(__DIR__ . "/kitten.base64.txt");

        $quoted_body = self::HTML_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?UTF-8?Q?Rasmus =C3=A5h Schultz?= <blip@test.org>
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++"

This is a multipart message in MIME format.

--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: image/jpeg
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="kitten.jpg"

{$encoded_attachment}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++--

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeTextAndHTMLMessage(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $quoted_text = self::TEXT_BODY_QUOTED_PRINTABLE;
        $quoted_html = self::HTML_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: Rasmus Schultz <blip@test.org>
From: blub@test.org
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

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeTextAndHTMLMessageWithAttachment(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addAttachment(Attachment::fromFile(__DIR__ . "/kitten.jpg"));

        $encoded_attachment = file_get_contents(__DIR__ . "/kitten.base64.txt");

        $quoted_text_body = self::TEXT_BODY_QUOTED_PRINTABLE;

        $quoted_html_body = self::HTML_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: =?UTF-8?Q?Rasmus =C3=A5h Schultz?= <blip@test.org>
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++"

This is a multipart message in MIME format.

--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: multipart/alternative; boundary="++++alternative-5870793b3a929ca762d3e15521a8ff2b1e382f08++++"

--++++alternative-5870793b3a929ca762d3e15521a8ff2b1e382f08++++
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

${quoted_text_body}
--++++alternative-5870793b3a929ca762d3e15521a8ff2b1e382f08++++
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_html_body}
--++++alternative-5870793b3a929ca762d3e15521a8ff2b1e382f08++++--
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: application/octet-stream
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="kitten.jpg"

{$encoded_attachment}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++--

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeMessageWithMultipleAttachments(UnitTester $I)
    {
        // NOTE: this test also covers use of the Attachment::fromFile() factory method
        //       as well as constructing an Attachment instance using string content

        $message = new Message(
            new Address("blip@test.org", "Rasmus Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addAttachment(Attachment::fromFile(__DIR__ . '/kitten.jpg'));

        $encoded_first_attachment = file_get_contents(__DIR__ . "/kitten.base64.txt");

        $message->addAttachment(new Attachment(self::TEXT_BODY, "hello.txt", "text/plain"));

        $encoded_second_attachment = self::TEXT_BODY_BASE64;

        $quoted_body = self::TEXT_BODY_QUOTED_PRINTABLE;

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: Rasmus Schultz <blip@test.org>
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++"

This is a multipart message in MIME format.

--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

{$quoted_body}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: application/octet-stream
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="kitten.jpg"

{$encoded_first_attachment}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++
Content-Type: text/plain
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="hello.txt"

{$encoded_second_attachment}
--++++mixed-03e3b4a304d78659f1a3c0a3cea31a4d73f3a426++++--

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeMessageWithMultipleRecipients(UnitTester $I)
    {
        $message = new Message(
            [
                new Address("blip@test.org", "Rasmus Schultz"),
                new Address("also-blip@test.org", "Also Rasmus Schultz")
            ],
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            "Hello!"
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: Rasmus Schultz <blip@test.org>, Also Rasmus Schultz <also-blip@test.org>
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

Hello!

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeMessageWithCCAndBCCRecipients(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            "Hello!"
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addCC(new Address("joe@test.org"));

        $message->addBCC(new Address("bob@test.org"));

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: blip@test.org
From: blub@test.org
Cc: joe@test.org
Bcc: bob@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

Hello!

MIME;

        $I->assertSame($expected_mime, $this->toMIME($message));
    }

    public function writeMessageWithCustomHeaders(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            "Hello!"
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addHeader("X-Custom-Header", "custom-value");

        $expected_mime = <<<MIME
Date: Thu, 15 Sep 2016 17:20:54 +0200
To: blip@test.org
From: blub@test.org
Subject: Hey, Rasmus!
MIME-Version: 1.0
X-Custom-Header: custom-value
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

Hello!

MIME;

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

//        echo "-----------------------------------\n{$mime}\n--------------------------------\n\n";

        return $mime;
    }
}
