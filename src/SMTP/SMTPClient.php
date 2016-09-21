<?php

namespace Kodus\Mail\SMTP;

use Kodus\Mail\Message;
use Psr\Log\LoggerInterface;

class SMTPClient
{
    /**
     * @var resource SMTP socket handle
     */
    protected $socket;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @var string
     */
    protected $eol = "\r\n";

    /**
     * Stack of all commands issued to SMTP
     *
     * @var array
     */
    protected $command_stack = [];

    /**
     * Stack of all results issued to SMTP
     *
     * @var array
     */
    protected $result_stack = [];

    /**
     * @param resource $socket SMTP socket
     */
    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    public function __destruct()
    {
        fclose($this->socket);
    }

    /**
     * @see http://www.php-fig.org/psr/psr-3/
     *
     * @param LoggerInterface|null $logger PSR-3 compliant Logger implementation
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Read the welcome message from the SMTP server, and send an EHLO command.
     *
     * Connectors call this method to perform the initial handshake with an SMTP server.
     *
     * @param string $client_domain
     */
    public function handshake($client_domain)
    {
        $code = $this->readCode();

        if ($code !== '220') {
            throw new CodeException('220', $code, $this->getLastResult());
        }

        $this->ehlo($client_domain);
    }

    /**
     * SMTP EHLO
     * SUCCESS 250
     *
     * @param string $client_domain
     *
     * @throws CodeException
     * @throws SMTPException
     */
    public function ehlo($client_domain)
    {
        $code = $this->writeCommand("EHLO {$client_domain}");

        if ($code !== '250') {
            throw new CodeException('250', $code, $this->getLastResult());
        }
    }

    /**
     * Write an SMTP command (with EOL) to the SMTP socket, and read the status code.
     *
     * @param string      $command
     * @param string|null $expected_code
     *
     * @return string SMTP status code
     */
    public function writeCommand($command, $expected_code = null)
    {
        $this->write("{$command}{$this->eol}");

        $code = $this->readCode();

        if ($expected_code !== null && $code !== $expected_code) {
            throw new CodeException('250', $code, $this->getLastResult());
        }

        return $code;
    }

    /**
     * @param Message $message
     */
    public function writeMessage(Message $message)
    {
        $this->mailFrom();
        $this->rcptTo();
        $this->data();
        $this->quit();
    }

    /**
     * SMTP MAIL FROM
     * SUCCESS 250
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function mailFrom()
    {
        $code = $this->writeCommand("MAIL FROM:<{$this->message->getFromEmail()}>");

        if ($code !== '250') {
            throw new CodeException('250', $code, $this->getLastResult());
        }
    }

    /**
     * SMTP RCPT TO
     * SUCCESS 250
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function rcptTo()
    {
        $to = array_merge(
            $this->message->getTo(),
            $this->message->getCc(),
            $this->message->getBcc()
        );

        foreach ($to as $toEmail => $_) {

            $code = $this->writeCommand("RCPT TO:<{$toEmail}>");

            if ($code !== '250') {
                throw new CodeException('250', $code, $this->getLastResult());
            }
        }
    }

    /**
     * SMTP DATA
     * SUCCESS 354
     * SUCCESS 250
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function data()
    {
        $this->writeCommand("DATA");

        if ($code !== '354') {
            throw new CodeException('354', $code, $this->getLastResult());
        }

        $in = $this->message->toString(); // TODO integrate MIMEWriter

        $code = $this->write($in);

        // TODO terminate data with "." CRLF

        if ($code !== '250') {
            throw new CodeException('250', $code, $this->getLastResult());
        }
    }

    /**
     * SMTP QUIT
     * SUCCESS 221
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function quit()
    {
        $code = $this->writeCommand("QUIT");

        if ($code !== '221') {
            throw new CodeException('221', $code, $this->getLastResult());
        }
    }

    /**
     * Write raw data to the SMTP socket
     *
     * @param string $data
     */
    protected function write($data)
    {
        $this->command_stack[] = $data;

        fwrite($this->socket, $data, strlen($data));

        $this->log('Sent: ' . $data);
    }

    /**
     * Read the SMTP status code
     *
     * @return string SMTP status code
     *
     * @throws SMTPException
     */
    protected function readCode()
    {
        while ($line = fgets($this->socket, 4096)) {
            $this->log("Got: " . $line);

            $this->result_stack[] = $line;

            if (preg_match('/^\d\d\d /', $line) === 1) {
                return substr($line, 0, 3);
            }
        }

        throw new SMTPException("SMTP Server did not respond with anything I recognized");
    }

    /**
     * @param string $message
     */
    protected function log($message)
    {
        if ($this->logger) {
            $this->log($message);
        }
    }

    /**
     * @return mixed
     */
    protected function getLastResult()
    {
        return array_pop($this->result_stack);
    }
}
