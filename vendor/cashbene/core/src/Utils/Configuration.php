<?php

namespace Cashbene\Core\Utils;

class Configuration {
	private $environment;
	private $merchantId;
	private $clientId;
	private $clientSecret;
    private $secretKey;
	private $headerPrefix = 'Bearer';
	private $sslOn = true;
	private $host;
	private $apiVersion = 1;
	private $language;

	const ENVIRONMENT_PRODUCTION = 'production';
	const ENVIRONMENT_SANDBOX = 'sandbox';

	protected static $_validEnvironments = [
		self::ENVIRONMENT_PRODUCTION,
		self::ENVIRONMENT_SANDBOX
	];


    public function __construct($params)
	{
		$this->environment = $params['environment'] ?? self::ENVIRONMENT_SANDBOX;
		$this->merchantId = $params['merchant_id'];
		$this->clientId = $params['client_id'];
		$this->clientSecret = $params['client_secret'];
        $this->secretKey = $params['secret_key'];
		$this->language = $params['language'] ?? 'en_EN';
	}

	public function baseUrl()
	{
		return sprintf('%s://%s', $this->getProtocol(), $this->getServerName($this->environment));
	}

	public function apiUrl($apiVersion = null)
	{
		$apiVersion = $apiVersion ?? $this->getApiVersion();
		return sprintf('%s://%s/%s', $this->getProtocol(), $this->getServerName($this->environment), $apiVersion);
	}

    public static function rootPath()
    {
        return  __DIR__ . "/../..";
    }

	public function getServerName($environment)
	{
		switch ($environment) {
			default:
			case self::ENVIRONMENT_PRODUCTION:
				return 'api-prod.cashbene.com';

			case self::ENVIRONMENT_SANDBOX:
				return 'api-uat.cashbene.com';
		}
	}

    public function getPayuPosId($environment)
    {
//        UAT: 404836
//        PROD: 4273110

        switch ($environment) {
            default:
            case self::ENVIRONMENT_PRODUCTION:
                return 4273110;

            case self::ENVIRONMENT_SANDBOX:
                return 404836;
        }
    }

	/**
	 * @return string
	 */
    public function getPayuScript(): string {
		switch ($this->environment) {
			default:
			case self::ENVIRONMENT_SANDBOX:
				return 'https://secure.snd.payu.com/javascript/sdk';
			case self::ENVIRONMENT_PRODUCTION:
				return 'https://secure.payu.com/javascript/sdk';
		}
    }

	public function getInPostScript(): string
	{
		switch ($this->environment) {
			default:
			case self::ENVIRONMENT_SANDBOX:
				return 'https://sandbox-geowidget.easypack24.net/js/sdk-for-javascript.js';
			case self::ENVIRONMENT_PRODUCTION:
				return 'https://geowidget.easypack24.net/js/sdk-for-javascript.js';
		}
	}

	public function getInPostStyle(): string
	{
		switch ($this->environment) {
			default:
			case self::ENVIRONMENT_SANDBOX:
				return 'https://sandbox-geowidget.easypack24.net/css/easypack.css';
			case self::ENVIRONMENT_PRODUCTION:
				return 'https://geowidget.easypack24.net/css/easypack.css';
		}
	}

	/**
	 * @param string $environment
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function setEnvironment( string $environment ): void {
		if(!in_array($environment, self::$_validEnvironments)) {
			throw new \Exception('Invalid environment');
		}
		$this->environment = $environment;
	}

	/**
	 * @return mixed
	 */
	public function getEnvironment() {
		return $this->environment;
	}

	/**
	 * @return mixed
	 */
	public function getMerchantId() {
		return $this->merchantId;
	}

	/**
	 * @param mixed $merchantId
	 */
	public function setMerchantId( $merchantId ): void {
		$this->merchantId = $merchantId;
	}

	/**
	 * @param mixed $clientId
	 */
	public function setClientId( $clientId ): void {
		$this->clientId = $clientId;
	}

	/**
	 * @return mixed
	 */
	public function getClientId() {
		return $this->clientId;
	}

    /**
     * @param mixed $secretKey
     */
    public function setSecretKey( $secretKey ): void {
        $this->secretKey = $secretKey;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

	/**
	 * @return false|string
	 */
	public function getInstanceId()
	{
		return hash('sha256', $this->getClientId());
	}

	/**
	 * @param mixed $clientSecret
	 */
	public function setClientSecret( $clientSecret ): void {
		$this->clientSecret = $clientSecret;
	}

	/**
	 * @return mixed
	 */
	public function getClientSecret() {
		return $this->clientSecret;
	}

	/**
	 * @param string $headerPrefix
	 */
	public function setHeaderPrefix( string $headerPrefix ): void {
		$this->headerPrefix = $headerPrefix;
	}

	/**
	 * @return string
	 */
	public function getHeaderPrefix(): string {
		return $this->headerPrefix;
	}

	/**
	 * @param bool $sslOn
	 */
	public function setSslOn( bool $sslOn ): void {
		$this->sslOn = $sslOn;
	}

	/**
	 * @return bool
	 */
	public function isSslOn(): bool {
		return $this->sslOn;
	}

	/**
	 * @param mixed $host
	 */
	public function setHost( $host ): void {
		$this->host = $host;
	}

	/**
	 * @return mixed
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param int $apiVersion
	 */
	public function setApiVersion( int $apiVersion ): void {
		$this->apiVersion = $apiVersion;
	}

	/**
	 * @return string
	 */
	public function getApiVersion(): string {
		return "v$this->apiVersion";
	}

	/**
	 * @return string
	 */
	public function getProtocol(): string {
		return $this->isSslOn() ? 'https' : 'http';
	}

	/**
	 * @return string
	 */
	public function getLanguage(): string {
		return $this->language;
	}

	/**
	 * @param string $language
	 */
	public function setLanguage( string $language ): void {
		$this->language = $language;
	}
}
