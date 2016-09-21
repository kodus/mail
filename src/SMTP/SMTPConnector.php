<?php

namespace Kodus\Mail\SMTP;

/**
 * An SMTP Connector is responsible for the creation of an SMTP Client, and performing
 * the initial handshake with the SMTP server to ensure that the host at the other end
 * is actually an SMTP server.
 */
interface SMTPConnector
{
    /**
     * @param string $client_domain
     *
     * @return SMTPClient open SMTP client, post-handshake, awaiting commands
     */
    public function connect($client_domain);
}
