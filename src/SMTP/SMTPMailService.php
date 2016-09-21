<?php

namespace Kodus\Mail\SMTP;

use Psr\Log\LoggerInterface;

/**
 * This Mail Service implementation delivers Messages directly to an SMTP server.
 *
 * It will block the script while connecting and delivering the Message.
 */
class SMTPMailService implements MailService
{
    /**
     * @var resource SMTP socket handle
     */
    protected $smtp;

    /**
     * @var string SMTP hostname
     */
    protected $host;

    /**
     * @var int SMTP server port-number
     */
    protected $port;

    /**
     * @var string SMTP secure SSL TLS
     */
    protected $secure;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string SMTP username
     */
    protected $username;

    /**
     * @var string SMTP password
     */
    protected $password;

    /**
     * @var string oauth access token
     */
    protected $oauth_token;

    /**
     * @var string
     */
    protected $eol = "\r\n";

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param string $domain local domain-name (as specified in the EHLO message to the SMTP server)
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
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
     * set server and port
     *
     * @param string $host   server
     * @param int    $port   port
     * @param string $secure ssl tls
     */
    public function setServer($host, $port, $secure = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->secure = $secure;

        if (! $this->domain) {
            $this->domain = $host;
        }

        $this->log("Set: the server");
    }

    /**
     * auth login with server
     *
     * @param string $username
     * @param string $password
     */
    public function setAuth($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->log("Set: the auth login");
    }

    /**
     * auth oauthbearer with server
     *
     * @param string $oauth_token
     */
    public function setOAuth($oauth_token)
    {
        $this->oauth_token = $oauth_token;

        $this->log("Set: the auth oauthbearer");
    }

    /**
     * Send the message
     *
     * @param Message $message
     *
     * @throws CodeException
     * @throws CryptoException
     * @throws SMTPException
     */
    public function writeMessage(Message $message)
    {
        $this->log('Set: a message will be sent');

        $this->message = $message;

        $this->connect();
        $this->ehlo();

        if ($this->secure === 'tls') {
            $this->starttls();
                $this->ehlo();
        }

        if ($this->username !== null || $this->password !== null) {
            $this->authLogin();
        } elseif ($this->oauth_token !== null) {
            $this->authOAuthBearer();
        }

        $this->mailFrom();
        $this->rcptTo();
        $this->data();
        $this->quit();

        fclose($this->smtp);
    }

    /**
     * connect the server
     * SUCCESS 220
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function connect()
    {
        $this->log("Connecting to {$this->host} at {$this->port}");

        $host = ($this->secure == 'ssl') ? 'ssl://' . $this->host : $this->host;

        $this->smtp = @fsockopen($host, $this->port);

        // TODO use blocking mode?
        // stream_set_blocking($this->smtp, 1);

        if (! $this->smtp) {
            throw new SMTPException("Could not open SMTP Port.");
        }

        $code = $this->readCode();

        if ($code !== '220') {
            throw new CodeException('220', $code, array_pop($this->result_stack));
        }
    }

    /**
     * SMTP STARTTLS
     * SUCCESS 220
     *
     * @throws CodeException
     * @throws CryptoException
     * @throws SMTPException
     */
    protected function starttls()
    {
        $code = $this->writeCommand("STARTTLS");

        if ($code !== '220') {
            throw new CodeException('220', $code, array_pop($this->result_stack));
        }

        if (! \stream_socket_enable_crypto($this->smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new CryptoException("Start TLS failed to enable crypto");
        }
    }

    /**
     * SMTP EHLO
     * SUCCESS 250
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function ehlo()
    {
        $code = $this->writeCommand("EHLO {$this->domain}");

        if ($code !== '250') {
            throw new CodeException('250', $code, array_pop($this->result_stack));
        }
    }

    /**
     * SMTP AUTH LOGIN
     * SUCCESS 334
     * SUCCESS 334
     * SUCCESS 235
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authLogin()
    {
        $code = $this->writeCommand("AUTH LOGIN");

        if ($code !== '334') {
            throw new CodeException('334', $code, array_pop($this->result_stack));
        }

        $code = $this->writeCommand(base64_encode($this->username));

        if ($code !== '334') {
            throw new CodeException('334', $code, array_pop($this->result_stack));
        }

        $code = $this->writeCommand(base64_encode($this->password));

        if ($code !== '235') {
            throw new CodeException('235', $code, array_pop($this->result_stack));
        }
    }

    /**
     * SMTP AUTH OAUTHBEARER
     * SUCCESS 235
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authOAuthBearer()
    {
        $auth_str = sprintf("n,a=%s,%shost=%s%sport=%s%sauth=Bearer %s%s%s",
            $this->message->getFromEmail(),
            chr(1),
            $this->host,
            chr(1),
            $this->port,
            chr(1),
            $this->oauth_token,
            chr(1),
            chr(1)
        );

        $auth_str = base64_encode($auth_str);

        $code = $this->writeCommand("AUTH OAUTHBEARER {$auth_str}");

        if ($code !== '235') {
            throw new CodeException('235', $code, array_pop($this->result_stack));
        }
    }

    /**
     * SMTP AUTH XOAUTH2
     * SUCCESS 235
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authXOAuth2()
    {
        // TODO this method is unused - add support for OAUTH 2.0 authentication?

        $auth_str = sprintf("user=%s%sauth=Bearer %s%s%s",
            $this->message->getFromEmail(),
            chr(1),
            $this->oauth_token,
            chr(1),
            chr(1)
        );

        $auth_str = base64_encode($auth_str);

        $code = $this->writeCommand("AUTH XOAUTH2 {$auth_str}");

        if ($code !== '235') {
            throw new CodeException('235', $code, array_pop($this->result_stack));
        }
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
            throw new CodeException('250', $code, array_pop($this->result_stack));
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
                throw new CodeException('250', $code, array_pop($this->result_stack));
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
            throw new CodeException('354', $code, array_pop($this->result_stack));
        }

        $in = $this->message->toString(); // TODO integrate MIMEWriter

        $code = $this->write($in);

        // TODO terminate data with "." CRLF

        if ($code !== '250') {
            throw new CodeException('250', $code, array_pop($this->result_stack));
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
            throw new CodeException('221', $code, array_pop($this->result_stack));
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

        fwrite($this->smtp, $data, strlen($data));

        $this->log('Sent: ' . $data);
    }

    /**
     * Write an SMTP command (with EOL) to the SMTP socket, and read the status code.
     *
     * @param string $command
     *
     * @return string SMTP status code
     */
    protected function writeCommand($command)
    {
        $this->write("{$command}{$this->eol}");

        return $this->readCode();
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
        while ($str = fgets($this->smtp, 515)) {
            $this->log("Got: " . $str);

            $this->result_stack[] = $str;

            if (substr($str, 3, 1) == " ") {
                $code = substr($str, 0, 3);

                return $code;
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
}
