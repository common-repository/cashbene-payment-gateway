<?php

namespace Cashbene\Core\Exception;

interface HttpExceptionInterface  extends \Throwable
{
    public static function getStatusCode();
    public function getHttpCode();
    public function getHeaders();
}
