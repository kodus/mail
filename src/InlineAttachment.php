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
     * @var string random seed for Content ID creation
     */
    private static $seed;

    /**
     * @param Attachment $attachment
     */
    public function __construct(Attachment $attachment)
    {
        if (self::$seed === null) {
            self::$seed = sha1(mt_rand() . microtime());
        } else {
            self::$seed = sha1("d73TfecaRMDmRPqBgy4Lv5wZavBeHagmqnLBgd5w" . self::$seed);
        }

        $this->attachment = $attachment;
        $this->content_id = sha1(self::$seed) . "@kodus.mail";
    }

    /**
     * @return Attachment
     */
    public function getAttachment(): Attachment
    {
        return $this->attachment;
    }

    /**
     * @return string
     */
    public function getContentID(): string
    {
        return $this->content_id;
    }
}
