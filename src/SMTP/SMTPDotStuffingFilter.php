<?php

namespace Kodus\Mail\SMTP;

use php_user_filter as Filter;

stream_filter_register(SMTPDotStuffingFilter::FILTER_NAME, SMTPDotStuffingFilter::class);

/**
 * This class implements a PHP stream filter that applies "dot-stuffing" to the stream contents.
 *
 * The transmission of the body of a mail message under the SMTP protocol is initiated with a `DATA` command
 * after which it is transmitted verbatim line by line and is terminated with an end-of-data sequence.
 *
 * This sequence consists of a new-line (`<CR><LF>`), a single full stop (`"."`), followed by another new-line.
 *
 * Since a message body can contain a line with just a period as part of the text, the client sends two periods
 * every time a line starts with a period; correspondingly, the server replaces every sequence of two periods
 * at the beginning of a line with a single one.
 */
class SMTPDotStuffingFilter extends Filter
{
    const FILTER_NAME = "smtp.dot_stuffing";

    /**
     * @var string
     */
    private $last_two_bytes = "";

    /**
     * @param resource $in
     * @param resource $out
     * @param int      &$consumed
     * @param bool     $closing
     *
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $data = substr(
                str_replace(
                    "\r\n.",
                    "\r\n..",
                    $this->last_two_bytes . $bucket->data
                ),
                strlen($this->last_two_bytes)
            );

            $this->last_two_bytes = substr($this->last_two_bytes . $bucket->data, -2);

            $consumed += $bucket->datalen;

            $bucket->data = $data;
            $bucket->datalen = strlen($data);

            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
