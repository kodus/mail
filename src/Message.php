<?php

namespace Kodus\Mail;

use InvalidArgumentException;

/**
 * This class represents an e-mail Message.
 *
 * TODO QA: validate text/html fields for CRLF line-breaks? (not required by transport) - or auto-correct line-breaks?
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
     * @var int timestamp
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
    public function __construct($to, $from, $subject, $text, $html = null)
    {
        $this->setTo($to);
        $this->setFrom($from);
        $this->setSubject($subject);
        $this->setText($text);
        $this->setHTML($html);
        $this->setDate(time());
    }

    /**
     * @return Address|Address[]
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * This field contains the identity of the primary recipients of the Message.
     *
     * @param Address|Address[] $address
     */
    public function setTo($address)
    {
        $this->to = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addTo($address)
    {
        $this->to = array_merge($this->to, is_array($address) ? $address : [$address]);
    }

    /**
     * @return Address|Address[]
     */
    public function getFrom()
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
    public function setFrom($address)
    {
        $this->from = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addFrom($address)
    {
        $this->from = array_merge($this->from, is_array($address) ? $address : [$address]);
    }

    /**
     * Return the Sender
     *
     * @return Address|null
     */
    public function getSender()
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
    public function setSender(Address $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return Address|Address[]
     */
    public function getCC()
    {
        return $this->cc;
    }

    /**
     * This field contains the identity of any secondary recipients of the Message.
     *
     * @param Address|Address[] $address
     */
    public function setCC($address)
    {
        $this->cc = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addCC($address)
    {
        $this->cc = array_merge($this->cc, is_array($address) ? $address : [$address]);
    }

    /**
     * @return Address|Address[]
     */
    public function getBCC()
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
    public function setBCC($address)
    {
        $this->bcc = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addBCC($address)
    {
        $this->bcc = array_merge($this->bcc, is_array($address) ? $address : [$address]);
    }

    /**
     * @return Address|Address[]
     */
    public function getReplyTo()
    {
        return $this->reply_to;
    }

    /**
     * @param Address|Address[] $address
     */
    public function setReplyTo($address)
    {
        $this->reply_to = is_array($address) ? $address : [$address];
    }

    /**
     * @param Address|Address[] $address
     */
    public function addReplyTo($address)
    {
        $this->reply_to = array_merge($this->reply_to, is_array($address) ? $address : [$address]);
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return int timestamp
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int|string $date integer timestamp, or a string compatible with the strtotime() function
     */
    public function setDate($date)
    {
        $this->date = is_int($date)
            ? $date
            : strtotime($date);
    }

    /**
     * @return string|null plain text message body
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string|null $text plain text message body
     *
     * @throws InvalidArgumentException if the given message body is not valid UTF-8
     */
    public function setText($text)
    {
        if (preg_match('//u', $text) !== 1) {
            throw new InvalidArgumentException("message body contains an invalid UTF-8 byte sequence");
        }

        $this->text = $text;
    }

    /**
     * @return string|null HTML message body
     */
    public function getHTML()
    {
        return $this->html;
    }

    /**
     * @param string|null $html HTML message body
     *
     * @throws InvalidArgumentException if the given message body is not valid UTF-8
     */
    public function setHTML($html)
    {
        if (preg_match('//u', $html) !== 1) {
            throw new InvalidArgumentException("message body contains an invalid UTF-8 byte sequence");
        }

        $this->html = $html;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @return Header[]
     */
    public function getHeaders()
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
    public function setHeader($name, $value)
    {
        $this->headers[strtolower($name)] = [new Header($name, $value)];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value)
    {
        $this->headers[strtolower($name)][] = new Header($name, $value);
    }
}
