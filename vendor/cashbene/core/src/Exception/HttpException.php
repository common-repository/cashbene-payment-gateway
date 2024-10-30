<?php

namespace Cashbene\Core\Exception;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    private $httpCode;
    private $headers;

    public function __construct($httpCode, $message = '', $previous = null, $headers = [], $code = 0)
    {
        $this->httpCode = $httpCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public static function getStatusCode()
    {
        return "UNKNOWN_ERROR";
    }
}
