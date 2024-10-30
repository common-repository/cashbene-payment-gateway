<?php

namespace Cashbene\Core\Dto\Checkout;

use Cashbene\Core\Dto\Shop\Price;
use Cashbene\Core\Dto\Shop\Product;

class CheckoutData {

    /** @var string $shippingMethodId */
    public $shippingMethodId;

	/** @var string $externalOrderId */
	public $externalOrderId;

    /** @var Product[] */
    public $products;

    /** @var Price|null */
    public $discount;
}
