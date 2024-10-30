<?php

namespace Cashbene\Core\Service\Credentials;

use Cashbene\Core\Dto\OAuth\AccessToken;
use Cashbene\Core\Service\OAuth;

class MerchantCredentials extends Credentials {

	/** @var callable */
	private $callback;

	public function __construct(OAuth $OAuth, ?AccessToken $accessToken)
	{
		parent::__construct($OAuth, $accessToken);
	}

	/**
	 * @return AccessToken
	 */
	public function getAccessToken(): AccessToken
	{
		if (!$this->accessToken || !$this->accessToken->isValid()) {
			$this->accessToken = $this->OAuth->getMerchantAccessToken();
			$this->runCallback($this->accessToken);
		}

		return $this->accessToken;
	}

	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

	private function runCallback($token)
	{
		if ($this->callback instanceof \Closure) {
			($this->callback)($token);
		} elseif (is_array($this->callback) && is_object($this->callback[0])) {
			$this->callback[0]->{$this->callback[1]}($token);
		} else if (is_array($this->callback) && class_exists($this->callback[0])) {
			$this->callback[0]::{$this->callback[1]}($token);
		} else {
			throw new \Exception('Callback is not a valid callback');
		}
	}
}
