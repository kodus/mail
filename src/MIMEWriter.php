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

        $sender = $message->getSender();

        if ($sender) {
            $this->writeAddressHeader("Sender", [$sender]);
        } else {
            $from = $message->getFrom();

            if (count($from) > 1) {
                $this->writeAddressHeader("Sender", [$from[0]]);
            } else {
                // The contents of this field would be completely redundant with the "From" field.
                // The "Sender" field need not be present, and its use is discouraged - it's therefore left out.
            }
        }

        $this->writeHeader("Subject", $message->getSubject());

        $this->writeHeader("MIME-Version", "1.0");

        foreach ($message->getHeaders() as $header) {
            $this->writeHeader($header->getName(), $header->getValue());
        }

        $attachments = $message->getAttachments();

        if (count($attachments)) {
            $boundary = $this->createMultipartBoundaryName("mixed");

            $this->writeMixedContentTypeHeader($boundary);

            $this->writeLine();
            $this->writeLine("This is a multipart message in MIME format.");
            $this->writeLine();

            $this->writeMultipartBoundary($boundary);

            $this->writeMessageBody($message);

            foreach ($attachments as $attachment) {
                $this->writeMultipartBoundary($boundary);
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
     * @param string $content
     */
    public function writeTextPart($content)
    {
        $this->writeContentTypeHeader("text/plain; charset=UTF-8");
        $this->writeQuotedPrintableEncodingHeader();
        $this->writeLine();
        $this->writeQuotedPrintable($this->adjustLineBreaks($content));
        $this->writeLine();
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
        $this->writeQuotedPrintable($this->adjustLineBreaks($content));
        $this->writeLine();
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
        $this->writeLine();
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
        $value = $this->escapeHeaderValue($value);

        $this->writeLine("{$name}: {$value}");
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
                            $email = $address->getEmail();
                            $name = $address->getName();

                            return empty($name)
                                ? $email
                                : $this->escapeHeaderValue($name) . " <{$email}>";
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

        return "++++{$prefix}-" . sha1(microtime(true) . $boundary_index++) . "++++";
    }

    /**
     * Escape UTF-8 string (if necessary) for use in a header-value
     *
     * @param string $value
     *
     * @return string
     */
    protected function escapeHeaderValue($value)
    {
        return preg_match('/[\x80-\xFF]/', $value) === 1
            ? "=?UTF-8?Q?" . quoted_printable_encode($value) . "?="
            : $value; // as-is
    }

    /**
     * Adjusts line-breaks, correcting CR or LF as CRLF, to improve quoted-printable encoding.
     *
     * @param string $value
     *
     * @return string
     */
    protected function adjustLineBreaks($value)
    {
        return preg_replace('/(?>\r\n|\n|\r)/u', "\r\n", $value);
    }
}
