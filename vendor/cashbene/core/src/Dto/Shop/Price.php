<?php

namespace Cashbene\Core\Dto\Shop;

class Price {
	/** @var float|string $amount */
	public $amount;

	/** @var string $currency */
	public $currency;

	/** @var bool */
	private $taxEnabled = true;

	/** @var bool */
	private $hasTax = true;

	/** @var float */
	private $taxRate = 23.0;

	/**
	 * @return bool
	 */
	public function isTaxEnabled(): bool {
		return $this->taxEnabled;
	}

	/**
	 * @param bool $taxEnabled
	 */
	public function setTaxEnabled( bool $taxEnabled ): void {
		$this->taxEnabled = $taxEnabled;
	}

	/**
	 * @return bool
	 */
	public function hasTax(): bool {
		return $this->hasTax;
	}

	/**
	 * @param bool $hasTax
	 */
	public function setHasTax( bool $hasTax ): void {
		$this->hasTax = $hasTax;
	}

	/**
	 * @return float
	 */
	public function getTaxRate(): float {
		return $this->taxRate;
	}

	/**
	 * @param float $taxRate
	 */
	public function setTaxRate( float $taxRate ): void {
		$this->taxRate = $taxRate;
	}

}
