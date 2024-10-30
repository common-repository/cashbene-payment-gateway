<?php

namespace Cashbene\Core\Dto\Shop;

use Cashbene\Core\Dto\Shop\Price;

class Product {

    /** @var string $sku */
    public $sku;

    /** @var Price */
    public $unitPrice;

    /** @var Price|null */
    public $unitGrossPrice;

    /** @var string $name */
    public $name;

    /** @var string $productId */
    public $productId;

    /** @var string $externalProductId */
    public $externalProductId;

    /** @var int $quantity */
    public $quantity;

	/** @var string $image */
	public $image;

	/** @var Attribute[]  */
	public $attributes = [];

    /** @var Price|null */
    public $totalPrice;

    /** @var Price|null */
    public $displayPrice;

	/** @var array */
	private $allAttributes = [];

	/** @return array */
	public function getAllAttributes(): array {
		return $this->allAttributes;
	}

	/** @param array $allAttributes */
	public function setAllAttributes(array $allAttributes): void {
		$this->allAttributes = $allAttributes;
	}

}
