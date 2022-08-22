<?php

namespace Kodus\Mail\Test\Unit;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Kodus\Mail\Address;
use Kodus\Mail\Header;
use Kodus\Mail\Message;
use UnitTester;

/**
 * NOTE: other message behavior not covered by this test is covered by the `MIMEWriter` test
 */
class MessageCest
{
    public function setHeader(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("foo@bar.org"),
            "Hello, Bob",
            null
        );

        $message->addHeader("X-Foo", "foo");

        $I->assertEquals([new Header("X-Foo", "foo")], $message->getHeaders());

        $message->addHeader("X-FoO", "bar");

        $I->assertEquals([new Header("X-Foo", "foo"), new Header("X-FoO", "bar")], $message->getHeaders(), "can append headers with same name");

        $message->setHeader("X-FOO", "baz");

        $I->assertEquals([new Header("X-FOO", "baz")], $message->getHeaders(), "can overwrite headers case-insensitively");
    }

    public function rejectInvalidUTF8MessageBody(UnitTester $I)
    {
        $I->expectThrowable(
            InvalidArgumentException::class,
            function () {
                $message = new Message(
                    new Address("foo@test.org"),
                    new Address("bar@test.org"),
                    "La la la",
                    "\xc3\x28"
                );
            }
        );

        $message = new Message(
            new Address("foo@test.org"),
            new Address("bar@test.org"),
            "La la",
            "Blu blu"
        );

        $I->expectThrowable(
            InvalidArgumentException::class,
            function () use ($message) {
                $message->setHTML("\xc3\x28");
            }
        );
    }

    public function setDate(UnitTester $I)
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("foo@bar.org"),
            "Hello, Bob",
            null
        );

        // try setting a Date from a string in two different system timezones:

        date_default_timezone_set("Europe/Copenhagen");

        $message->setDate("Thu, 15 Sep 2016 17:20:54");

        $I->assertSame("Thu, 15 Sep 2016 17:20:54 +0200", $message->getDate()->format("r"));

        date_default_timezone_set("UTC");

        $message->setDate("Thu, 15 Sep 2016 17:20:54");

        $I->assertSame("Thu, 15 Sep 2016 17:20:54 +0000", $message->getDate()->format("r"));

        // try setting a Date from an UNIX timestamp in two different system timezones:

        date_default_timezone_set("Europe/Copenhagen");

        $message->setDate(1473952854);

        $I->assertSame("Thu, 15 Sep 2016 17:20:54 +0200", $message->getDate()->format("r"));

        date_default_timezone_set("UTC");

        $message->setDate(1473952854);

        $I->assertSame("Thu, 15 Sep 2016 15:20:54 +0000", $message->getDate()->format("r"));

        // try setting a Date from a DateTime instance:

        $date = new DateTime("@" . strtotime("Thu, 15 Sep 2016 17:20:54 +0200"));

        $date->setTimezone(new DateTimeZone("Europe/Copenhagen"));

        $message->setDate($date);

        $I->assertSame("Thu, 15 Sep 2016 17:20:54 +0200", $message->getDate()->format("r"));
    }
}
