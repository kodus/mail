<?php

namespace Kodus\Mail;

interface MailService
{
    public function send(Message $message);
}
