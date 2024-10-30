<?php

namespace Cashbene\Core\Dto\Payment;

class PayByPayu
{
    public const TYPE_CARD = 'CARD_TOKEN';
    public const TYPE_BLIK = 'PAY_BY_LINKS';

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $deviceFingerprint;

    /**
     * @var string
     */
    public $value;

    /**
     * @var
     */
    public $authorizationCode;
}
