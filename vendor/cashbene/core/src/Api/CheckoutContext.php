<?php

namespace Cashbene\Core\Api;

use Cashbene\Core\Dto\Checkout\CheckoutDataOutput;
use Cashbene\Core\Dto\Shop\Price;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Service\Credentials\ClientCredentials;
use Cashbene\Core\Dto\Checkout\CheckoutOrder;

class CheckoutContext extends Context {

    /**
     * Calculate cart value
     *
     * @param CheckoutOrder $checkoutOrder
     * @return Price
     * @throws HttpExceptionInterface
     */
	public function calculateCartValue(ClientCredentials $clientCredentials, CheckoutOrder $checkoutOrder)
	{
        $userData = $this->_gateway->userContext()->getUserData($clientCredentials);
        $response = $this->request->doRequest(
            'POST',
            "/v1/e-commerce/checkouts/{$userData->uuid}/calculate",
            $clientCredentials,
            [
                'json' => $checkoutOrder,
                'headers' => [
                    'x-user-origin' => 'CASH_BENE' // header required by cashbene
                ]
            ]
        );

        $jsonDecode = json_decode($response->getContent(false), true);

        $prices = [];
        foreach ($jsonDecode as $key => $value) {
            $prices[$key] = $this->serializer->denormalize(
                $value,
                Price::class,
                'array'
            );
        }

        return $prices;
	}

    /**
     * Checkout cart
     *
     * @param ClientCredentials $clientCredentials
     * @param CheckoutOrder $checkoutOrder
     * @return string
     * @throws HttpExceptionInterface
     */
	public function checkout(ClientCredentials $clientCredentials, CheckoutOrder $checkoutOrder)
	{
        $userData = $this->_gateway->userContext()->getUserData($clientCredentials);

        $response = $this->request->doRequest(
            'POST',
            "/v1/e-commerce/checkouts/{$userData->uuid}",
            $clientCredentials,
            [
                'json' => $checkoutOrder,
                'headers' => [
                    'x-user-origin' => 'CASH_BENE' // header required by cashbene
                ]
            ]
        );

        // return $checkoutId
        return str_replace(
            $this->_gateway->configuration->baseUrl() . '/v1/e-commerce/checkouts/' . $userData->uuid . '/',
            "",
	        $response->getHeaders(false)['location'][0]
        );
	}

    /**
     * Checkout cart
     *
     * @param ClientCredentials $clientCredentials
     * @param $checkoutId
     * @return CheckoutDataOutput
     * @throws HttpExceptionInterface
     */
    public function getCheckoutData(ClientCredentials $clientCredentials, $checkoutId)
    {

        $userData = $this->_gateway->userContext()->getUserData($clientCredentials);
        $response = $this->request->doRequest(
            'GET',
            "/v1/e-commerce/checkouts/{$userData->uuid}/{$checkoutId}",
            $clientCredentials
        );

        // remove merchantID from response to match DTO
        $jsonDecode = json_decode($response->getContent(false), true);
        $jsonDecode['checkoutData'] = $jsonDecode['checkoutData'][$this->_gateway->configuration->getMerchantId()];

        return $this->serializer->deserialize(
            json_encode($jsonDecode),
            CheckoutDataOutput::class,
            'json'
        );
    }
}
