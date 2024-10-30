<?php

namespace Cashbene\Core\Api;

use Cashbene\Core\Dto\Shop\Cashback;
use Cashbene\Core\Dto\Shop\ShopShippingMethod;

class ShopContext extends Context {

	/** @return ShopShippingMethod[] */
	public function getMerchantShippingMethods()
	{
		$response = $this->request->doRequest(
			'GET',
			"v1/e-commerce/merchants/{$this->_configuration->getMerchantId()}/shipping",
			$this->_gateway->merchantCredentials
		);

		return $this->serializer->deserialize($response->getContent(false), ShopShippingMethod::class.'[]', 'json');
	}

	// @todo: implement getMerchantPaymentMethods()
	/** @return ShopShippingMethod */
	public function getMerchantShippingMethod(string $addressId)
	{
		$response = $this->request->doRequest(
			'GET',
			"v1/e-commerce/merchants/{$this->_configuration->getMerchantId()}/shipping/{$addressId}",
			$this->_gateway->merchantCredentials
		);

		return $this->serializer->deserialize($response->getContent(false), ShopShippingMethod::class, 'json');
	}

    /** @return Cashback */
    public function getMerchantCashbackValue()
    {
        $response = $this->request->doRequest(
            'GET',
            "v1/e-commerce/merchants/{$this->_configuration->getMerchantId()}/cashback-value",
            $this->_gateway->merchantCredentials
        );

        return $this->serializer->deserialize($response->getContent(false), Cashback::class, 'json');
    }

    public function getVerificationCode(string $phoneNumber)
    {
        $response = $this->request->doRequest(
            'GET',
            "v1/users/verification-code/{$phoneNumber}",
            $this->_gateway->merchantCredentials
        );

        return $response->getContent(false);
    }

    public function simulateWebhook($credentials, $order)
    {
        $response = $this->request->doRequest(
            'POST',
            "/v1/admin/payu/notifications/order",
            $credentials,
            ['json' => $order]
        );

        return $response->getContent(false);
    }
}
