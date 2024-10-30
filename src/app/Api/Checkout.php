<?php

namespace Cashbene\GatewayWordpress\App\Api;

use Cashbene\Core\Api\CheckoutContext;
use Cashbene\Core\Dto\ShippingMethodOutput;
use Cashbene\Core\Exception\HttpException;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Utils\CredentialsSession;
use Cashbene\GatewayWordpress\App\Service\CheckoutService;
use Cashbene\GatewayWordpress\App\Utils\Shop;
use Cashbene\GatewayWordpress\Kernel\App;

class Checkout extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/checkout/calculate', 'POST', 'calculateCart'],
            ['/checkout/calculate-whole-cart', 'POST', 'calculateWholeCart'],
            ['/checkout/execute', 'POST', 'checkoutCart'],
            ['/checkout/execute-whole-cart', 'POST', 'checkoutWholeCart'],
            ['/checkout/(?P<checkoutId>\S+)', 'GET', 'getCheckoutData'],
        ];
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::calculateCartValue()
     */
    public function calculateCart(\WP_REST_Request $data)
    {
        try {
			$params = $data->get_json_params();
	        $attributes = $params['attributes'] ?? [];

	        $cashbeneUser = $this->cashbeneGateway->userContext()->getUserData(
		        CredentialsSession::getCredentials(App::get('cashbeneGateway'))
	        );
	        $address = CheckoutService::getBillingAddress($cashbeneUser);

			$product = Shop::getProduct(Shop::getWcProduct($params['productId'], $attributes), $address['country'] ?? 'pl');
			$product->quantity = $params['quantity'] ?? 1;
            CheckoutService::productSetTotalPrice($product);
            CheckoutService::productSetDisplayPrice($product);

            $calcOrder = CheckoutService::prepareOrder(
	            [$product],
	            $params['shippingAddressId'],
	            $params['shippingMethodId'],
				$this->cashbeneGateway->configuration->getMerchantId()
            );
            $data = $this->cashbeneGateway->checkoutContext()->calculateCartValue(
				CredentialsSession::getCredentials($this->cashbeneGateway),
				$calcOrder
            );
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data);
    }


    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::calculateCartValue()
     */
    public function calculateWholeCart(\WP_REST_Request $data)
    {
        try {
            $params = $data->get_json_params();

            $cashbeneUser = $this->cashbeneGateway->userContext()->getUserData(
                CredentialsSession::getCredentials(App::get('cashbeneGateway'))
            );
            $address = CheckoutService::getBillingAddress($cashbeneUser);
            $country = $address['country'] ?? 'pl';

            $products = CheckoutService::getCartProducts($country);
            $calcOrder = CheckoutService::prepareOrder(
                $products,
                $params['shippingAddressId'],
                $params['shippingMethodId'],
                $this->cashbeneGateway->configuration->getMerchantId()
            );
            CheckoutService::orderSetDiscount($calcOrder, $country);

            $data = $this->cashbeneGateway->checkoutContext()->calculateCartValue(
                CredentialsSession::getCredentials($this->cashbeneGateway),
                $calcOrder
            );
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        } catch (\Exception $exception) {
            return $this->error(new HttpException($exception->getCode(), $exception->getMessage()));
        }

        return $this->success($data);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::checkout()
     */
    public function checkoutCart(\WP_REST_Request $data)
    {
        try {
            $params = $data->get_json_params();
	        $attributes = $params['attributes'] ?? [];

	        $cashbeneUser = $this->cashbeneGateway->userContext()->getUserData(
		        CredentialsSession::getCredentials(App::get('cashbeneGateway'))
	        );
	        $address = CheckoutService::getBillingAddress($cashbeneUser);

			// Prepare product
			$wcProduct = Shop::getWcProduct($params['productId'], $attributes);
			$cashbeneProduct = Shop::getProduct($wcProduct, $address['country'] ?? 'pl');
			$cashbeneProduct->quantity = $params['quantity'] ?? 1;
            CheckoutService::productSetTotalPrice($cashbeneProduct);
            CheckoutService::productSetDisplayPrice($cashbeneProduct);

            $calcOrder = CheckoutService::prepareOrder(
                [$cashbeneProduct],
                $params['shippingAddressId'],
                $params['shippingMethodId'],
                $this->cashbeneGateway->configuration->getMerchantId()
            );

            $prices = $this->cashbeneGateway->checkoutContext()->calculateCartValue(
                CredentialsSession::getCredentials($this->cashbeneGateway),
                $calcOrder
            );

			// Create WC Order
	        $wcOrder = CheckoutService::createWcOrder(
				$cashbeneUser,
				[$wcProduct],
				$params['shippingAddressId'],
				$params['shippingMethodId'],
                $prices,
                false,
                $cashbeneProduct->quantity
	        );

			// Prepare order object
            $checkoutOrder = CheckoutService::prepareOrder(
	            [$cashbeneProduct],
                $params['shippingAddressId'],
                $params['shippingMethodId'],
                $this->cashbeneGateway->configuration->getMerchantId(),
	            $wcOrder->get_id() . '-WP-' .
                (
                    defined('CONTAINER_TAG')
                    ? CONTAINER_TAG . '-' . wp_generate_uuid4()
                    : wp_generate_uuid4()
                )
            );

            $checkoutId = $this->cashbeneGateway->checkoutContext()->checkout(CredentialsSession::getCredentials($this->cashbeneGateway), $checkoutOrder);
            $data = $this->cashbeneGateway->checkoutContext()->getCheckoutData(CredentialsSession::getCredentials($this->cashbeneGateway), $checkoutId);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        } catch (\Exception $exception) {
            return $this->error(new HttpException($exception->getCode(), $exception->getMessage()));
        }

        return $this->success($data, 201);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::checkout()
     */
    public function checkoutWholeCart(\WP_REST_Request $data)
    {
        try {
            $params = $data->get_json_params();
            $cashbeneUser = $this->cashbeneGateway->userContext()->getUserData(
                CredentialsSession::getCredentials(App::get('cashbeneGateway'))
            );
            $address = CheckoutService::getBillingAddress($cashbeneUser);
            $country = $address['country'] ?? 'pl';
            $products = CheckoutService::getCartProducts($country);

            $calcOrder = CheckoutService::prepareOrder(
                $products,
                $params['shippingAddressId'],
                $params['shippingMethodId'],
                $this->cashbeneGateway->configuration->getMerchantId()
            );
            CheckoutService::orderSetDiscount($calcOrder, $country);

            $prices = $this->cashbeneGateway->checkoutContext()->calculateCartValue(
                CredentialsSession::getCredentials($this->cashbeneGateway),
                $calcOrder
            );

            // Create WC Order
            $wcOrder = CheckoutService::createWcOrder(
                $cashbeneUser,
                [], // products can be empty, loaded from cart
                $params['shippingAddressId'],
                $params['shippingMethodId'],
                $prices,
                true
            );

            // Prepare order object
            $checkoutOrder = CheckoutService::prepareOrder(
                $products,
                $params['shippingAddressId'],
                $params['shippingMethodId'],
                $this->cashbeneGateway->configuration->getMerchantId(),
                $wcOrder->get_id() . '-WP-' .
                (
                defined('CONTAINER_TAG')
                    ? CONTAINER_TAG . '-' . wp_generate_uuid4()
                    : wp_generate_uuid4()
                )
            );

            $checkoutId = $this->cashbeneGateway->checkoutContext()->checkout(CredentialsSession::getCredentials($this->cashbeneGateway), $checkoutOrder);
            $data = $this->cashbeneGateway->checkoutContext()->getCheckoutData(CredentialsSession::getCredentials($this->cashbeneGateway), $checkoutId);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        } catch (\Exception $exception) {
            return $this->error(new HttpException($exception->getCode(), $exception->getMessage()));
        }

        return $this->success($data, 201);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::getCheckoutData()
     */
    public function getCheckoutData(\WP_REST_Request $data)
    {
        try {
            $data = $this->cashbeneGateway->checkoutContext()->getCheckoutData(CredentialsSession::getCredentials($this->cashbeneGateway), $data->get_param('checkoutId'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data);
    }

}
