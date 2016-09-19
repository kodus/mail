<?php

namespace Kodus\Mail;

class MIMEWriter extends Writer
{
    public function writeMessage(Message $message)
    {
        $this->writeHeader("Date", date("r", $message->getDate()));

        $this->writeAddressHeader("To", $message->getTo());
        $this->writeAddressHeader("From", $message->getFrom());
        $this->writeAddressHeader("Cc", $message->getCC());
        $this->writeAddressHeader("Bcc", $message->getBCC());
        $this->writeAddressHeader("Reply-To", $message->getReplyTo());

        // TODO Return-Path header?

        $this->writeHeader("Subject", $message->getSubject());

        // TODO Message-ID header?
        // TODO X-Priority header?
        // TODO X-Mailer header?

        $this->writeHeader("MIME-Version", "1.0");

        foreach ($message->getHeaders() as $header) {
            $this->writeHeader($header->getName(), $header->getValue());
        }

        $attachments = $message->getAttachments();

        if (count($attachments)) {
            $boundary = $this->createMultipartBoundaryName("mixed");

            $this->writeMixedContentTypeHeader($boundary);

            // TODO QA: is this necessary, or even valid? GMail, for example, does not add this
            $this->writeLine();
            $this->writeLine("This is a multipart message in MIME format.");
            $this->writeLine();

            $this->writeMultipartBoundary($boundary);

            $this->writeMessageBody($message);

            foreach ($attachments as $attachment) {
                $this->writeAttachmentPart($attachment);
            }

            $this->writeMultipartBoundaryEnd($boundary);
        } else {
            $this->writeMessageBody($message);
        }
    }

    /**
     * Write the text and/or HTML message body parts
     *
     * @param Message $message
     */
    public function writeMessageBody(Message $message)
    {
        $text = $message->getText();

        $html = $message->getHTML();

        if (! empty($text)) {
            if (! empty($html)) {
                $boundary = $this->createMultipartBoundaryName("alternative");

                $this->writeAlternativeContentTypeHeader($boundary);
                $this->writeLine();

                $this->writeMultipartBoundary($boundary);
                $this->writeTextPart($text);

                $this->writeMultipartBoundary($boundary);
                $this->writeHTMLPart($html);

                $this->writeMultipartBoundaryEnd($boundary);
            } else {
                $this->writeTextPart($text);
            }
        } elseif (! empty($html)) {
            $this->writeHTMLPart($html);
        }
    }

    /**
     * Write the "Content-Type" header and the plain-text body in quoted-printable format
     *
     * @param string|resource $content
     */
    public function writeTextPart($content)
    {
        $this->writeContentTypeHeader("text/plain; charset=UTF-8");
        $this->writeQuotedPrintableEncodingHeader();
        $this->writeLine();
        $this->writeQuotedPrintable($content);
    }

    /**
     * Write the "Content-Type" header and the plain-text body in quoted-printable format
     *
     * @param string $content
     */
    public function writeHTMLPart($content)
    {
        $this->writeContentTypeHeader("text/html; charset=UTF-8");
        $this->writeQuotedPrintableEncodingHeader();
        $this->writeLine();
        $this->writeQuotedPrintable($content);
    }

    /**
     * Write the "Content-Type" header and the Attachment Content in base-64 encoded format
     *
     * @param Attachment $attachment
     */
    public function writeAttachmentPart(Attachment $attachment)
    {
        $filename = $attachment->getFilename();

        $this->writeContentTypeHeader($attachment->getMIMEType());
        $this->writeBase64EncodingHeader();
        $this->writeHeader("Content-Disposition", "attachment; filename=\"{$filename}\"");
        $this->writeLine();
        $this->writeBase64($attachment->getContent());
    }

    /**
     * @param string $boundary
     */
    public function writeMultipartBoundary($boundary)
    {
        $this->writeLine("--{$boundary}");
    }

    /**
     * @param string $boundary
     */
    public function writeMultipartBoundaryEnd($boundary)
    {
        $this->writeLine("--{$boundary}--");
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function writeHeader($name, $value)
    {
//        if (mb_detect_encoding($value, 'ASCII', true) === true) {
        if (preg_match('/^([\x00-\x7F])*$/', $value) === 1) {
            $this->writeLine("{$name}: {$value}");
        } else {
            $this->writeLine("{$name}: =?utf-8?Q?" . quoted_printable_encode($value) . "?=");
        }
    }

    /**
     * @param string    $name      header name
     * @param Address[] $addresses list of Address objects
     */
    public function writeAddressHeader($name, $addresses)
    {
        if (count($addresses)) {
            $this->writeHeader(
                $name,
                implode(
                    ", ",
                    array_map(
                        function (Address $address) {
                            return $address->toString();
                        },
                        $addresses
                    )
                )
            );
        }
    }

    /**
     * @param string $type
     */
    public function writeContentTypeHeader($type)
    {
        $this->writeHeader("Content-Type", $type);
    }

    /**
     * @param string $boundary
     */
    public function writeMixedContentTypeHeader($boundary)
    {
        $this->writeContentTypeHeader("multipart/mixed; boundary=\"{$boundary}\"");
    }

    /**
     * @param string $boundary
     */
    public function writeAlternativeContentTypeHeader($boundary)
    {
        $this->writeContentTypeHeader("multipart/alternative; boundary=\"{$boundary}\"");
    }

    /**
     * Writes the "Content-Transfer-Encoding" header with value "quoted-printable"
     */
    public function writeQuotedPrintableEncodingHeader()
    {
        $this->writeContentEncodingHeader("quoted-printable");
    }

    /**
     * Writes the "Content-Transfer-Encoding" header with value "base64"
     */
    public function writeBase64EncodingHeader()
    {
        $this->writeContentEncodingHeader("base64");
    }

    /**
     * @param string $encoding encoding (e.g. "quoted-printable", "base64" or "8bit")
     */
    protected function writeContentEncodingHeader($encoding)
    {
        $this->writeHeader("Content-Transfer-Encoding", $encoding);
    }

    /**
     * Generates a unique MIME boundary name
     *
     * @param string $prefix static prefix (helps developers diagnose the output)
     *
     * @return string
     */
    protected function createMultipartBoundaryName($prefix)
    {
        static $boundary_index = 1;

        return "===={$prefix}-" . sha1(microtime(true) . $boundary_index++) . "====";
    }
}
