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
     *
     * @return void
     */
    public function authenticate(SMTPClient $client);
}
