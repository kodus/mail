<?php

namespace Kodus\Mail\Test;

use Kodus\Mail\Address;
use Kodus\Mail\Attachment;
use Kodus\Mail\Message;

/**
 * This class creates `Message` objects of all possible flavors:
 *
 *   - Plain text
 *   - HTML only
 *   - HTML with plain text alternative
 *   - Each of the above with Attachment
 *   - Multiple Attachments
 *   - Various other features (custom headers, multiple recipients, ...)
 */
class TestMessageFactory
{
    const TEXT_BODY                  = "It's me! Rasmus!\r\n\r\nI love danish characters, look: æøåÆØÅ! whoa!\r\n\r\nTake care, friend.";
    const TEXT_BODY_BASE64           = "SXQncyBtZSEgUmFzbXVzIQ0KDQpJIGxvdmUgZGFuaXNoIGNoYXJhY3RlcnMsIGxvb2s6IMOmw7jD\r\npcOGw5jDhSEgd2hvYSENCg0KVGFrZSBjYXJlLCBmcmllbmQu";
    const TEXT_BODY_QUOTED_PRINTABLE = "It's me! Rasmus!\r\n\r\nI love danish characters, look: =C3=A6=C3=B8=C3=A5=C3=86=C3=98=C3=85! whoa!\r\n\r\nTake care, friend.";

    const HTML_BODY                  = "<strong>It's me! Rasmus!</strong>\n\nI love danish characters, look: æøåÆØÅ! whoa!\r\n\r\nTake care, friend.";
    const HTML_BODY_QUOTED_PRINTABLE = "<strong>It's me! Rasmus!</strong>\r\n\r\nI love danish characters, look: =C3=A6=C3=B8=C3=A5=C3=86=C3=98=C3=85! whoa!\r\n\r\nTake care, friend.";

    const HTML_BODY_WITH_INLINE_IMAGE = '<strong>It\'s me! Rasmus!</strong><br>I love kittens, look:<br><img src="#kitten-uri"><br>Take care, friend.';
    const HTML_BODY_WITH_INLINE_IMAGE_QP = "<strong>It's me! Rasmus!</strong><br>I love kittens, look:<br><img src=3D\"c=\r\nid:b4d9305ff3748b154ca751b562342c527c23d3bf@kodus.mail\"><br>Take care, frie=\r\nnd.";

    /**
     * @param string $filename
     *
     * @return string absolute path to fixture file
     */
    public function getFixturePath(string $filename): string
    {
        return __DIR__ . '/fixtures/' . $filename;
    }

    /**
     * @return Message
     */
    public function createTextMessage(): Message
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus! I like ÆØÅæøå!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->setSender(new Address("someone-else@test.org"));

        return $message;
    }

    /**
     * @return Message
     */
    public function createTextMessageWithAttachment(): Message
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            [new Address("blub@test.org"), new Address("zoink@test.org")],
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addAttachment(
            new Attachment(file_get_contents($this->getFixturePath("kitten.jpg")), "kitten.jpg", "image/jpeg")
        );

        return $message;
    }

    /**
     * @return Message
     */
    public function createHTMLMessage(): Message
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            null,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        return $message;
    }

    /**
     * @return Message
     */
    public function createHTMLMessageWithAttachment(): Message
    {
        $message = $this->createHTMLMessage();

        $message->addAttachment(
            new Attachment(file_get_contents($this->getFixturePath("kitten.jpg")), "kitten.jpg", "image/jpeg")
        );

        return $message;
    }

    /**
     * @return Message
     */
    public function createTextAndHTMLMessage(): Message
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        return $message;
    }

    /**
     * @return Message
     */
    public function createTextAndHTMLMessageWithAttachment(): Message
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus åh Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY,
            self::HTML_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addAttachment(Attachment::fromFile($this->getFixturePath("kitten.jpg")));

        return $message;
    }

    /**
     * @return Message
     */
    public function createMessageWithMultipleAttachments(): Message
    {
        $message = new Message(
            new Address("blip@test.org", "Rasmus Schultz"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addAttachment(Attachment::fromFile($this->getFixturePath('kitten.jpg')));

        $message->addAttachment(new Attachment(self::TEXT_BODY, "hello.txt", "text/plain; charset=UTF-8"));

        return $message;
    }

    /**
     * @return Message
     */
    public function createMessageWithMultipleRecipients(): Message
    {
        $message = new Message(
            [
                new Address("blip@test.org", "Rasmus Schultz"),
                new Address("also-blip@test.org", "Also Rasmus Schultz"),
            ],
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            "Hello!"
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        return $message;
    }

    /**
     * @return Message
     */
    public function createMessageWithCCAndBCCRecipients(): Message
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            "Hello!"
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addCC(new Address("joe@test.org"));

        $message->addBCC(new Address("bob@test.org"));

        return $message;
    }

    /**
     * @return Message
     */
    public function createMessageWithCustomHeaders(): Message
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "We need a special character - Ø - so that escapeHeaderValue is used, and more than 75 characters - see documentation for quoted_printable_encode()",
            "Hello!"
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $message->addHeader("X-Custom-Header", "custom-value");

        return $message;
    }

    /**
     * @return Message
     */
    public function createMessageWithInlineAttachment(): Message
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            null
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $uri = $message->addInlineAttachment(
            Attachment::fromFile($this->getFixturePath("kitten.jpg"), null, "image/jpeg")
        );

        $message->setHTML(strtr(self::HTML_BODY_WITH_INLINE_IMAGE, ["#kitten-uri" => $uri]));

        return $message;
    }

    /**
     * @return Message
     */
    public function createMessageWithInlineAttachmentAndTextAlternative(): Message
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $uri = $message->addInlineAttachment(
            Attachment::fromFile($this->getFixturePath("kitten.jpg"), null, "image/jpeg")
        );

        $message->setHTML(strtr(self::HTML_BODY_WITH_INLINE_IMAGE, ["#kitten-uri" => $uri]));

        return $message;
    }

    public function createMessageWithInlineAndRegularAttachments(): Message
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            null
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $uri = $message->addInlineAttachment(
            Attachment::fromFile($this->getFixturePath("kitten.jpg"), null, "image/jpeg")
        );

        $message->addAttachment(new Attachment(self::TEXT_BODY, "hello.txt"));

        $message->setHTML(strtr(self::HTML_BODY_WITH_INLINE_IMAGE, ["#kitten-uri" => $uri]));

        return $message;
    }

    public function createMessageWithInlineAndRegularAttachmentsAndTextAlternative(): Message
    {
        $message = new Message(
            new Address("blip@test.org"),
            new Address("blub@test.org"),
            "Hey, Rasmus!",
            self::TEXT_BODY
        );

        $message->setDate("Thu, 15 Sep 2016 17:20:54 +0200");

        $uri = $message->addInlineAttachment(
            Attachment::fromFile($this->getFixturePath("kitten.jpg"), null, "image/jpeg")
        );

        $message->addAttachment(new Attachment(self::TEXT_BODY, "hello.txt"));

        $message->setHTML(strtr(self::HTML_BODY_WITH_INLINE_IMAGE, ["#kitten-uri" => $uri]));

        return $message;
    }

    /**
     * @return Message[]
     */
    public function createAllMessageTypes(): array
    {
        $messages = [];

        foreach (get_class_methods(__CLASS__) as $method) {
            if ($method !== __FUNCTION__ && preg_match("/^create/", $method) === 1) {
                $messages[substr($method, 6)] = $this->$method();
            }
        }

        return $messages;
    }
}
