<?php

namespace Kodus\Mail;

/**
 * This simple interface permits us to abstract from how Messages are sent.
 */
interface MailService
{
    /**
     * Send the message, by whatever means is defined by the implementation.
     *
     * Success is assumed (hence, there is no return status) but the implementation or
     * underlying transport could throw runtime-exceptions, which you may wish to handle.
     *
     * @param Message $message
     *
     * @return void
     */
    public function send(Message $message): void;
}
