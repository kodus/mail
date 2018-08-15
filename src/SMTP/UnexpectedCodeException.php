<?php

namespace Kodus\Mail\SMTP;

/**
 * Exception thrown by the SMTP Client when the server responds with an unexpected status-code.
 */
class UnexpectedCodeException extends SMTPException
{
    /**
     * @param string $expected_code
     * @param string $unexpected_code
     * @param string $last_command
     * @param string $last_result
     */
    public function __construct(
        string $expected_code,
        string $unexpected_code,
        string $last_command,
        string $last_result
    )
    {
        parent::__construct(
            "Unexpected status code: {$unexpected_code} (expected: {$expected_code})\n"
            . "S: {$last_command}\n"
            . "R: {$last_result}"
        );
    }
}
