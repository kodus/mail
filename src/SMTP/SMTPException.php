<?php

namespace Kodus\Mail\SMTP;

use Exception;

/**
 * General exception thrown by the SMTP Client for various error-conditions.
 */
class SMTPException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
