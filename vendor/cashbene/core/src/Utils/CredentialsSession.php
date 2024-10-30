<?php

namespace Cashbene\Core\Utils;

use Cashbene\Core\Exception\UnauthorizedException;
use Cashbene\Core\Gateway;
use Cashbene\Core\Service\Credentials\ClientCredentials;

class CredentialsSession {
	private const KEY = 'cashbene_plugin_client_credentials';

	/**
	 * Save client credentials to session
	 *
	 * @param ClientCredentials $credentials
	 * @return void
	 */
	public static function saveCredentials(ClientCredentials $credentials) : void
	{
		$credentials = clone $credentials;
		$_SESSION[self::KEY] = serialize($credentials);
	}

	/**
	 * Get client credentials from session
	 *
	 * @param Gateway $gateway
	 * @return ClientCredentials|null
     * @throws UnauthorizedException
	 */
	public static function getCredentials(Gateway $gateway) : ?ClientCredentials
	{
		if(isset($_SESSION[self::KEY])) {

			/** @var ClientCredentials $unserialized */
			$unserialized = unserialize($_SESSION[self::KEY]);

			if(
				!$unserialized->isRefreshTokenValid() &&
				$unserialized->getAccessToken()->createdAt < (new \DateTime())->modify('+1 day')
			) {
                throw new UnauthorizedException('Please log in first.');
			}

			return new ClientCredentials(
				$gateway->getOAuth(),
				$unserialized->getAccessToken(),
				$unserialized->getEmail()
			);
		}
        throw new UnauthorizedException('Please log in first.');
	}
}
