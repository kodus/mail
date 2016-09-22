<?php

namespace Kodus\Mail\SMTP;

class CodeException extends SMTPException
{
    public function __construct($expected, $received, $server_message)
    {
        parent::__construct("Expected return-code: {$expected}, received: {$received} ($server_message})");
    }
}
