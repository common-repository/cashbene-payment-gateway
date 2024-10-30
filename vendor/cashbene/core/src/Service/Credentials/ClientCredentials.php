<?php

namespace Cashbene\Core\Service\Credentials;

use Cashbene\Core\Dto\OAuth\AccessToken;
use Cashbene\Core\Service\OAuth;

class ClientCredentials extends Credentials {

	/** @var string */
	private $email;

	/** @inheritDoc */
	public function __construct(OAuth $OAuth, ?AccessToken $accessToken, string $email, string $pinCode = null) {
		parent::__construct($OAuth, $accessToken);

		$this->email = $email;

		// First login
		if(!$this->accessToken) {
			$this->accessToken = $this->OAuth->getUserAccessToken($this->email, $pinCode);
		}
	}

	public function __clone() {
		unset($this->OAuth);
		unset($this->pinCode);
	}

	/** @inheritDoc */
	public function getAccessToken(): AccessToken {
		if (!$this->accessToken->isValid() && $this->OAuth) {
			$this->accessToken = $this->OAuth->refreshToken($this->accessToken);
		}

		return $this->accessToken;
	}

	public function isRefreshTokenValid(): bool {
		if(!$this->accessToken->refreshToken) {
			return false;
		}

		$decode = $this->decodeToken($this->accessToken->refreshToken);
		$expiresAt = (new \DateTime('@' . $decode->exp));
		return new \DateTime() < $expiresAt;
	}

	/** @return string */
	public function getEmail(): string {
		return $this->email;
	}

	/** @param string $email */
	public function setEmail( string $email ): void {
		$this->email = $email;
	}
}
