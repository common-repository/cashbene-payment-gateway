<?php

namespace Cashbene\Core\Dto\Payment;

class StoreCardTokenPayuOutput
{
    /**
     * @var string|null
     */
    public $failureReason;

    /**
     * @var string|null
     */
    public $redirectUri;

    /**
     * @var string
     */
    public $result;

    /**
     * @var bool|null
     */
    public $iframeAllowed;

    /**
     * @var string|null
     */
    public $threeDsProtocolVersion;
}
