<?php

namespace Cashbene\Core\Exception;

interface ArrayErrorsHttpExceptionInterface
{
    public function getErrors();
    public function setErrors(array $errors);
}
