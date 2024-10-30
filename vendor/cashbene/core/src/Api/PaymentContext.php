<?php

namespace Cashbene\Core\Api;

use Cashbene\Core\Dto\Payment\PayByPayu;
use Cashbene\Core\Dto\Payment\PayByPayuOutput;
use Cashbene\Core\Dto\Payment\PaymentMethodsOutput;
use Cashbene\Core\Dto\Payment\StoreCardTokenPayuOutput;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Service\Credentials\ClientCredentials;

class PaymentContext extends Context {

    /**
     * Store card token
     *
     * @param ClientCredentials $clientCredentials
     * @param PayByPayu $payment
     * @return StoreCardTokenPayuOutput
     * @throws HttpExceptionInterface
     */
	public function storeCardToken(ClientCredentials $clientCredentials, PayByPayu $payment)
	{
        $userData = $this->_gateway->userContext()->getUserData($clientCredentials);
        $response = $this->request->doRequest(
            'POST',
            "/v1/e-commerce/cards/{$userData->uuid}/token",
            $clientCredentials,
            [
                'json' => $payment,
                'headers' => [
                    'x-user-origin' => 'CASH_BENE' // header required by cashbene
                ]
            ],
            false
        );

        return $this->serializer->deserialize(
            $response->getContent(false),
            StoreCardTokenPayuOutput::class,
            'json'
        );
	}

    /**
     * Delete card token
     *
     * @param ClientCredentials $clientCredentials
     * @throws HttpExceptionInterface
     */
    public function deleteCardToken(ClientCredentials $clientCredentials, $token)
    {
        $userData = $this->_gateway->userContext()->getUserData($clientCredentials);
        $this->request->doRequest(
            'DELETE',
            "/v1/e-commerce/cards/{$userData->uuid}/token/{$token}",
            $clientCredentials,
            [
                'headers' => [
                    'x-user-origin' => 'CASH_BENE' // header required by cashbene
                ]
            ]
        );
    }

    /**
     * Fetch payment methods
     *
     * @param ClientCredentials $clientCredentials
     * @return PaymentMethodsOutput
     * @throws HttpExceptionInterface
     */
    public function fetchPaymentMethods(ClientCredentials $clientCredentials)
    {
        $response = $this->request->doRequest(
            'GET',
            "/v1/e-commerce/payments/fetch-methods",
            $clientCredentials,
            [
                'headers' => [
                    'x-user-origin' => 'CASH_BENE', // header required by cashbene
                    'X-MERCHANT-ID' => $this->_configuration->getMerchantId() // header required by cashbene
                ]
            ]
        );

        return $this->serializer->deserialize(
            $response->getContent(false),
            PaymentMethodsOutput::class,
            'json'
        );
    }

    /**
     * Fetch payment methods
     *
     * @param ClientCredentials $clientCredentials
     * @param PayByPayu $payment
     * @param $checkoutId
     * @return PaymentMethodsOutput
     * @throws HttpExceptionInterface
     */
    public function payByPayu(ClientCredentials $clientCredentials, PayByPayu $payment, $checkoutId)
    {

        $userData = $this->_gateway->userContext()->getUserData($clientCredentials);
        $response = $this->request->doRequest(
            'POST',
            "/v1/e-commerce/payments/{$userData->uuid}/pay/{$checkoutId}",
            $clientCredentials,
            [
                'json' => $payment,
            ]
        );

        return $this->serializer->deserialize(
            $response->getContent(false),
            PayByPayuOutput::class,
            'json'
        );
    }

}
