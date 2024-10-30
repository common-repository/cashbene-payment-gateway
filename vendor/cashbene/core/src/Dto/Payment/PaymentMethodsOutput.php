<?php

namespace Cashbene\Core\Dto\Payment;

class PaymentMethodsOutput
{
    /**
     * @var PaymentMethodsCardTokens[] $cardTokens
     */
    public $cardTokens;

    /**
     * @var PaymentMethodsExternal[] $external
     */
    public $external;

//    /**
//     * @var PaymentMethodsExternal[]
//     */
//    public $internal;
}
