<?php

namespace Kodus\Mail\Test\Unit;

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
        $I->expectException(
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

        $I->expectException(
            InvalidArgumentException::class,
            function () use ($message) {
                $message->setHTML("\xc3\x28");
            }
        );
    }
}
