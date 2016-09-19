<?php

namespace Kodus\Mail\Test\Unit;

use Kodus\Mail\Address;
use Kodus\Mail\Header;
use Kodus\Mail\Message;
use UnitTester;

/**
 * NOTE: other message behavior besides adding/setting headers is covered by the `MIMEWriter` test
 */
class MessageCest
{
    public function setHeader(UnitTester $I)
    {
        $message = new Message(
            new Address("rasc@jfmedier.dk"),
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
}
