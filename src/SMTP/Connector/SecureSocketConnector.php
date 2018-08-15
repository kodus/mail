<?php

namespace Kodus\Mail\SMTP\Connector;

use Kodus\Mail\SMTP\SMTPClient;

class SecureSocketConnector extends SocketConnector
{
    /**
     * @var int
     */
    private $crypto_method;

    /**
     * @param string $host          SMTP SSL host-name
     * @param int    $port          SMTP port-number
     * @param int    $crypto_method one of the STREAM_CRYPTO_METHOD_* constants (defined by PHP)
     *
     * @see stream_socket_enable_crypto()
     */
    public function __construct(string $host, int $port = 25, int $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT)
    {
        parent::__construct($host, $port);

        $this->crypto_method = $crypto_method;
    }

    public function connect(string $client_domain): SMTPClient
    {
        $client = parent::connect($client_domain);

        $client->sendSTARTTLS($this->crypto_method);

        $client->sendEHLO($client_domain);

        return $client;
    }
}
