<?php

namespace Cashbene\Core\Exception;

class ResourceNotFoundErrorException extends HttpException
{
    public function __construct($message = '', $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }

    public static function getStatusCode()
    {
        return "RESOURCE_NOT_FOUND_ERROR";
    }
}
