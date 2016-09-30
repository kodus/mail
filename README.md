kodus/mail
==========

[Simple](#objectives) services for sending UTF-8 e-mail.


## Overview

Mail service implementations are abstracted behind the `MailService` interface, which depends
on the `Message` model to define the actual content.

To send mail, simply create an instance of `Message`, populate it with new instances of `Address`
and `Attachment` models by calling various public set/add-methods, then call `MailService::send()`.

The model permits any combination of UTF-8 text and HTML message bodies with any number of attachments.


## Usage

The following example assumes a `MailService` instance named `$service` is in scope - subsequent sections
will explain how to configure an e-mail service.

Here's an example of sending a plain-text e-mail with an attachment:

```php
$message = new Message(
    new Address($email),        // recipient
    new Address("me@test.org"), // sender
    "Hello, Person!",           // subject
    $text                       // plain text message body
);

$service->addAttachment(Attachment::fromFile(__DIR__ . "/awsum/unicorns.gif"));

$service->send($message);
```

Refer to the [`Message`](src/Message.php) model to learn about additional message properties.


## SMTP Mail Service

The `SMTPMailService` implementation connects directly to an SMTP server via sockets.

To bootstrap an SMTP mail service, you need to select a `Connector`, optionally an `Authenticator`
implementation - for example:

```php
$service = new SMTPMailService(
    new SocketConnector("localhost", 25),
    new LoginAuthenticator("user", "super_secret_pa55w0rd"),
    "test.org"
);
```

Note the third argument, which is the local host-name - this is used in the handshake (`EHLO`) message
send to the SMTP server when the client connects.

SMTP protocol-level logging is supported for diagnostic purposes, and can be enabled by injecting
a [PSR-3 Logger](http://www.php-fig.org/psr/psr-3/) into `SMTPClient` via `SMTPClient::setLogger()` -
this may be useful if you have connection issues, as it will write a `debug`-level entry for every
SMTP command sent, and every response received.


## Message Logging

Unlike SMTP protocol-level logging described above, a more general logging facility is also available -
this will write a single log-entry on success or failure to send a message, and is more generally
applicable to any `MailService` implementation, including of course the SMTP service.

To write a log-entry to a [PSR-3 Logger](http://www.php-fig.org/psr/psr-3/), use the `MailSeviceLogger`
implementation, which acts as a decorator for any other `MailService` implementation - for example:

```php
$service = new MailServiceLogger($psr_logger, new SMTPMailService(...));
```

See inline documentation for `MailServiceLogger` for details on customizing the message template and log-level.


## Passive Mail Service

A passive implementation of `MailService` is available, which does nothing.

You can use this during testing/development to disable any outgoing Messages.

This is typically most useful in conjunction with the `MailServiceLogger` described above, to bootstrap
a fake mail-service for testing and development, enabling you to see Messages that *would* have been sent.


## Development

To run the integration tests, you will need to set up a local SMTP server for testing.

On Windows, try [http://smtp4dev.codeplex.com/](SMTP4Dev).

On Linux, you will likely just have to set up an actual, local SMTP daemon. (Have fun with that!)

You may need to copy `integration.suite.dist.yml` to `integration.suite.yml` to customize the
SMTP host-name, port-number, etc.


<a name="objectives"></a>
## Objectives

This library has a number of stated design objectives:

  * Simplicity: UTF-8 is the only supported character-set, we make an effort not to reinvent the wheel
    (e.g. leveraging base64 and quoted-printable stream-filters) and the library has no external dependencies.
  * Using streams for efficiency and predictable memory usage - in particular, file attachments are
    encoded and sent in chunks, without buffering the entire message or juggling huge strings.
  * Separation of concerns - in particular, MIME encoding and SMTP transport concerns
    ("[dot-stuffing](https://tools.ietf.org/html/rfc5321#section-4.5.2)") are fully separated, which in
    turn is made possible by proper use of streams.
  * Using dependency injection (primarily constructor injection) everywhere.
  * Using abstractions (interfaces) both at the high level and for dependencies/components.
