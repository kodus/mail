<?php

namespace Kodus\Mail\SMTP;

use Exception;

class SMTPException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
