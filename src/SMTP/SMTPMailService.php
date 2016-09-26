<?php

namespace Kodus\Mail\SMTP;

use Kodus\Mail\MailService;
use Kodus\Mail\Message;
use Kodus\Mail\MIMEWriter;

/**
 * This Mail Service implementation delivers Messages directly to an SMTP server.
 *
 * It will block the script while connecting and delivering the Message.
 */
class SMTPMailService implements MailService
{
    /**
     * @var SMTPAuthenticator
     */
    protected $authenticator;

    /**
     * @var string
     */
    protected $client_domain;

    /**
     * @var SMTPConnector
     */
    private $connector;

    /**
     * @var SMTPClient|null
     */
    private $client;

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
        $this->getClient()->sendMail(
            $this->getSender($message),
            $this->getRecipients($message),
            function ($socket) use ($message) {
                $writer = new MIMEWriter($socket);

                $writer->writeMessage($message);
            }
        );
    }

    /**
     * Internally disconnect the SMTP Client.
     *
     * Long-running services may wish to disconnect the SMTP client after sending a batch
     * of Messages, to avoid timeouts.
     *
     * @return void
     */
    public function disconnect()
    {
        // NOTE: this will cause the SMTP Client instance will fall out of scope, which
        //       will trigger it's destructor, which will send QUIT and close the socket.

        $this->client = null;
    }

    /**
     * Connect and authenticate SMTP Client (if not already connected)
     *
     * @return SMTPClient
     */
    protected function getClient()
    {
        if (! isset($this->client)) {
            $this->client = $this->connector->connect($this->client_domain);

            $this->authenticator->authenticate($this->client);
        }

        return $this->client;
    }

    /**
     * Determine the sender e-mail address from the "Sender" or first "From" field of the Message
     *
     * @param Message $message
     *
     * @return string sender e-mail address
     */
    private function getSender(Message $message)
    {
        $sender = $message->getSender();

        if ($sender) {
            return $sender->getEmail();
        }

        $from = $message->getFrom();

        return $from[0]->getEmail();
    }

    /**
     * Extract recipient e-mail addresses from the "To", "CC" and "BCC" fields of the Message
     *
     * @param Message $message
     *
     * @return string[] list of recipient e-mail addresses
     */
    private function getRecipients(Message $message)
    {
        $recipients = [];

        foreach ($message->getTo() as $recipient) {
            $recipients[] = $recipient->getEmail();
        }

        foreach ($message->getCC() as $recipient) {
            $recipients[] = $recipient->getEmail();
        }

        foreach ($message->getBCC() as $recipient) {
            $recipients[] = $recipient->getEmail();
        }

        return $recipients;
    }
}
