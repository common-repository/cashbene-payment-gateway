<?php

namespace Cashbene\Core\Exception;

class ValidationFailedErrorException extends HttpException implements ArrayErrorsHttpExceptionInterface
{
    private $errors;

    public function __construct($message = '', $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public static function getStatusCode()
    {
        return "VALIDATION_FAILED_ERROR";
    }
}
