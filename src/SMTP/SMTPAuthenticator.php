<?php

namespace Kodus\Mail\SMTP;

/**
 * @see SMTPClient::__construct()
 */
interface SMTPAuthenticator
{
    /**
     * Authenticates with an SMTP server
     *
     * @param SMTPClient $client
     */
    public function authenticate(SMTPClient $client): void;
}
