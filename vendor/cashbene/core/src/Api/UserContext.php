<?php

namespace Cashbene\Core\Api;

use Cashbene\Core\Dto\ShippingMethod;
use Cashbene\Core\Dto\ShippingMethodOutput;
use Cashbene\Core\Dto\User;
use Cashbene\Core\Service\Credentials\ClientCredentials;

class UserContext extends Context {
	/**
	 * Login user into Cashbene API
	 *
	 * @param string $email
	 * @param string $pinCode
	 *
	 * @return ClientCredentials required to authenticate all future requests
	 */
	public function signIn(string $email, string $pinCode)
	{
		return new ClientCredentials($this->_gateway->getOAuth(), null, $email, $pinCode);
	}

	/**
	 * Return user information
	 *
	 * @param ClientCredentials $clientCredentials
	 * @return User
	 */
	public function getUserData(ClientCredentials $clientCredentials): User
	{
		$response = $this->request->doRequest('GET', '/v1/users/',  $clientCredentials);
		return $this->serializer->deserialize($response->getContent(false), User::class, 'json');
	}

	/**
	 * Adds a shipping address or parcel locker to the user
	 *
	 * @param ClientCredentials $clientCredentials
	 * @param ShippingMethod $shippingMethod
	 *
	 * @return void
	 */
	public function addShipmentAddress(ClientCredentials $clientCredentials, ShippingMethod $shippingMethod)
	{
		$userData = $this->getUserData($clientCredentials);
		$this->request->doRequest(
			'POST',
			"/v2/users/{$userData->uuid}/shipment-addresses",
			$clientCredentials,
			['json' => $shippingMethod]
		);
	}

    /**
     * Update a shipping address or parcel locker from the user
     *
     * @param ClientCredentials $clientCredentials
     * @param string $userUuid
     * @param string $addressUuid
     * @param ShippingMethod $shippingMethod
     *
     * @return void
     */
    public function updateShipmentAddress(ClientCredentials $clientCredentials, string $userUuid, string $addressUuid, ShippingMethod $shippingMethod)
    {
        $this->request->doRequest(
            'PATCH',
            "/v1/users/{$userUuid}/shipment-addresses/{$addressUuid}",
            $clientCredentials,
            ['json' => $shippingMethod]
        );
    }

	/**
	 * Returns a list of shipping addresses for the user
	 *
	 * @param ClientCredentials $clientCredentials
	 * @param string|null $type ShippingMethod::TYPE_COURIER | ShippingMethod::TYPE_POINT
	 *
	 * @return ShippingMethodOutput[]
	 */
	public function getShipmentAddresses(ClientCredentials $clientCredentials, string $userUuid, ?string $type = null)
	{
		$type = $type ? '/'.strtoupper($type) : '';
		$response = $this->request->doRequest('GET', "/v2/users/{$userUuid}/shipment-addresses{$type}", $clientCredentials);

		return $this->serializer->deserialize($response->getContent(false), ShippingMethodOutput::class.'[]', 'json');
	}

	/**
	 * Return shipping address by id
	 *
	 * @param ClientCredentials $clientCredentials
	 * @param string $userUuid
	 * @param string $shipmentAddressId
	 *
	 * @return ShippingMethodOutput
	 */
	public function getShipmentAddress(ClientCredentials $clientCredentials, string $userUuid, string $shipmentAddressId )
	{
		$response = $this->request->doRequest('GET', "/v1/users/{$userUuid}/shipment-addresses/{$shipmentAddressId}", $clientCredentials);
		return $this->serializer->deserialize($response->getContent(false), ShippingMethodOutput::class, 'json');
	}

	/**
	 * Removes a shipping address or parcel locker from the user
	 *
	 * @param ClientCredentials $clientCredentials
	 * @param string $userUuid
	 * @param string $addressUuid
	 *
	 * @return void
	 * @throws \Cashbene\Core\Exception\HttpExceptionInterface
	 */
	public function deleteShipmentAddress(ClientCredentials $clientCredentials, string $userUuid, string $addressUuid)
	{
		$this->request->doRequest(
			'DELETE',
			"/v1/users/{$userUuid}/shipment-addresses/{$addressUuid}",
			$clientCredentials
		);
	}
}
