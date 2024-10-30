<?php

namespace Cashbene\Core\Service;

use Cashbene\Core\Exception\ArrayErrorsHttpExceptionInterface;
use Cashbene\Core\Exception\HttpExceptionInterface;

class Response
{
    /**
     * @param HttpExceptionInterface $httpException
     * @return array
     */
    public static function prepareSimpleErrorResponseBody(HttpExceptionInterface $httpException)
    {
        $responseBody = [
            'status'=> 'error',
            'error' => [
                'code' => $httpException->getStatusCode(),
                'message' => $httpException->getMessage()
            ]
        ];

        if ($httpException instanceof ArrayErrorsHttpExceptionInterface) {
            $responseBody['error']['errors'] = $httpException->getErrors();
        }

        return $responseBody;
    }

    /**
     * @param array|object|null $data
     * @return string[]
     */
    public static function prepareSimpleSuccessResponseBody($data = null)
    {
        $responseBody = [
            'status' => 'success'
        ];

        if (!is_null($data)) {
            $responseBody['data'] = $data;
        }

        return $responseBody;
    }
}
