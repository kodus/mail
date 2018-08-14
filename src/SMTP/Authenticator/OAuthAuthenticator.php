<?php

namespace Kodus\Mail\SMTP\Authenticator;

use Kodus\Mail\SMTP\SMTPClient;

/**
 * TODO finish this untested, partial implementation
 *
 * @see https://github.com/kodus/mail/issues/1
 *
 * @see https://tools.ietf.org/html/rfc7628
 */
class OAuthAuthenticator
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

    /**
     * @param string $user
     * @param string $host
     * @param string $port
     * @param string $token
     */
    public function __construct($user, $host, $port, $token)
    {
        $this->user = $user;
        $this->host = $host;
        $this->port = $port;
        $this->token = $token;
    }

    public function authenticate(SMTPClient $client)
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
