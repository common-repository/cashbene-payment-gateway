<?php

namespace Cashbene\Core\Utils;

use Cashbene\Core\Gateway;
use Cashbene\Core\Service\Request;
use Cashbene\Core\Service\SerializerService;
use Cashbene\Core\Utils\Configuration;

interface ContextInterface {
	/**
	 * @example("
	 *        if(!self::$instance || self::$instance->_gateway !== $gateway) {
	 *          self::$instance = new self($gateway);
	 *        }
	 *        return self::$instance;
	 * ")
	 *
	 * @param Gateway $gateway
	 */
	public static function _(Gateway $gateway);
}
