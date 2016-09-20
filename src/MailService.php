<?php

namespace Kodus\Mail;

/**
 * This simple interface permits us to abstract from how Messages are sent.
 */
interface MailService
{
    /**
     * @param Message $message
     *
     * @return void
     */
    public function send(Message $message);
}
