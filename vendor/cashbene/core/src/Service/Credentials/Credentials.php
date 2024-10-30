<?php

namespace Cashbene\Core\Service\Credentials;

use Cashbene\Core\Dto\OAuth\AccessToken;
use Cashbene\Core\Service\OAuth;

abstract class Credentials {
	/** @var AccessToken */
	protected $accessToken;

	/** @var OAuth */
	protected $OAuth;

	public function __construct(OAuth $OAuth, ?AccessToken $accessToken) {
		$this->accessToken = $accessToken;
		$this->OAuth = $OAuth;
	}

	/** @return AccessToken */
	public abstract function getAccessToken(): AccessToken;

	protected function decodeToken(string $jwt)
	{
		return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $jwt)[1]))));
	}
}
