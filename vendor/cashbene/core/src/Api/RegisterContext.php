<?php

namespace Cashbene\Core\Api;

use Cashbene\Core\Dto\MobileNumber;
use Cashbene\Core\Dto\Register\RegisterUser;
use Cashbene\Core\Dto\Register\UserPhoneNumber;
use Cashbene\Core\Exception\EmailExistsException;
use Cashbene\Core\Exception\HttpExceptionInterface;

class RegisterContext extends Context {
	/**
	 * Checks whether the pin code you entered meets security requirements
	 *
	 * @param string $pinCode
	 * @return void
	 * @throws HttpExceptionInterface
	 */
	public function checkPinCode(string $pinCode)
	{
		$this->request->doRequest(
			'POST',
			'/v1/users/check-password/',
			$this->_gateway->merchantCredentials,
			['json' => [
				'password' => $pinCode
			]]
		);
	}

	/**
	 * Send SMS to user phone number to verify it
	 *
	 * @param MobileNumber $mobileNumber
	 * @return void
     * @throws HttpExceptionInterface
	 */
	public function generateSmsVerificationCode(MobileNumber $mobileNumber) {
		$data = new UserPhoneNumber();
		$data->applicationInstanceId = $this->_configuration->getInstanceId();
		$data->mobileNumber = $mobileNumber;

		$this->request->doRequest(
			'POST',
			'/v1/users/generate-verification-code',
			$this->_gateway->merchantCredentials,
			['json' => $data]
		);
	}

	/**
	 * Register user into Cashbene API
	 *
	 * @param RegisterUser $user
	 * @return void
     * @throws HttpExceptionInterface
	 */
	public function signUp(RegisterUser $user) {
        $user->locale = $this->_configuration->getLanguage();
        $user->applicationInstanceId = $this->_configuration->getInstanceId();

		$this->request->doRequest(
			'POST',
			'/v1/users/individual-sign-up',
			$this->_gateway->merchantCredentials,
			[
                'json' => $user,
                'headers' => [
                    'x-user-origin' => 'CASH_BENE' // header required by cashbene
                ]
            ]
		);
	}

    /**
     * Check if email already exist
     *
     * @param string $email
     * @return bool
     * @throws HttpExceptionInterface
     */
    public function emailExist(string $email) {

        try {
            $this->request->doRequest(
                'GET',
                "/v1/users/check-email/{$email}",
                $this->_gateway->merchantCredentials
            );
            return false;
        } catch (EmailExistsException $emailExistsException) {
            return true;
        }
    }

}
