<?php

namespace Cashbene\Core\Utils;


class SignatureChecker
{
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array $payload
     * @return string
     */
    public function createSignature(array $payload)
    {
        $signature = json_encode($payload) . $this->configuration->getSecretKey();
        return strtoupper(
            md5($signature)
        );
    }

    public function compareSignatures(string $signature1, string $signature2)
    {
        return $signature1 === $signature2;
    }
}
