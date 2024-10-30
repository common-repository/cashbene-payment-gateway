<?php

namespace Cashbene\Core\Service;

use Cashbene\Core\Exception\ArrayErrorsHttpExceptionInterface;
use Cashbene\Core\Exception\HttpException;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Utils\Configuration;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpExceptionService {
    private const EXCEPTIONS_NAMESPACE = "Cashbene\Core\Exception";
    private const EXCEPTIONS_CLASSES_PATH = __DIR__ . '/../Exception/';
    private $exceptionClasses;

    public function generateExceptionClasses()
    {
        foreach (glob(self::EXCEPTIONS_CLASSES_PATH .'/*.php') as $class) {
            if (substr($class, -13) == "Interface.php") {
                continue;
            } elseif (substr($class, -9) == "index.php") { // file is created for prestashop by CI/CD
                continue;
            }

            $this->exceptionClasses[] = self::EXCEPTIONS_NAMESPACE . '\\' . basename($class, ".php");
        }
    }

	/**
	 * @param ResponseInterface $response
	 * @return void
     * @throws HttpExceptionInterface
	 */
    public function checkResponseAndThrow(ResponseInterface $response)
    {
        if ($response->getStatusCode() < 400) {
            return;
        }

        $responseContents = $response->getContent(false);
        $responseContents = json_decode($responseContents);
        if (!$responseContents) {
            return;
        }

        $responseContents->code = $responseContents->code ?? $responseContents->error;
        if (is_null($responseContents->code)) {
            return;
        }

        if(!$this->exceptionClasses) {
            $this->generateExceptionClasses();
        }

        foreach ($this->exceptionClasses as $class) {
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                if ($reflection->isAbstract() || $reflection->isInterface()) {
                    continue;
                }

                if (
                    $reflection->implementsInterface(HttpExceptionInterface::class)
                    && strtoupper($responseContents->code) === $class::getStatusCode()
                ) {
                    $instance = new $class(
                        $responseContents->message
                        ?? $responseContents->error_description
                        ?? "Unknown error."
                    );

                    if (
                        $reflection->implementsInterface(ArrayErrorsHttpExceptionInterface::class)
                        && !empty($responseContents->errors)
                    ) {
                        $instance->setErrors($responseContents->errors);
                    }

                    throw $instance;
                }
            }
        }

        throw new HttpException(
            $response->getStatusCode() ?: 500,
            !empty($responseContents->message) ? $responseContents->message : "Unknown error."
        );
    }
}
