UPGRADING
=========

## 1.0.0

This release requires PHP 7.1 or later - APIs have not changed since 0.2.x, but static type-hints
have been added, so you may need to add type-hints to custom implementations of any interfaces defined
by this package.

## 0.1.x to 0.2.x

`Message::getDate()` now returns `DateTimeInterface` - it previously returned a UNIX (integer) timestamp.

`Message::setDate()` still accepts UNIX timestamps and strings, and now also `DateTime` and `DateTimeImmutable`.
