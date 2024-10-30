<?php
/**
 * Plugin Name:     Cashbene Payment Gateway
 * Description:     A payment gateway for cashbene.com.
 * Plugin URI:      https://cashbene.com/
 * Requires PHP:    7.1
 * Author:          InterSynergy
 * Author URI:      https://www.intersynergy.pl
 * Text Domain:     cashbene_gateway_plugin
 * Domain Path:     /languages
 * Version:         1.0.5
 */

use Cashbene\GatewayWordpress\Kernel\App;

add_action('woocommerce_init',  function() {
    require __DIR__ . '/vendor/autoload.php';
    require 'src/bootstrap.php';

    App::get('hookInitializer')->run();
});
