UPGRADING
=========

## 1.0.0

There are no breaking changes from 0.2.x, but this release requires PHP 7.0 or later.

## 0.1.x to 0.2.x

`Message::getDate()` now returns `DateTimeInterface` - it previously returned a UNIX (integer) timestamp.

`Message::setDate()` still accepts UNIX timestamps and strings, and now also `DateTime` and `DateTimeImmutable`.
