<?php

namespace Kodus\Mail\SMTP\Authenticator;

use Kodus\Mail\SMTP\SMTPAuthenticator;
use Kodus\Mail\SMTP\SMTPClient;

/**
 * You can use this `Authenticator` with SMTP servers that do not require authentication.
 */
class NoAuthenticator implements SMTPAuthenticator
{
    public function authenticate(SMTPClient $client): void
    {
        // yep, this method does nothing :-)
    }
}
