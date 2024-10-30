<?php

namespace Cashbene\Core\Exception;

class UnauthorizedException extends HttpException
{
    public function __construct($message = '', $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(401, $message, $previous, $headers, $code);
    }

    public static function getStatusCode()
    {
        return "UNAUTHORIZED";
    }
}
