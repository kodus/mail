<?php

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Exception as ConstraintException;
use PHPUnit\Framework\Constraint\ExceptionMessage as ConstraintExceptionMessage;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;

    /**
     * @param string $contents
     */
    public function dumpFile($contents)
    {
        $name = preg_replace('/[^\w\d]/s', '-', $this->scenario->getFeature());

        file_put_contents(dirname(__DIR__) . "/_output/mail.{$name}.txt", $contents);
    }

    /**
     * @param string          $type
     * @param string|callable $message_or_function
     * @param callable|null   $function
     */
    public function assertException(string $type, $message_or_function, ?callable $function = null)
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
