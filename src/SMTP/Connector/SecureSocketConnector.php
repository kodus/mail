<?php

namespace Kodus\Mail\SMTP\Connector;

class SecureSocketConnector extends SocketConnector
{
    /**
     * @param string $host          SMTP SSL host-name
     * @param int    $port          port-number
     * @param int    $crypto_method one of the STREAM_CRYPTO_METHOD_* constants (defined by PHP)
     *
     * @see stream_socket_enable_crypto()
     */
    public function __construct($host, $port = 465, $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT)
    {
        parent::__construct("ssl://{$this->host}", $port);
    }

    public function connect($client_domain)
    {
        $client = parent::connect($client_domain);

        $code = $client->writeCommand("STARTTLS");

        if ($code !== '220') {
            throw new CodeException('220', $code, array_pop($this->resultStack));
        }

        if (! \stream_socket_enable_crypto($this->smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new CryptoException("Start TLS failed to enable crypto");
        }

        $client->ehlo($client_domain);

        return $client;
    }
}
