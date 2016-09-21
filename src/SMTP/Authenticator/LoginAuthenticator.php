<?php

namespace Kodus\Mail\SMTP\Authenticator;

use Kodus\Mail\SMTP\SMTPAuthenticator;
use Kodus\Mail\SMTP\SMTPClient;

/**
 * Plain `AUTH LOGIN` SMTP Authenticator
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
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate(SMTPClient $client)
    {
        $client->writeCommand("AUTH LOGIN", "334");
        $client->writeCommand(base64_encode($this->username), "334");
        $client->writeCommand(base64_encode($this->password), "235");
    }
}
