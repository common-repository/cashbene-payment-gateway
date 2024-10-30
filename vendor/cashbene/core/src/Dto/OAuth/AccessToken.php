<?php

namespace Cashbene\Core\Dto\OAuth;

class AccessToken {
	public function __construct() {
		$this->createdAt = new \DateTime();
	}

	/** @var string */
	public $grantType;

	/** @var string $accessToken */
	public $accessToken;

	/** @var string $tokenType */
	public $tokenType;

	/** @var string $refreshToken */
	public $refreshToken;

	/** @var int $expiresIn */
	public $expiresIn = 0;

	/** @var string $scope */
	public $scope;

	/** @var string $jti */
	public $jti;

	/** @var \DateTime */
	public $createdAt;

	public function isValid(): bool {
		$expiresAt = (clone $this->createdAt)->modify("+{$this->expiresIn} seconds");
		return new \DateTime() < $expiresAt;
	}
}
