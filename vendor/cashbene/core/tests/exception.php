<?php

use Cashbene\Core\Utils\Configuration;
use Cashbene\Core\Exception\HttpExceptionInterface;

$configuration = new Configuration([
    'client_id' => '',
    'client_secret' => ''
]);

$response = [
    "code" => "EMAIL_EXISTS_CODE",
    "message" => "Provided email already exists"
];

if ($response["code"]) {
    $exceptionsNamespace = "Cashbene\Core\Exception"; // @todo move to config
    $rootPath = $configuration->rootPath();

    foreach (glob($rootPath . "/src/Exception/*.php") as $file) {
        $class = $exceptionsNamespace . "\\" . basename($file, ".php");

        if (class_exists($class)) {
            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            if ($reflection->implementsInterface(HttpExceptionInterface::class) && $response["code"] === $class::getStatusCode()) {
                throw new $class($response['message']);
            }
        }
    }
}

