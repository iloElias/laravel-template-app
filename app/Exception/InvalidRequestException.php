<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class InvalidRequestException extends \Exception
{
    protected array $data;

    public function __construct(string $message = 'Invalid request', array $data = [], $code = Response::HTTP_BAD_REQUEST, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function data()
    {
        return $this->data;
    }
}
