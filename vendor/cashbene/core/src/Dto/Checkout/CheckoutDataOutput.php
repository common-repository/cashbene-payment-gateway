<?php

namespace Cashbene\Core\Dto\Checkout;


use Cashbene\Core\Dto\Shop\Price;

class CheckoutDataOutput
{
    /**
     * @var string $buyerId
     */
    public $buyerId;

    /**
     * @var CheckoutData $checkoutData
     */
    public $checkoutData;

    /**
     * @var string $id
     */
    public $id;

    /**
     * @var
     */
    public $note;

    /**
     * @var string
     */
    public $shippingAddressId;

    /**
     * @var string
     */
    public $status;

    /**
     * @var Price $totalAmount
     */
    public $totalAmount;
}
