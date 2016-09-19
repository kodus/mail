<?php

namespace Kodus\Mail\Test\Unit;

use InvalidArgumentException;
use Kodus\Mail\Address;
use RuntimeException;
use UnitTester;

class AddressCest
{
    public function rejectInvalidEmailAddress(UnitTester $I)
    {
        $I->assertException(
            InvalidArgumentException::class,
            function () {
                new Address("foo@bar");
            }
        );
    }

    public function preventNameInjection(UnitTester $I)
    {
        $I->assertException(
            RuntimeException::class,
            function () {
                new Address("foo@bar.org", "Foo\nBar");
            }
        );
    }
}
