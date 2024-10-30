<?php

namespace Cashbene\Core\Service;

use Cashbene\Core\Exception\ArrayErrorsHttpExceptionInterface;
use Cashbene\Core\Exception\HttpException;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Utils\Configuration;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Validator
{
    const DEFAULT_EXCEPTION_LANGUAGE = "en_EN";

    /**@var string */
    private $translationDirPath;

    /**@var string */
    private $language;

    /**@var HttpExceptionService */
    private $exceptionService;

    public function __construct(HttpExceptionService $exceptionService, string $language)
    {
        $this->exceptionService = $exceptionService;
        $this->translationDirPath = Configuration::rootPath() . '/storage/translations';
        $this->language = $language;
    }

    /**
     * @param ResponseInterface $response
     * @return void
     * @throws HttpExceptionInterface
     */
    public function validate(ResponseInterface $response)
    {
        try {
            $this->exceptionService->checkResponseAndThrow($response);
        } catch (HttpExceptionInterface $httpException) {
            throw $this->translateException($httpException);
        }
    }

    /**
     * @param HttpExceptionInterface $httpException
     * @return HttpExceptionInterface
     */
    public function translateException(HttpExceptionInterface $httpException)
    {
        if ($this->language == self::DEFAULT_EXCEPTION_LANGUAGE) {
            return $httpException;
        }

        $message = $this->findTranslation($httpException->getMessage());

        if ($httpException instanceof ArrayErrorsHttpExceptionInterface) {
            $errors = $httpException->getErrors();
        }

        if ($message) {
            $exceptionClass = get_class($httpException);

            if ($exceptionClass === HttpException::class) {
                $httpException = new $exceptionClass($httpException->getHttpCode(), $message);
            } else {
                $httpException = new $exceptionClass($message);
            }
        }


        if (isset($errors)) {
            foreach ($errors as $error) {
                $message = $this->findTranslation($error->message);

                if ($message) {
                    $error->message = $message;
                }
            }

            $httpException->setErrors($errors);
        }

        return $httpException;
    }

    /**
     * @param string $textKey
     * @return false|string
     */
    private function findTranslation(string $textKey)
    {
        $translationFile = $this->translationDirPath . '/fails_responses.' . $this->language . '.json';
        if (file_exists($translationFile)) {
            $translation = json_decode(
                file_get_contents($translationFile),
                true
            );

            if (isset($translation[$textKey])) {
                return $translation[$textKey];
            } else {
                preg_match_all("/'[^']*'/", $textKey, $matches);
                $textKey = preg_replace("/'[^']*'/", "'%s'", $textKey);

                if (isset($translation[$textKey])) {
                    $textKey = $translation[$textKey];

                    $matches = array_map(function ($v) {
                        return trim($v, "'\"");
                    }, $matches[0]);

                    return sprintf($textKey, ...$matches);
                }

                preg_match_all('/"[^"]*"/', $textKey, $matches);
                $textKey = preg_replace('/"[^"]*"/', '"%s"', $textKey);

                if (isset($translation[$textKey])) {
                    $textKey = $translation[$textKey];

                    $matches = array_map(function ($v) {
                        return trim($v, "'\"");
                    }, $matches[0]);

                    return sprintf($textKey, ...$matches);
                }
            }
        }

        return false;
    }
}
