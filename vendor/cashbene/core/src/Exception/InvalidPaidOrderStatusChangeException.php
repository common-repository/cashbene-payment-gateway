<?php

namespace Cashbene\Core\Exception;

class InvalidPaidOrderStatusChangeException extends HttpException
{
    public function __construct($message = '', $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(422, $message, $previous, $headers, $code);
    }

    public static function getStatusCode()
    {
        return "INVALID_PAID_ORDER_STATUS_CHANGE_ERROR";
    }
}
