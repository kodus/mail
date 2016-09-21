<?php

namespace Kodus\Mail\SMTP;

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
     * @param string   $client_domain
     */
    public function __construct($socket, $client_domain)
    {
        $this->socket = $socket;

        $this->doHandshake($client_domain);
    }

    /**
     * Send the `QUIT` command and close the SMTP socket
     */
    public function __destruct()
    {
        $this->sendCommand("QUIT", "221");

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
     * Send the `EHLO` command and checks the response
     *
     * @param string $client_domain
     */
    public function sendEHLO($client_domain)
    {
        $this->sendCommand("EHLO {$client_domain}", "250");
    }

    /**
     * Send the `STARTTLS` command and initialize stream socket encryption.
     *
     * @param int $crypto_method one of the STREAM_CRYPTO_METHOD_* constants (defined by PHP)
     *
     * @throws SMTPException on failure
     */
    public function sendSTARTTLS($crypto_method)
    {
        $this->sendCommand("STARTTLS", "220");

        if (! stream_socket_enable_crypto($this->socket, true, $crypto_method)) {
            throw new SMTPException("STARTTLS failed to enable crypto-method: {$crypto_method}");
        }
    }

    /**
     * Write an SMTP command (with EOL) to the SMTP socket, and read the status code.
     *
     * @param string      $command
     * @param string|null $expected_code optional expected response status-code
     *
     * @return string SMTP status code
     *
     * @throws CodeException
     */
    public function sendCommand($command, $expected_code = null)
    {
        $this->write("{$command}{$this->eol}");

        $code = $this->readCode();

        if ($expected_code !== null && $code !== $expected_code) {
            throw new CodeException('250', $code, $this->getLastResult());
        }

        return $code;
    }

    /**
     * @param string   $sender     sender e-mail address
     * @param string[] $recipients list of recipient e-mail addresses
     * @param callable $write      function (resource $resouce) : void
     */
    public function sendMail($sender, array $recipients, callable $write)
    {
        $this->sendMailFromCommand($sender);
        $this->sendRecipientCommands($recipients);
        $this->sendDataCommands($write);
    }

    /**
     * Read the welcome message from the SMTP server, and send an EHLO command.
     *
     * Connectors call this method to perform the initial handshake with an SMTP server.
     *
     * @param string $client_domain
     *
     * @throws CodeException on failed handshake
     */
    protected function doHandshake($client_domain)
    {
        $code = $this->readCode();

        if ($code !== '220') {
            throw new CodeException('220', $code, $this->getLastResult());
        }

        $this->sendEHLO($client_domain);
    }

    /**
     * Send the `MAIL FROM` command and check the response
     *
     * @param string $sender sender e-mail address
     */
    protected function sendMailFromCommand($sender)
    {
        $this->sendCommand("MAIL FROM:<{$sender}>", "250");
    }

    /**
     * Send a series of `RCPT TO` commands and check each response
     *
     * @param string[] $recipients list of recipient e-mail addresses
     */
    protected function sendRecipientCommands(array $recipients)
    {
        foreach ($recipients as $recipient) {
            $this->sendCommand("RCPT TO:<{$recipient}>", "250");
        }
    }

    /**
     * Send the `DATA` command, expose the filtered stream to a callback for writing
     * the data, terminate the data-stream, and check the response.
     *
     * @param callable $write function (resource $resouce) : void
     *
     * @throws SMTPException
     */
    protected function sendDataCommands(callable $write)
    {
        $this->sendCommand("DATA", "354");

        $filter = stream_filter_append($this->socket, SMTPDotStuffingFilter::FILTER_NAME, STREAM_FILTER_WRITE);

        $write($this->socket);

        stream_filter_remove($filter);

        $this->sendCommand("{$this->eol}.{$this->eol}", "250");
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
