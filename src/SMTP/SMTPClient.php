<?php

namespace Kodus\Mail\SMTP;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class SMTPClient implements LoggerAwareInterface
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
     * @var string last command issued to the SMTP server
     */
    protected $last_command;

    /**
     * @var string last result received from the SMTP server
     */
    protected $last_result;

    /**
     * @param resource $socket SMTP socket
     *
     * @throws UnexpectedCodeException on missing welcome message
     */
    public function __construct($socket)
    {
        $this->socket = $socket;

        $code = $this->readCode();

        if ($code !== '220') {
            throw new UnexpectedCodeException("220", $code, $this->last_command, $this->last_result);
        }
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
     *
     * @return void
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
     * @throws UnexpectedCodeException
     */
    public function sendCommand($command, $expected_code = null)
    {
        $this->last_command = $command;

        $this->log("S: {$command}");

        fwrite($this->socket, "{$command}{$this->eol}");

        $code = $this->readCode();

        if ($expected_code !== null && $code !== $expected_code) {
            throw new UnexpectedCodeException("250", $code, $this->last_command, $this->last_result);
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

        $this->sendCommand("{$this->eol}.", "250");
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
            $this->log("R: {$line}");

            $this->last_result = $line;

            if (preg_match('/^\d\d\d /', $line) === 1) {
                return substr($line, 0, 3);
            }
        }

        throw new SMTPException("unexpected response\nS: {$this->last_command}\nR: {$this->last_result}");
    }

    /**
     * @param string $message
     */
    protected function log($message)
    {
        if ($this->logger) {
            $this->logger->debug($message);
        }
    }
}
