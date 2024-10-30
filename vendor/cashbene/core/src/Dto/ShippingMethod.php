<?php

namespace Cashbene\Core\Dto;

class ShippingMethod
{
	public const TYPE_COURIER = 'COURIER';
	public const TYPE_POINT = 'POINT';

	/** @var string $name */
	public $name;

	/** @var Address $address */
	public $address;

	/** @var bool $defaultShipment */
	public $defaultShipment;

	/** @var string $type */
	public $type = ShippingMethod::TYPE_COURIER;

	/** @var string */
	public $pointId;
}
