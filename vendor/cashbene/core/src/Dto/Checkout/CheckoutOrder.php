<?php

namespace Cashbene\Core\Dto\Checkout;

use Cashbene\Core\Dto\Shop\Price;

class CheckoutOrder {

	/** @var string $shippingAddressId */
	public $shippingAddressId;

    /** @var array $checkoutData */
    public $checkoutData = [];

    /** @var Price|null */
    public $discount;
}
