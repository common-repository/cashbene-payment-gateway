<?php

namespace Cashbene\Core;

use Cashbene\Core\Api\CheckoutContext;
use Cashbene\Core\Api\HelloWorldContext;
use Cashbene\Core\Api\PaymentContext;
use Cashbene\Core\Api\RegisterContext;
use Cashbene\Core\Api\RegulationContext;
use Cashbene\Core\Api\ShopContext;
use Cashbene\Core\Api\UserContext;
use Cashbene\Core\Dto\OAuth\AccessToken;
use Cashbene\Core\Service\Credentials\MerchantCredentials;
use Cashbene\Core\Service\OAuth;
use Cashbene\Core\Utils\Configuration;
use Cashbene\Core\Utils\SignatureChecker;

/**
 * Cashbene PHP Library
 */
final class Gateway {

	/** @var Configuration */
	public $configuration;

	/** @var MerchantCredentials */
	public $merchantCredentials;

	/** @var OAuth */
	private $OAuth;

    /** @var SignatureChecker */
    private $signatureChecker;

    public function __construct(Configuration $configuration, AccessToken $merchantAccessToken = null)
	{
		$this->configuration = $configuration;
		$this->OAuth = new OAuth($configuration);
        $this->signatureChecker = new SignatureChecker($configuration);
		$this->merchantCredentials = new MerchantCredentials($this->OAuth, $merchantAccessToken);
	}

	public function setMerchantCredentialsCallback($callback)
	{
		$this->merchantCredentials->setCallback($callback);
	}

    /**
     * @return OAuth
     */
	public function getOAuth()
	{
		return $this->OAuth;
	}

    /**
     * @return SignatureChecker
     */
    public function getSignatureChecker()
    {
        return $this->signatureChecker;
    }

	public function isSetMerchantCredentials()
    {
        $configuration = $this->configuration;
        return $configuration->getClientId() && $configuration->getClientSecret()
            && $configuration->getMerchantId() && $configuration->getEnvironment();
    }

	/**
	 * @return HelloWorldContext
	 */
	public function helloWorldContext()
	{
		return HelloWorldContext::_($this);
	}

	/**
	 * @return RegisterContext
	 */
	public function registerContext()
	{
		return RegisterContext::_($this);
	}

	/**
	 * @return UserContext
	 */
	public function userContext()
	{
		return UserContext::_($this);
	}

	/**
	 * @return ShopContext
	 */
	public function shopContext()
	{
		return ShopContext::_($this);
	}

    /**
     * @return CheckoutContext
     */
    public function checkoutContext()
    {
        return CheckoutContext::_($this);
    }

    /**
     * @return PaymentContext
     */
    public function paymentContext()
    {
        return PaymentContext::_($this);
    }

    /**
     * @return RegulationContext
     */
    public function regulationContext()
    {
        return RegulationContext::_($this);
    }
}
