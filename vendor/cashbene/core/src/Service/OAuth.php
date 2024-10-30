<?php

namespace Cashbene\Core\Service;

use Symfony\Component\HttpClient\HttpClient;
use Cashbene\Core\Dto\OAuth\AccessToken;
use Cashbene\Core\Exception\HttpException;
use Cashbene\Core\Utils\Configuration;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Cashbene OAuth service.
 */
class OAuth {
	/**
	 * Merchant auth
	 * @var string
	 */
	const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials'; // Merchant auth

	/**
	 * User auth
	 * @var string
	 */
	const GRANT_TYPE_CASHBENE = 'password'; // User auth

	const GRANT_REFRESH_TOKEN = 'refresh_token';

	/** @var Configuration */
	private $configuration;

	/** @var HttpClient */
	private $client;

	/** @var \Symfony\Component\Serializer\Serializer */
	private $serializer;

    /** @var Validator */
    private $validator;

    public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
		$this->client = HttpClient::create([
			'base_uri' => $this->configuration->baseUrl(),
		]);
		$this->serializer = new Serializer(
			[new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())],
			[new JsonEncoder()]
		);
        $this->validator = new Validator(new HttpExceptionService(), $configuration->getLanguage());
	}

	/**
	 * @return AccessToken
	 */
	public function getMerchantAccessToken()
	{
		$response = $this->doRequest(
			['grant_type' => self::GRANT_TYPE_CLIENT_CREDENTIALS],
			['Authorization' => $this->getMerchantAuth()]
		);

		return $this->serializer->deserialize($response, AccessToken::class, 'json');
	}

	/**
	 * @param string $email
	 * @param string $pinCode
	 *
	 * @return AccessToken
	 */
	public function getUserAccessToken(string $email, string $pinCode)
	{
		$response = $this->doRequest([
			'grant_type' => self::GRANT_TYPE_CASHBENE,
			'username' => $email,
			'password' => (string) $pinCode,
			'application_instance_id' => $this->configuration->getInstanceId(),
		], ['Authorization' => $this->getMerchantAuth()], 'multipart');

		return $this->serializer->deserialize($response, AccessToken::class, 'json');
	}

	/**
	 * @param AccessToken $accessToken
	 * @return AccessToken
	 */
	public function refreshToken(AccessToken $accessToken)
	{
		$response = $this->doRequest([
			'grant_type' => self::GRANT_REFRESH_TOKEN,
			'refresh_token' => $accessToken->refreshToken
		], ['Authorization' => $this->getMerchantAuth()], 'multipart');

		return $this->serializer->deserialize($response, AccessToken::class, 'json');
	}

	/**
	 * @param array $data
	 * @param array $headers
	 * @param string $contentType form_params|multipart
	 *
	 * @return string
	 * @throws \Cashbene\Core\Exception\HttpExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
	 */
	private function doRequest(array $data, array  $headers = [], string $contentType = 'form_params')
	{
		if ($contentType == 'multipart') {
			$formData = new FormDataPart($data);
			$data = $formData->toString();
			$headers = array_merge_recursive($headers, $formData->getPreparedHeaders()->toArray());
		}

		$response = $this->client->request('POST', '/oauth/token', [
			'headers' => array_merge_recursive([
				'Cache-Control' => 'no-cache',
				'Accept' => 'application/json',
			], $headers),
			'body' => $data
		]);

        $this->validator->validate($response);
		return $response->getContent(false);
	}

	/**
	 * Return Basic auth string for current merchant
	 * @return string
	 */
	private function getMerchantAuth()
	{
		return sprintf(
			'Basic %s',
			base64_encode( sprintf(
				'%s:%s',
				$this->configuration->getClientId(),
				$this->configuration->getClientSecret()
			))
		);
	}
}
