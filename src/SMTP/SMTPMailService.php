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

        $client->sendMail(
            $this->getSender($message),
            $this->getRecipients($message),
            function ($socket) use ($message) {
                $writer = new MIMEWriter($socket);

                $writer->writeMessage($message);
            }
        );

        // TODO persist the SMTP connection between calls to send() ?

        unset($client);
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
