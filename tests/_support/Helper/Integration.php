<?php

namespace Helper;

use Kodus\Mail\SMTP\Authenticator\LoginAuthenticator;
use Kodus\Mail\SMTP\Connector\SecureSocketConnector;
use Kodus\Mail\SMTP\Connector\SocketConnector;

class Integration extends \Codeception\Module
{
    /**
     * @return \Kodus\Mail\SMTP\Connector\SocketConnector
     */
    public function createSocketConnector()
    {
        return new SocketConnector(
            $this->config["smtp_host"],
            $this->config["smtp_port"]
        );
    }

    /**
     * @return \Kodus\Mail\SMTP\Connector\SecureSocketConnector
     */
    public function createSecureSocketConnector()
    {
        return new SecureSocketConnector(
            $this->config["smtp_ssl_host"],
            $this->config["smtp_ssl_port"],
            constant($this->config["smtp_ssl_crypto"])
        );
    }

    /**
     * @return \Kodus\Mail\SMTP\Authenticator\LoginAuthenticator
     */
    public function createLoginAuthenticator()
    {
        return new LoginAuthenticator(
            $this->config["smtp_username"],
            $this->config["smtp_password"]
        );
    }
}
