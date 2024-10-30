<?php

namespace Cashbene\Core\Dto;

class ShippingMethodOutput extends ShippingMethod
{
	public const TYPE_COURIER = 'COURIER';
	public const TYPE_POINT = 'POINT';

	/** @var string */
	public $shipmentAddressId;
}
