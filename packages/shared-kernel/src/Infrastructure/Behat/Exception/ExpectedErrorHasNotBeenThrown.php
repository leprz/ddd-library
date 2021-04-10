<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Behat\Exception;

use Exception;

class ExpectedErrorHasNotBeenThrown extends Exception
{
    public static function forExpectedMessage(string $expectedMessage): self
    {
        return new self(
            sprintf("Expected error [%s] has not been thrown", $expectedMessage)
        );
    }

    public static function gotActualMessageInstead(string $actualMessage): self
    {
        return new self(
            sprintf("Actual message [%s] is not the same as expected", $actualMessage)
        );
    }
}
