<?php

namespace Kodus\Mail\SMTP\Authenticator;

use Kodus\Mail\SMTP\SMTPAuthenticator;
use Kodus\Mail\SMTP\SMTPClient;

/**
 * TODO finish this untested, partial implementation
 *
 * @see https://github.com/kodus/mail/issues/1
 *
 * @see https://tools.ietf.org/html/rfc7628
 */
class OAuthAuthenticator implements SMTPAuthenticator
{
    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $token;

    public function __construct(string $user, string $host, string $port, string $token)
    {
        $this->user = $user;
        $this->host = $host;
        $this->port = $port;
        $this->token = $token;
    }

    public function authenticate(SMTPClient $client): void
    {
        // NOTE: I don't know if any of this is correct or not - it was ported from somewhere else

        $auth_str = sprintf("n,a=%s,%shost=%s%sport=%s%sauth=Bearer %s%s%s",
            $this->user,
            chr(1),
            $this->host,
            chr(1),
            $this->port,
            chr(1),
            $this->token,
            chr(1),
            chr(1)
        );

        $auth_str = base64_encode($auth_str);

        $client->sendCommand("AUTH OAUTHBEARER {$auth_str}", "235");
    }
}
