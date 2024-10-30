<?php

namespace Cashbene\Core\Dto\Regulation;

class MerchantRegulation
{
	public const TYPE_PRIVACY_POLICY_PAGE = 'privacy_policy_page';
	public const TYPE_TERMS_AND_CONDITIONS_PAGE = 'terms_and_conditions_page';

    /** @var int $id */
    public $id;

    /** @var string $title */
    public $title;

    /** @var string $url */
    public $url;
}
