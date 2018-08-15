<?php

namespace Kodus\Mail\SMTP\Connector;

use Kodus\Mail\SMTP\SMTPClient;
use Kodus\Mail\SMTP\SMTPConnector;
use Kodus\Mail\SMTP\SMTPException;

class SocketConnector implements SMTPConnector
{
    /**
     * @var string SMTP hostname
     */
    protected $host;

    /**
     * @var int SMTP server port-number
     */
    protected $port;

    /**
     * @param string $host SMTP host-name
     * @param int    $port SMTP port-number
     */
    public function __construct(string $host, int $port = 25)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function connect(string $client_domain): SMTPClient
    {
        $socket = @fsockopen($this->host, $this->port);

        if (! $socket) {
            throw new SMTPException("Could not open SMTP Port.");
        }

        $client = new SMTPClient($socket);

        $client->sendEHLO($client_domain);

        return $client;
    }
}
