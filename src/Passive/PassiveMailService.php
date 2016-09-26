<?php

namespace Kodus\Mail\Passive;

use Kodus\Mail\MailService;
use Kodus\Mail\Message;

/**
 * You can use this implementation to disable sending of Messages for testing purposes.
 *
 * Note that this can be used with the `MailServiceLogger` to set up a logging-only mail service.
 */
class PassiveMailService implements MailService
{
    public function send(Message $message)
    {
        // yep, this does nothing :-)
    }
}
