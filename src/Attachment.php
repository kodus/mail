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
    public function __construct($content, string $filename, string $mime_type = self::DEFAULT_MIME_TYPE)
    {
        $this->content = $content;
        $this->filename = $filename;
        $this->mime_type = $mime_type;
    }

    /**
     * Create an attachment from a physical file (or any other supported stream protocol)
     *
     * @param string      $path      absolute path to physical file (or any other supported stream protocol)
     * @param string|null $filename  optional logical filename (defaults to base filename of the specified physical file)
     * @param string      $mime_type MIME-type
     *
     * @return self
     */
    public static function fromFile(string $path, ?string $filename = null, string $mime_type = self::DEFAULT_MIME_TYPE): self
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

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMIMEType(): string
    {
        return $this->mime_type;
    }
}
