UPGRADING
=========

## 0.1.x to 0.2.x

`Message::getDate()` now returns `DateTimeInterface` - it previously returned a UNIX (integer) timestamp.

`Message::setDate()` still accepts UNIX timestamps and strings, and now also `DateTime` and `DateTimeImmutable`.
