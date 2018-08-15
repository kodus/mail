<?php

namespace Kodus\Mail\Test\Unit;

use Kodus\Mail\MailService;
use Kodus\Mail\Message;
use RuntimeException;

/**
 * Mock Message Service that always fails
 */
class TotallyIncompetentMessageService implements MailService
{
    public function send(Message $message): void
    {
        throw new RuntimeException("OMG! everything is broken!");
    }
}
