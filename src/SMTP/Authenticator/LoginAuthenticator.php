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
        $code = $client->writeCommand("AUTH LOGIN", "334");

        if ($code !== '334') {
            throw new CodeException('334', $code, $client->getLastResult());
        }

        $code = $client->writeCommand(base64_encode($this->username));

        if ($code !== '334') {
            throw new CodeException('334', $code, $client->getLastResult());
        }

        $code = $client->writeCommand(base64_encode($this->password));

        if ($code !== '235') {
            throw new CodeException('235', $code, $client->getLastResult());
        }
    }
}
