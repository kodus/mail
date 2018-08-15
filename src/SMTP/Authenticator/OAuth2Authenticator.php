<?php

namespace Kodus\Mail\SMTP\Authenticator;

use Kodus\Mail\SMTP\SMTPAuthenticator;
use Kodus\Mail\SMTP\SMTPClient;

/**
 * TODO finish this untested, partial implementation
 *
 * @see https://github.com/kodus/mail/issues/1
 *
 * @see https://tools.ietf.org/html/rfc6750
 */
class OAuth2Authenticator implements SMTPAuthenticator
{
    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $user
     * @param string $token
     */
    public function __construct(string $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function authenticate(SMTPClient $client): void
    {
        // NOTE: I don't know if any of this is correct or not - it was ported from somewhere else

        $auth_str = sprintf("user=%s%sauth=Bearer %s%s%s",
            $this->user,
            chr(1),
            $this->token,
            chr(1),
            chr(1)
        );

        $auth_str = base64_encode($auth_str);

        $client->sendCommand("AUTH XOAUTH2 {$auth_str}", "235");
    }
}
