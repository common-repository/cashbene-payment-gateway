<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../autoload.php';

use Cashbene\Core\Utils\Configuration;
use Cashbene\Core\Gateway;
use Cashbene\Core\Service\OAuth;

$configuration = new Configuration([
	'client_id'     => 'plugin_inter_synergy_sp.z.o.o',
	'client_secret' => '6efe94dd118f15c91c290eaec4701956',
	'environment'   => 'sandbox'
]);
$gateway = new Gateway($configuration);
$gateway->setMerchantCredentialsCallback(function ($accessToken) {
	dump('callback', $accessToken);
});

$mobileNumber = new \Cashbene\Core\Dto\MobileNumber();
$mobileNumber->prefix = '+48';
$mobileNumber->number = '693064139';
