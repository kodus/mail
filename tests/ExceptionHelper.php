<?php

namespace Kodus\Mail\Test;

use Codeception\Module;
use Exception;
use PHPUnit_Framework_Assert;
use PHPUnit_Framework_Constraint_Exception;
use PHPUnit_Framework_Constraint_ExceptionMessage;

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

        PHPUnit_Framework_Assert::assertThat(
            $exception,
            new PHPUnit_Framework_Constraint_Exception($type),
            $exception_type . " NOT EQUAL TO " . $type
        );

        if ($message !== null) {
            PHPUnit_Framework_Assert::assertThat(
                $exception,
                new PHPUnit_Framework_Constraint_ExceptionMessage($message)
            );
        }
    }
}
