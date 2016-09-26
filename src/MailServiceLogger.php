<?php

namespace Kodus\Mail;

use Error;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * This decorator writes a log-message to a PSR-3 logger when sending
 * a given `Message` via another `MailService` implementation.
 */
class MailServiceLogger implements MailService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MailService
     */
    private $mail_service;

    /**
     * @var string log-message template (see getMessageTokens() for available tokens)
     */
    public $template = "{status} via {service} to: {to} from: {from} with subject: {subject}";

    /**
     * @var string PSR-3 log-entry severity level
     *
     * @see https://tools.ietf.org/html/rfc5424#page-11
     */
    public $level = "debug";

    /**
     * @param LoggerInterface $logger
     * @param MailService     $mail_service
     */
    public function __construct(LoggerInterface $logger, MailService $mail_service)
    {
        $this->logger = $logger;
        $this->mail_service = $mail_service;
    }

    /**
     * @param Message $message
     *
     * @throws Exception|Error if the underlying Mail Service throws an Exception or Error
     */
    public function send(Message $message)
    {
        try {
            $this->mail_service->send($message);
        } catch (Exception $e) {
            // catch Exception
        } catch (Error $e) {
            // catch Error
        }

        $status = isset($e)
            ? "ERROR: message not sent [" . get_class($e) . ": " . $e->getMessage() . "]"
            : "message sent";

        $message = strtr(
            $this->template,
            $this->getMessageTokens($message, $status)
        );

        $this->logger->log($this->level, $message);

        if (isset($e)) {
            throw $e; // re-throw unhandled Exception/Error
        }
    }

    /**
     * @param Message $message
     *
     * @return array map where message token => replacement string
     */
    protected function getMessageTokens(Message $message, $status)
    {
        return [
            "{status}"  => $status,
            "{service}" => get_class($this->mail_service),
            "{to}"      => $this->formatAddresses($message->getTo()),
            "{from}"    => $this->formatAddresses($message->getFrom()),
            "{cc}"      => $this->formatAddresses($message->getCC()),
            "{bcc}"     => $this->formatAddresses($message->getBCC()),
            "{subject}" => $message->getSubject(),
            // TODO add more tokens
        ];
    }

    /**
     * @param Address[] $addresses
     *
     * @return string
     */
    protected function formatAddresses($addresses)
    {
        return implode(
            ", ",
            array_map(
                function (Address $address) {
                    $name = $address->getName();
                    $email = $address->getEmail();

                    return $name
                        ? "{$name} <{$email}>"
                        : "<{$email}>";
                },
                $addresses
            )
        );
    }
}
