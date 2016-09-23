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


## SMTP Service

The `SMTPMailService` implementation connects directly to an SMTP server via sockets.

TODO document SMTP service bootstrapping


## Development

To run the integration tests, you will need to set up a local SMTP server for testing.

On Windows, try [http://smtp4dev.codeplex.com/](SMTP4Dev).

On Linux, you will likely just have to set up an actual, local SMTP daemon. (Have fun with that!)

You may need to copy `integration.suite.dist.yml` to `integration.suite.yml` to customize the
SMTP host-name, port-number, etc.
