kodus/mail
==========

Simple services for sending UTF-8 e-mail.


## Overview

Mail service implementations are abstracted behind the `MailService` interface, which depends
on the `Message` model to define the actual content.

To send mail, simply create an instance of `Message`, populate it with new instances of `Address`
and `Attachment` models by calling various public set/add-methods, then call `MailService::send()`.

The model permits any combination of UTF-8 text and HTML message bodies with any number of attachments.


## Usage

Components that need to send e-mail should depend on the `MailService` interface, via constructor
injection, which allows for injection of e.g. a mock or void mail service under test.

As an example, here is a naiive controller for a POST-request from an open e-mail form:

```php
use Kodus\Mail\MailService;
use Kodus\KodusView;

class PostMailForm extends Controller
{
    private $mail;
    private $view;

    public function __construct(MailService $mail, KodusView $view)
    {
        $this->mail = $mail;
        $this->view = $view;
    }

    public function run($text, $email)
    {
        $body = $this->view->capture(new MessageView($text, $email));

        $message = new Message(
            new Address($email),        // recipient
            new Address("me@test.org"), // sender
            "Hello, Person!",           // subject
            $body                       // plain text message body
        );

        $this->mail->send($message);

        return new ViewResult(new MailFormConfirmationPage());
    }
}
```

In this simple example, we use the view-service to render the message body, then compose a
message with the posted e-mail address in the "To" field, and then ship it off.


## SMTP Mail Service

The `SMTPMailService` implementation connects directly to an SMTP server via sockets.

To bootstrap an SMTP mail service, you need to select a `Connector`, optionally an `Authenticator`
implementation - for example:

```php
$service = new SMTPMailService(
    new SocketConnector("localhost", 25),
    new LoginAuthenticator("user", "super_secret_pa55w0rd"),
    "test.org"
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
