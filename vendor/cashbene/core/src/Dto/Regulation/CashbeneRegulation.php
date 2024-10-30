<?php

namespace Cashbene\Core\Dto\Regulation;

class CashbeneRegulation
{
	public const TYPE_PRIVACY_POLICY = 'PRIVACY_POLICY';
	public const TYPE_TERMS_AND_CONDITIONS = 'TERMS_AND_CONDITIONS';

    /** @var string $termsType */
    public $termsType;

    /** @var int $version */
    public $version;

    /** @var string $file */
    public $file;
}
