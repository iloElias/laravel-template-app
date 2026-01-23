<?php

namespace App\Support\Traits;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use Dotenv\Exception\ValidationException;
use Illuminate\Validation\ValidationException as ValidationValidationException;

trait IgnoresExceptionOnTransaction
{
    protected const EXCEPTIONS_TO_IGNORE = [
        ValidationValidationException::class,
        ValidationException::class,
        InvalidFormException::class,
        InvalidRequestException::class,
    ];

    protected function shouldIgnoreException($exception)
    {
        foreach (self::EXCEPTIONS_TO_IGNORE as $ignoredException) {
            if ($exception instanceof $ignoredException) {
                return true;
            }
        }

        return false;
    }
}
