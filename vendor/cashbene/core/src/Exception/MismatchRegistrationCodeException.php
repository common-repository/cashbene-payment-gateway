<?php

namespace Cashbene\Core\Exception;

class MismatchRegistrationCodeException extends HttpException
{
    public function __construct($message = '', $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(422, $message, $previous, $headers, $code);
    }

    public static function getStatusCode()
    {
        return "MISMATCH_REGISTRATION_CODE";
    }
}
