<?php

namespace Kodus\Mail;

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
     * @return Address|Address[]
     */
    public function getCC()
    {
        return $this->cc;
    }

    /**
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
     */
    public function setText($text)
    {
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
     */
    public function setHTML($html)
    {
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
        return $this->headers;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = [$value];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name][] = $value;
    }
}
