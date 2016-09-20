<?php

namespace Kodus\Mail;

use InvalidArgumentException;

/**
 * Internal helper component - writes data to a stream using various filters.
 *
 * @internal
 */
class Writer
{
    /**
     * @var int
     */
    public $line_length = 76;

    /**
     * @var string end-of-line characters
     */
    public $eol = "\r\n";

    /**
     * @var resource
     */
    protected $output;

    /**
     * @param resource $output output stream handle
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Writes raw data from a string, or the raw contents of a given resource, to the output stream
     *
     * @param string|resource $input
     */
    public function write($input)
    {
        if (is_string($input)) {
            fwrite($this->output, $input);
        } elseif (is_resource($input)) {
            rewind($input);
            stream_copy_to_stream($input, $this->output);
        } else {
            throw new InvalidArgumentException("unsupported data-type; expected string or resource handle");
        }
    }

    /**
     * Writes the given raw string data, plus an EOL character, to the output stream
     *
     * @param string $string
     */
    public function writeLine($string = "")
    {
        fwrite($this->output, $string);
        fwrite($this->output, $this->eol);
    }

    /**
     * Writes data from a string, or the raw contents of a given resource, as quoted-printable, to the output stream
     *
     * @param string|resource $input
     */
    public function writeQuotedPrintable($input)
    {
        $this->writeFiltered(
            $input,
            'convert.quoted-printable-encode',
            [
                "line-length"      => $this->line_length,
                "line-break-chars" => $this->eol,
            ]
        );
    }

    /**
     * Writes data from a string, or the raw contents of a given resource, base-64 encoded, to the output stream
     *
     * @param string|resource $input
     */
    public function writeBase64($input)
    {
        $this->writeFiltered(
            $input,
            'convert.base64-encode',
            [
                "line-length"        => $this->line_length,
                "line-break-chars"   => $this->eol,
                "force-encode-first" => true,
            ]
        );
    }

    /**
     * Write data from a string, or the contents of a given resource, to the output string, while applying a filter
     *
     * @param string|resource $input
     * @param string          $filter
     * @param array           $options
     */
    protected function writeFiltered($input, $filter, $options = [])
    {
        $filter = stream_filter_append($this->output, $filter, STREAM_FILTER_WRITE, $options);

        $this->write($input);

        stream_filter_remove($filter);
    }
}
