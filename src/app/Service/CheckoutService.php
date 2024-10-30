<?php

namespace Cashbene\GatewayWordpress\App\Service;

use Cashbene\Core\Dto\Checkout\CheckoutData;
use Cashbene\Core\Dto\Checkout\CheckoutOrder;
use Cashbene\Core\Dto\ShippingMethodOutput;
use Cashbene\Core\Dto\Shop\Price;
use Cashbene\Core\Dto\Shop\Product;
use Cashbene\Core\Dto\User;
use Cashbene\Core\Utils\CredentialsSession;
use Cashbene\GatewayWordpress\App\Component\CashbeneInpostShipmentMethod;
use Cashbene\GatewayWordpress\App\Utils\InpostPaczkomatyIntegration;
use Cashbene\GatewayWordpress\App\Utils\Shop;
use Cashbene\GatewayWordpress\App\Utils\WPDeskPaczkomatyInpostIntegration;
use Cashbene\GatewayWordpress\Kernel\App;
use Symfony\Component\Intl\Languages;

class CheckoutService {

    const PLUGIN_NAME = 'Cashbene gateway';

    /**
	 * @param Product[] $products
	 * @param string $shippingAddressId
	 * @param string $shippingMethodId
	 * @param string $merchantId
	 * @param string|null $externalOrderId
	 *
	 * @return CheckoutOrder
	 */
	static public function prepareOrder(
		array $products,
		string $shippingAddressId,
		string $shippingMethodId,
		string $merchantId,
		?string $externalOrderId = null
	) {
		$order = new CheckoutOrder();
        $order->shippingAddressId = $shippingAddressId;

        $checkoutData = new CheckoutData();
        $checkoutData->shippingMethodId = $shippingMethodId;

        if ($externalOrderId !== null) {
            $checkoutData->externalOrderId = $externalOrderId;
        }

        $checkoutData->products = self::reCalculateProducts($products);

        $order->checkoutData = (object)[
            $merchantId => $checkoutData
        ];

        return $order;
	}

	/**
	 * @param Product[] $products
	 * @return Product[]
	 */
	public static function reCalculateProducts(array &$products)
	{
		foreach ($products as $product) {
			self::reCalculateProduct($product);
		}
		return $products;
	}

	public static function reCalculateProduct(Product &$product)
	{
        $prices = ['unitPrice', 'unitGrossPrice', 'totalPrice', 'displayPrice'];
        foreach ($prices as $price) {
            if($product->{$price}->isTaxEnabled() && !$product->{$price}->hasTax()) {
                $tax = \WC_Tax::calc_tax( $product->{$price}->amount, [
                    [
                        "rate"     => $product->{$price}->getTaxRate(),
                        "label"    => "Tax",
                        "shipping" => "yes",
                        "compound" => "no",
                    ]
                ], $product->{$price}->hasTax());
                $product->{$price}->amount = (float) ($product->{$price}->amount + reset($tax));
            }
        }
		return $product;
	}

	/**
	 * @param User $user
	 * @param \WC_Product[]|\WC_Product_Variation[] $products
	 * @param string $shippingAddressId
	 * @param string $shippingMethodId
	 *
	 * @return \WC_Order|\WP_Error
	 * @throws \WC_Data_Exception|\Exception
	 */
	public static function createWcOrder(
		User $user,
		array $products,
		string $shippingAddressId,
		string $shippingMethodId,
        $prices,
        bool $isCart = false,
        int $givenQuantity = 1
	)
	{
        // Prepare address
        $address = self::prepareAddress($user, $shippingAddressId);

        // Prepare shipping
        $wcShippingMethod = null;
        if (!empty(App::get('databaseSettings')['shipping_methods'][$shippingMethodId])) {
            $wcShippingMethod = Shop::getWcShippingMethod(App::get('databaseSettings')['shipping_methods'][$shippingMethodId], $address['country']);
        }

        if (!$wcShippingMethod) {
            throw new \Exception("Shipping method doesn't exists.", 500);
        }

		$args = [
			'created_via' => self::PLUGIN_NAME
		];

        $wpUser = get_user_by('email', $user->email);
        if ($wpUser instanceof \WP_User && $wpUser->ID) {
            $args['customer_id'] = $wpUser->ID;
        }

		$order = wc_create_order($args);

        if ($isCart) {
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $order->add_product( $product, $quantity);
            }
        } else {
            foreach ($products as $product) {
                $order->add_product($product, $givenQuantity);
            }
        }

        $cashbeneShippingMethod = App::get('cashbeneGateway')->shopContext()->getMerchantShippingMethod($shippingMethodId);

        $order->set_address($address, 'shipping');
        if ($cashbeneShippingMethod->type === ShippingMethodOutput::TYPE_POINT) {
            $order->set_address(self::getBillingAddress($user), 'billing');
        } else {
            $order->set_address($address, 'billing');
        }

		$wcShipping = new \WC_Order_Item_Shipping();
		$wcShipping->set_method_id($wcShippingMethod->id);
		$wcShipping->set_method_title($cashbeneShippingMethod->name);
		$wcShipping->set_instance_id($wcShippingMethod->instance_id);

        $shippingPriceAmount = isset($prices['shippingPrice']) ? $prices['shippingPrice']->amount : $cashbeneShippingMethod->price->amount;
		if(wc_tax_enabled()) {
            $wcShippingRates = self::getWcShippingRates($order, $address['country']);
			$shippingPrice = $shippingPriceAmount / (($wcShippingRates + 100) / 100);
			$wcShipping->set_total($shippingPrice);
			$wcShipping->calculate_taxes();
		} else {
			$wcShipping->set_total($shippingPriceAmount);
		}

        $wcShipping->save();
		$order->add_item($wcShipping);

		// Set payment gateway
		$wcPaymentGateways = WC()->payment_gateways->payment_gateways();
		$order->set_payment_method($wcPaymentGateways['cashbene']);

		$order->calculate_taxes();
		$order->calculate_totals();

        // coupon need to be added after calculate
        self::orderAddCoupon($order, $isCart);

		$order->set_status('on-hold');
		$order->save();

        if ($cashbeneShippingMethod->type === ShippingMethodOutput::TYPE_POINT) {
            self::setOrderParcelLocker($order->get_id(), self::prepareAddress($user, $shippingAddressId, true), $wcShippingMethod, $wcShipping);
        }

		return $order;
	}

    /**
     * @param \WC_Order $order
     * @param string $country
     * @return float|int
     * @see WC_Abstract_Order::calculate_taxes
     */
    private static function getWcShippingRates(\WC_Order $order, string $country)
    {
        $shippingTaxClass = get_option( 'woocommerce_shipping_tax_class' );

        if ('inherit' === $shippingTaxClass) {
            $found_classes = array_intersect(
                array_merge([''], \WC_Tax::get_tax_class_slugs()),
                $order->get_items_tax_classes()
            );
            
            $shippingTaxClass = count($found_classes) ? current($found_classes) : false;
        }

        $rates = \WC_Tax::find_rates([
            'country' => $country,
            'tax_class' => $shippingTaxClass
        ]);

        $rates = array_filter($rates, function($r) {
            return $r["shipping"] === "yes";
        });

        $rates = array_map(function ($r) {
            return $r['rate'];
        }, $rates);

        return array_sum($rates);
    }

    /**
     * Add parcel locker to order in chosen shipment integration
     *
     * @param $orderId
     * @param $address
     * @param $shippingMethod
     */
    private static function setOrderParcelLocker($orderId, $address, $shippingMethod, $wcShipping = null)
    {
        $lockerId = $address['point_id'];
        $lockerAddress1 = $address['address_1'];
        $lockerAddress1 .= $address['address_2'] != '' ? ' / '. $address['address_2'] : '';
        $lockerAddress2 =  $address['postcode'].' '.$address['city'];

        try {
            switch ($shippingMethod->id) {
                case InpostPaczkomatyIntegration::getShippingMethod():
                    InpostPaczkomatyIntegration::orderAddLockerAddress($orderId, $lockerId, $lockerAddress1, $lockerAddress2);
                    break;
                case WPDeskPaczkomatyInpostIntegration::getShippingMethod():
                    WPDeskPaczkomatyInpostIntegration::orderAddLockerAddress($orderId, $lockerId, $shippingMethod, $wcShipping);
                    break;
                case CashbeneInpostShipmentMethod::SHIPMENT_ID:
                default:
                    CashbeneInpostShipmentMethod::orderAddLockerAddress($orderId, $lockerId, $lockerAddress1, $lockerAddress2);
                    break;
            }
        } catch (\Exception $exception) {
            CashbeneInpostShipmentMethod::orderAddLockerAddress($orderId, $lockerId, $lockerAddress1, $lockerAddress2);
        }
    }

    /**
     * @param User $user
     * @return array|null
     * @throws \Exception
     */
    public static function getBillingAddress(User $user)
    {
        $gateway = App::get('cashbeneGateway');
        $addresses = $gateway->userContext()->getShipmentAddresses(
            CredentialsSession::getCredentials($gateway),
            $user->uuid,
            ShippingMethodOutput::TYPE_COURIER
        );

        foreach ($addresses as $address) {
            if ($address->defaultShipment === true) {
                return self::prepareAddressArray($user, $address);
            }
        }

		// If no default address, return first address
        foreach ($addresses as $address) {
            if ($address->type === 'COURIER') {
                return self::prepareAddressArray($user, $address);
            }
        }

        return null;
    }

    /**
     * @param User $user
     * @param $shipmentAddressId
     * @return array
     * @throws \Exception
     */
	private static function prepareAddress(User $user, $shipmentAddressId, $additionalFields = false)
	{
		$gateway = App::get('cashbeneGateway');
		$address = $gateway->userContext()->getShipmentAddress(
			CredentialsSession::getCredentials($gateway),
			$user->uuid,
			$shipmentAddressId
		);

		return self::prepareAddressArray($user, $address, $additionalFields);
	}

    /**
     * @param User $user
     * @param $address
     * @return array
     */
    private static function prepareAddressArray(User $user, $address, $additionalFields = false)
    {
        $data = [
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
            'email' => $user->email,
            'phone' => $user->mobile ? $user->mobile->prefix.$user->mobile->number : null,
            'address_1' => $address->address->streetName.' '.$address->address->streetNumber,
            'address_2' => $address->address->flatNumber,
            'city' => $address->address->city,
            'state' => '',
            'postcode' => $address->address->postal,
            'country' => (strlen($address->address->country) > 2)
                ? Languages::getAlpha2Code(strtolower($address->address->country))
                : strtolower($address->address->country)
        ];

		if ($additionalFields) {
			$data['point_id'] = $address->pointId ?? '';
		}

		return $data;
    }

    public static function getWcProductsFromCart(): array
    {
        if (WC()->cart) {
            $wcProducts = [];
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $attributes = $cart_item['variation'] ?? [];
                $wcProduct = Shop::getWcProduct($cart_item['product_id'], $attributes);

                $wcProducts[] = $wcProduct;
            }
            return $wcProducts;
        } else {
            throw new \Exception("Cart is empty.", 500);
        }
    }

    /**
     * @throws \Exception
     */
    public static function getCartProducts($country = 'pl'): array
    {
        if (WC()->cart) {
            $products = [];
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $attributes = $cart_item['variation'] ?? [];
                $product = Shop::getProduct(Shop::getWcProduct($cart_item['product_id'], $attributes), $country ?? 'pl');
                $product->quantity = $cart_item['quantity'] ?? 1;
                self::productSetTotalPrice($product);
                self::productSetDisplayPrice($product);

                $products[] = $product;
            }
            return $products;
        } else {
            throw new \Exception("Cart is empty.", 500);
        }
    }

    public static function productSetTotalPrice(Product $product)
    {
        $price = clone $product->unitGrossPrice;
        $product->totalPrice = $price;
        $product->totalPrice->amount = $price->amount * $product->quantity;
    }

    public static function productSetDisplayPrice(Product $product)
    {
        $price = clone $product->unitPrice;
        $product->displayPrice = $price;
        $product->displayPrice->amount = $price->amount * $product->quantity;
    }

    public static function getDiscountAmount()
    {
        $discountAmount = 0;
        foreach (WC()->cart->applied_coupons as $coupon) {
            $discountAmount += WC()->cart->get_coupon_discount_amount($coupon, false);
        }
        return $discountAmount;
    }

    public static function orderSetDiscount(CheckoutOrder $calcOrder, $country)
    {
        $discount = self::getDiscountAmount();
        if ($discount > 0) {
            $discountPrice = new Price();
            $discountPrice->amount = $discount;

            $discountPrice->setTaxEnabled(wc_tax_enabled());
            $discountPrice->setHasTax(wc_prices_include_tax());
            $discountPrice->currency = strtoupper(get_woocommerce_currency());

            if(wc_tax_enabled()) {
                $taxArray = \WC_Tax::find_rates(['country' => $country]);
                if($taxArray) {
                    $tax = reset($taxArray);
                    $discountPrice->setTaxRate($tax['rate']);
                }
            }

            $calcOrder->discount = $discountPrice;
        }
    }

    private static function orderAddCoupon($order, bool $isCart)
    {
        if ($isCart) {
            foreach (WC()->cart->applied_coupons as $coupon) {
                $order->apply_coupon($coupon);
            }
        }
    }
}
