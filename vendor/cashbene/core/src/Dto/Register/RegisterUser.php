<?php
namespace Cashbene\Core\Dto\Register;

use Cashbene\Core\Dto\MobileNumber;
use Cashbene\Core\Dto\RegistrationConsents;

class RegisterUser
{
	/** @var string $firstName */
	public $firstName;

	/** @var string $lastName */
	public $lastName;

	/** @var string $email */
	public $email;

	/** @var string $password */
	public $password;

	/** @var string $applicationInstanceId */
	public $applicationInstanceId;

    /** @var string $verificationCode */
    public $verificationCode;

	/** @var string $invitationCode */
	public $invitationCode;

	/** @var string $locale */
	public $locale;

	/** @var MobileNumber $mobile */
	public $mobile;

	/** @var RegistrationConsents[] $consents */
	public $consents;
}
