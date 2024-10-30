<?php

namespace Cashbene\Core\Dto;

class Address
{
	/** @var string $streetName */
	public $streetName;

	/** @var string $streetNumber */
	public $streetNumber;

	/** @var string? $flatNumber */
	public $flatNumber;

	/** @var string $city */
	public $city;

	/** @var string $postal */
	public $postal;

	/** @var string $country */
	public $country;

    /** @var string|null $state */
    public $state;
}
