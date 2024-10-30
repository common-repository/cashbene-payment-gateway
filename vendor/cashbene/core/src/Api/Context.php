<?php

namespace Cashbene\Core\Api;

use Cashbene\Core\Gateway;
use Cashbene\Core\Service\Request;
use Cashbene\Core\Service\SerializerService;
use Cashbene\Core\Utils\Configuration;
use Cashbene\Core\Utils\ContextInterface;

abstract class Context implements ContextInterface {
	/** @var array of context instances */
	protected static $instances = [];

	/** @var Gateway */
	public $_gateway;

	/** @var Configuration */
	protected $_configuration;

	/** @var Request */
	protected $request;

	/** @var \Symfony\Component\Serializer\Serializer */
	protected $serializer;

	public function __construct(Gateway $gateway)
	{
		$this->_gateway = $gateway;
		$this->_configuration = $gateway->configuration;
		$this->request = new Request($this->_gateway->configuration);
		$this->serializer = SerializerService::getSerializer();
	}

	public static function _(Gateway $gateway)
	{
		$class = get_called_class();
		if( !isset(self::$instances[$class]) || self::$instances[$class]->_gateway !== $gateway) {
			self::$instances[$class] = new $class($gateway);
		}
		return self::$instances[$class];
	}
}
