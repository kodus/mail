<?php

namespace Kodus\Mail\SMTP;

use Kodus\Mail\MailService;
use Kodus\Mail\Message;

/**
 * This Mail Service implementation delivers Messages directly to an SMTP server.
 *
 * It will block the script while connecting and delivering the Message.
 */
class SMTPMailService implements MailService
{
    /**
     * @var SMTPConnector
     */
    private $connector;

    /**
     * @var SMTPAuthenticator
     */
    protected $authenticator;

    /**
     * @var string
     */
    protected $client_domain;

    /**
     * @param SMTPConnector     $connector     provides the SMTP connection
     * @param SMTPAuthenticator $authenticator performs SMTP authentication
     * @param string            $client_domain client domain-name (provided in handshakes when connecting)
     */
    public function __construct(SMTPConnector $connector, SMTPAuthenticator $authenticator, $client_domain)
    {
        $this->connector = $connector;
        $this->authenticator = $authenticator;
        $this->client_domain = $client_domain;
    }

    public function send(Message $message)
    {
        $client = $this->connector->connect($this->client_domain);

        $this->authenticator->authenticate($client);

        $client->writeMessage($message);
    }
}
