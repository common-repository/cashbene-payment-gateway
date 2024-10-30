<?php

namespace Cashbene\Core\Dto\Shop;

class ShopShippingMethod {

	/** @var bool $freeShippingActive */
	public $freeShippingActive;

	/** @var mixed $freeShippingFrom */
	public $freeShippingFrom;

	/** @var string $id */
	public $id;

	/** @var string $name */
	public $name;

	/** @var Price $price */
	public $price;

	/** @var string $type */
	public $type;

	/** @var bool $active */
	public $active;
}
