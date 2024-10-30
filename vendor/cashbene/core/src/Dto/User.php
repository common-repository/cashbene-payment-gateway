<?php
namespace Cashbene\Core\Dto;

class User
{
    /** @var string */
    public $uuid;

    /** @var string */
    public $email;

    /** @var MobileNumber */
    public $mobile;

    /** @var string */
    public $locale;

	/** @var RegistrationConsents[] $registrationConsents */
	public $registrationConsents;

    /** @var string */
    public $onboardingStatus;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;
}
