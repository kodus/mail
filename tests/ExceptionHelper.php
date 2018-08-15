<?php

namespace Kodus\Mail\Test;

use Codeception\Module;
use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Exception as ConstraintException;
use PHPUnit\Framework\Constraint\ExceptionMessage as ConstraintExceptionMessage;

class ExceptionHelper extends Module
{
    /**
     * @param string          $type
     * @param string|callable $message_or_function
     * @param callable|null   $function
     */
    public function assertException($type, $message_or_function, callable $function = null)
    {
        if (func_num_args() === 3) {
            $message = $message_or_function;
        } else { // 2 args
            $message = null;
            $function = $message_or_function;
        }

        $exception = null;

        try {
            call_user_func($function);
        } catch (Exception $e) {
            $exception = $e;
        }

        $exception_type = $exception ? get_class($exception) : 'null';

        Assert::assertThat(
            $exception,
            new ConstraintException($type),
            $exception_type . " NOT EQUAL TO " . $type
        );

        if ($message !== null) {
            Assert::assertThat(
                $exception,
                new ConstraintExceptionMessage($message)
            );
        }
    }
}
