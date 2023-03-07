<?php

namespace Kodus\Mail\Test\Mocks;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class MockLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var string[]
     */
    public $records = [];

    public function log($level, mixed $message, array $context = []): void
    {
        $this->append($message);
    }

    private function append($message)
    {
        $this->records[] = $message;
    }
}
