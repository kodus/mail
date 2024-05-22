<?php

namespace Kodus\Mail;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * This class represents an e-mail Message.
 */
class Message
{
    /**
     * @var Address[]
     */
    private $to = [];

    /**
     * @var Address[]
     */
    private $from = [];

    /**
     * @var Address
     */
    private $sender;

    /**
     * @var Address[]
     */
    private $cc = [];

    /**
     * @var Address[]
     */
    private $bcc = [];

    /**
     * @var Address[]
     */
    private $reply_to = [];

    /**
     * @var string
     */
    private $subject;

    /**
     * @var DateTimeInterface
     */
    private $date;

    /**
     * @var string|null
     */
    private $text;

    /**
     * @var string|null
     */
    private $html;

    /**
     * @var Attachment[]
     */
    private $attachments = [];

    /**
     * @var InlineAttachment[]
     */
    private $inline_attachments = [];

    /**
     * @var Header[]
     */
    private $headers = [];

    /**
     * @param Address|Address[] $to
     * @param Address|Address[] $from
     * @param string            $subject
     * @param string|null       $text
     * @param string|null       $html
     */
    public function __construct($to, $from, string $subject, ?string $text, ?string $html = null)
    {
        $this->setTo($to);
        $this->setFrom($from);
        $this->setSubject($subject);
        $this->setText($text);
        $this->setHTML($html);
        $this->setDate(time());
    }

    /**
     * @return Address[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * This field contains the identity of the primary recipients of the Message.
     *
     * @param Address|Address[] $address
     */
    public function setTo($address): void
    {
        $this->to = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addTo($address): void
    {
        $this->to = array_merge($this->to, is_array($address) ? $address : [$address]);
    }

    /**
     * @return Address[]
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * Specifies the author(s) of the message; that is, the mailbox(es)
     * of the person(s) or system(s) responsible for the writing of the
     * message.
     *
     * @param Address|Address[] $address
     */
    public function setFrom($address): void
    {
        $this->from = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addFrom($address): void
    {
        $this->from = array_merge($this->from, is_array($address) ? $address : [$address]);
    }

    /**
     * Return the Sender (if any)
     *
     * @return Address|null
     */
    public function getSender(): ?Address
    {
        if ($this->sender) {
            return $this->sender;
        }

        if (count($this->from) > 1) {
            return $this->from[0];
        }

        return null;
    }

    /**
     * Specifies the mailbox of the agent responsible for the actual transmission of the message.
     *
     * This field contains the authenticated identity of the "agent" (person, system or process)
     * that sends the message. It is intended for use when the sender is *not* the author of
     * the message, or to indicate who among a group of authors actually sent the message.
     *
     * If the contents of this field would be completely redundant with the "From" field, then
     * the "Sender" field need not be present and its use is discouraged, though still permitted.
     *
     * In particular, the "Sender" field *must* be present if it is *not* the same as the "From" Field.
     *
     * @param Address|null $sender
     */
    public function setSender(?Address $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return Address[]
     */
    public function getCC(): array
    {
        return $this->cc;
    }

    /**
     * This field contains the identity of any secondary recipients of the Message.
     *
     * @param Address|Address[] $address
     */
    public function setCC($address): void
    {
        $this->cc = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addCC($address): void
    {
        $this->cc = array_merge($this->cc, is_array($address) ? $address : [$address]);
    }

    /**
     * @return Address[]
     */
    public function getBCC(): array
    {
        return $this->bcc;
    }

    /**
     * This field contains the identity of additional recipients of the message.
     *
     * The contents of this field are not included in copies of the Message sent to the primary
     * or secondary recipients, e.g. the "To" and "CC" fields.
     *
     * Some systems may choose to include the text of the "BCC" field only in the author's copy,
     * while others may also include it in the text sent to all those indicated in the "BCC" list.
     *
     * @param Address|Address[] $address
     */
    public function setBCC($address): void
    {
        $this->bcc = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addBCC($address): void
    {
        $this->bcc = array_merge($this->bcc, is_array($address) ? $address : [$address]);
    }

    /**
     * @return Address[]
     */
    public function getReplyTo(): array
    {
        return $this->reply_to;
    }

    /**
     * @param Address|Address[] $address
     */
    public function setReplyTo($address): void
    {
        $this->reply_to = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addReplyTo($address): void
    {
        $this->reply_to = array_merge($this->reply_to, is_array($address) ? $address : [$address]);
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param int|string|DateTimeInterface $date DateTime in Sender's timezone (or a UNIX integer timestamp;
     *                                           or a string that is compatible with the strtotime() function)
     */
    public function setDate($date): void
    {
        if ($date instanceof DateTimeInterface) {
            $this->date = $date;
        } elseif (is_int($date)) {
            $this->date = DateTimeImmutable::createFromFormat("U", (string) $date)
                ->setTimezone(timezone_open(date_default_timezone_get()));
        } elseif (is_string($date)) {
            $this->date = DateTimeImmutable::createFromFormat("U", (string) strtotime($date))
                ->setTimezone(timezone_open(date_default_timezone_get()));
        } else {
            throw new InvalidArgumentException("invalid date given: " . var_export($date, true));
        }
    }

    /**
     * @return string|null plain text message body
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text plain text message body
     *
     * @throws InvalidArgumentException if the given message body is not valid UTF-8
     */
    public function setText(?string $text): void
    {
        if (preg_match('//u', $text ?? '') !== 1) {
            throw new InvalidArgumentException("message body contains an invalid UTF-8 byte sequence");
        }

        $this->text = $text;
    }

    /**
     * @return string|null HTML message body
     */
    public function getHTML(): ?string
    {
        return $this->html;
    }

    /**
     * @param string|null $html HTML message body
     *
     * @throws InvalidArgumentException if the given message body is not valid UTF-8
     */
    public function setHTML(?string $html): void
    {
        if (preg_match('//u', $html ?? '') !== 1) {
            throw new InvalidArgumentException("message body contains an invalid UTF-8 byte sequence");
        }

        $this->html = $html;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @return InlineAttachment[]
     */
    public function getInlineAttachments(): array
    {
        return $this->inline_attachments;
    }

    /**
     * Add an inline Attachment, e.g. an image you wish to display in the HTML body of your Message.
     *
     * This method returns a URI for the inline Attachment - you should substitute a placeholder,
     * e.g. for the `src` attribute of an `img` tag, in the body of your HTML Message content -
     * for example:
     *
     *     $html = '<img src="#logo-image"/>';
     *     $uri = $message->addInlineAttachment(Attachment::fromFile(__DIR__ . '/logo.png'));
     *     $html = strtr($html, ["#logo-image" => $uri]);
     *     $message->setHTML($html);
     *
     * @param Attachment $attachment
     *
     * @return string inline Attachment URI
     */
    public function addInlineAttachment(Attachment $attachment): string
    {
        $inline_attachment = new InlineAttachment($attachment);

        $this->inline_attachments[] = $inline_attachment;

        return "cid:" . $inline_attachment->getContentID();
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        $all_headers = [];

        foreach ($this->headers as $headers) {
            $all_headers = array_merge($all_headers, $headers);
        }

        return $all_headers;
    }

    /**
     * Set a custom MIME message header - for example, you may wish to set special headers
     * such as `Message-ID`, `X-Priority` or `X-Mailer` headers, but be aware that some
     * headers (such as `Message-ID`) have syntax that you need to comply with.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader(string $name, string $value): void
    {
        $this->headers[strtolower($name)] = [new Header($name, $value)];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addHeader(string $name, string $value): void
    {
        $this->headers[strtolower($name)][] = new Header($name, $value);
    }
}
