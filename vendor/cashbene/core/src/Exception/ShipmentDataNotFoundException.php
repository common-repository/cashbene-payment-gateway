<?php

namespace Cashbene\Core\Exception;

class ShipmentDataNotFoundException extends HttpException
{
    public function __construct($message = '', $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }

    public static function getStatusCode()
    {
        return "SHIPMENT_DATA_NOT_FOUND";
    }
}
