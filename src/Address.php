<?php

namespace Kodus\Mail;

use InvalidArgumentException;
use RuntimeException;

/**
 * This model represents an e-mail address and (optionally) associated display-name.
 */
class Address
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @param string      $email valid e-mail address
     * @param string|null $name  display name (optional)
     *
     * @throws InvalidArgumentException for invalid e-mail address
     * @throws RuntimeException on attempted CRLF name injection
     */
    public function __construct(string $email, ?string $name = null)
    {
        if (! self::isValidEmail($email)) {
            throw new InvalidArgumentException("invalid e-mail address");
        }

        if (! empty($name)) {
            if (preg_match('/[\r\n]/', $name)) {
                throw new RuntimeException("CR/LF injection detected");
            }
        }

        $this->email = $email;
        $this->name = $name;
    }

    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
