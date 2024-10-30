<?php

namespace Cashbene\Core\Exception;

class NotFoundHttpException extends HttpException
{
    public function __construct($message = '', $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(404, $message, $previous, $headers, $code);
    }

    public static function getStatusCode()
    {
        return "NOT_FOUND";
    }
}
