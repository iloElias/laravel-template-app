<?php

namespace App\Exception;

class InvalidFormException extends \Exception
{
    protected array $errors;

    public function __construct(string $message = 'Invalid form data', array $errors = [], $code = 422, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function errors()
    {
        return $this->errors;
    }
}
