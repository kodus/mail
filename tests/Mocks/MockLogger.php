<?php

namespace Kodus\Mail\Test\Mocks;

use Psr\Log\LoggerInterface;

class MockLogger implements LoggerInterface
{
    /**
     * @var string[]
     */
    public $records = [];

    public function emergency($message, array $context = [])
    {
        $this->append($message);
    }

    public function alert($message, array $context = [])
    {
        $this->append($message);
    }

    public function critical($message, array $context = [])
    {
        $this->append($message);
    }

    public function error($message, array $context = [])
    {
        $this->append($message);
    }

    public function warning($message, array $context = [])
    {
        $this->append($message);
    }

    public function notice($message, array $context = [])
    {
        $this->append($message);
    }

    public function info($message, array $context = [])
    {
        $this->append($message);
    }

    public function debug($message, array $context = [])
    {
        $this->append($message);
    }

    public function log($level, $message, array $context = [])
    {
        $this->append($message);
    }

    private function append($message)
    {
        $this->records[] = $message;
    }
}
