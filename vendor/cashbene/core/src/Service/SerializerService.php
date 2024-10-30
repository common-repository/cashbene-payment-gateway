<?php

namespace Cashbene\Core\Service;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerService {
	/** @var \Symfony\Component\Serializer\Serializer */
	private static $serializer;

	public function __construct()
	{
		$extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
		self::$serializer = new Serializer(
			[new ObjectNormalizer(null, null, null, $extractor), new ArrayDenormalizer()],
			[new JsonEncoder()]
		);
	}

	/**
	 * @return \Symfony\Component\Serializer\Serializer
	 */
	public static function getSerializer()
	{
		if(!self::$serializer) {
			new self();
		}
		return self::$serializer;
	}
}
