<?php

namespace Cashbene\Core\Dto\Payment;

class PayByPayuOutput
{
    /**
     * @var
     */
    public $failureReason;

    /**
     * @var
     */
    public $redirectUri;

    /**
     * @var
     */
    public $status;

    /**
     * @var bool|null
     */
    public $iframeAllowed;

    /**
     * @var string|null
     */
    public $threeDsProtocolVersion;
}
