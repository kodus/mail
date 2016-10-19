<?php

namespace Kodus\Mail;

class InlineAttachment
{
    /**
     * @var Attachment
     */
    private $attachment;

    /**
     * @var string inline Content ID (in mailbox format, per RFC 2392)
     */
    private $content_id;

    /**
     * @param Attachment $attachment
     */
    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
        $this->content_id = sha1(mt_rand() . microtime()) . "@kodus.mail";
    }

    /**
     * @return Attachment
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @return string
     */
    public function getContentID()
    {
        return $this->content_id;
    }
}
