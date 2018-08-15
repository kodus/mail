<?php

namespace Kodus\Mail\SMTP\Authenticator;

use Kodus\Mail\SMTP\SMTPAuthenticator;
use Kodus\Mail\SMTP\SMTPClient;

/**
 * Use this `Authenticator` with SMTP servers that require plain login authentication.
 */
class LoginAuthenticator implements SMTPAuthenticator
{
    /**
     * @var string SMTP username
     */
    protected $username;

    /**
     * @var string SMTP password
     */
    protected $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate(SMTPClient $client): void
    {
        $client->sendCommand("AUTH LOGIN", "334");
        $client->sendCommand(base64_encode($this->username), "334");
        $client->sendCommand(base64_encode($this->password), "235");
    }
}
