<?php

namespace Kodus\Mail;

class Attachment
{
    /**
     * Default MIME-type for Attachments
     */
    const DEFAULT_MIME_TYPE = "application/octet-stream";

    /**
     * @var string|resource
     */
    private $content;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $mime_type;

    /**
     * @param string|resource $content   attachment content (or stream handle)
     * @param string          $filename  logical filename
     * @param string          $mime_type MIME-type
     */
    public function __construct($content, $filename, $mime_type = self::DEFAULT_MIME_TYPE)
    {
        $this->content = $content;
        $this->filename = $filename;
        $this->mime_type = $mime_type;
    }

    /**
     * Create an attachment from a physical file (or any other supported stream protocol)
     *
     * @param string $path absolute path to physical file (or any other supported stream protocol)
     * @param string|null $filename optional logical filename (defaults to base filename of the specified physical file)
     * @param string $mime_type MIME-type
     *
     * @return self
     */
    public static function fromFile($path, $filename = null, $mime_type = self::DEFAULT_MIME_TYPE)
    {
        return new self(
            fopen($path, "r"),
            $filename ?: basename($path),
            $mime_type
        );
    }

    /**
     * @return string|resource
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getMIMEType()
    {
        return $this->mime_type;
    }
}
