<?php

namespace Cashbene\Core\Service;

use Symfony\Component\HttpClient\HttpClient;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Service\Credentials\ClientCredentials;
use Cashbene\Core\Service\Credentials\MerchantCredentials;
use Cashbene\Core\Utils\Configuration;

class Request {
	/** @var Configuration */
	private $configuration;

	/** @var HttpClient */
	private $client;

    /** @var HttpExceptionService */
    private $httpExceptionService;

    /** @var Validator */
    private $validator;

    public function __construct(Configuration $configuration)
	{
        $this->validator = new Validator(new HttpExceptionService(), $configuration->getLanguage());
		$this->configuration = $configuration;
		$this->client = HttpClient::create([
			'base_uri' => $this->configuration->baseUrl(),
		]);
	}

	/**
	 * $data = ['headers' => [], 'json' => [], form_params => [], multipart => [], 'query' => []]
	 *
	 * @param string $method
	 * @param string $endpoint
	 * @param MerchantCredentials|ClientCredentials $credentials
	 * @param array $data
	 * @param bool $throw
	 *
	 * @return \Psr\Http\Message\ResponseInterface|string
	 * @throws HttpExceptionInterface
	 */
	public function doRequest(string $method, string $endpoint, $credentials, array $data = [], bool $throw = true) {
		$accessToken = $credentials->getAccessToken();

		$response =  $this->client->request(
			$method,
			$endpoint,
			array_merge_recursive(['headers' => [
				'Authorization' => "{$accessToken->tokenType} {$accessToken->accessToken}",
				'Cache-Control' => 'no-cache',
				'Accept' => 'application/json',
			]], $data)
		);

		if($throw) {
            $this->validator->validate($response);
		}

		return $response;
	}
}
