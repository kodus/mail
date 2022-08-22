<?php

namespace Helper;

use Kodus\Mail\SMTP\Authenticator\LoginAuthenticator;
use Kodus\Mail\SMTP\Connector\SecureSocketConnector;
use Kodus\Mail\SMTP\Connector\SocketConnector;

class Integration extends \Codeception\Module
{
    public function _before(\Codeception\TestInterface $test)
    {
        date_default_timezone_set("Europe/Copenhagen");
    }

    public function createSocketConnector(): SocketConnector
    {
        return new SocketConnector(
            $this->config["smtp_host"],
            $this->config["smtp_port"]
        );
    }

    public function createSecureSocketConnector(): SecureSocketConnector
    {
        return new SecureSocketConnector(
            $this->config["smtp_ssl_host"],
            $this->config["smtp_ssl_port"],
            constant($this->config["smtp_ssl_crypto"])
        );
    }

    public function createLoginAuthenticator(): LoginAuthenticator
    {
        return new LoginAuthenticator(
            $this->config["smtp_username"],
            $this->config["smtp_password"]
        );
    }
}
