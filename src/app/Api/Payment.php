<?php

namespace Cashbene\GatewayWordpress\App\Api;

use Cashbene\Core\Api\PaymentContext;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Utils\CredentialsSession;
use Cashbene\Core\Dto\Payment\PayByPayu as PaymentDTO;

class Payment extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/payments/fetch-methods', 'GET', 'fetchPaymentMethods'],
            ['/payments/store-card-token', 'POST', 'storeCardToken'],
            ['/payments/delete-card-token/(?P<token>\S+)', 'DELETE', 'deleteStoredCardToken'],
            ['/payments/pay/(?P<checkoutId>\S+)', 'POST', 'payByPayu'],
        ];
    }

    /**
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see PaymentContext::fetchPaymentMethods()
     */
    public function fetchPaymentMethods()
    {
        try {
            $data = $this->cashbeneGateway->paymentContext()->fetchPaymentMethods(CredentialsSession::getCredentials($this->cashbeneGateway));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see PaymentContext::payByPayu()
     */
    public function payByPayu(\WP_REST_Request $data)
    {
        try {
            $credentials = CredentialsSession::getCredentials($this->cashbeneGateway);
            $payment = $this->serializer->denormalize(
                $data->get_json_params(), PaymentDTO::class, 'array'
            );

            $data = $this->cashbeneGateway
                ->paymentContext()
                ->payByPayu($credentials, $payment, $data->get_param('checkoutId'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data, 201);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see PaymentContext::storeCardToken()
     */
    public function storeCardToken(\WP_REST_Request $data)
    {
        try {
            /** @var PaymentDTO $token */
            $token = $this->serializer->denormalize(
                $data->get_json_params(), PaymentDTO::class, 'array'
            );

            $data = $this->cashbeneGateway->paymentContext()->storeCardToken(CredentialsSession::getCredentials($this->cashbeneGateway), $token);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data);
    }


    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see PaymentContext::deleteCardToken()
     */
    public function deleteStoredCardToken(\WP_REST_Request $data)
    {
        try {
            $this->cashbeneGateway->paymentContext()->deleteCardToken(CredentialsSession::getCredentials($this->cashbeneGateway), $data->get_param('token'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success(null, 204);
    }

}
