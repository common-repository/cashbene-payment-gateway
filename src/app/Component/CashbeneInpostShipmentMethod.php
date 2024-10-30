<?php

namespace Cashbene\GatewayWordpress\App\Component;

use Cashbene\GatewayWordpress\Kernel\App;
use Cashbene\GatewayWordpress\Kernel\Initializer\ComponentInitializationInterface;
use Cashbene\GatewayWordpress\Kernel\Initializer\HookInitializer;

class CashbeneInpostShipmentMethod extends \WC_Shipping_Method implements ComponentInitializationInterface {

    CONST PREFIX = 'cashbene_';
    CONST PARCEL_LOCKER_KEY = self::PREFIX.'parcel_locker_key';
    CONST PARCEL_LOCKER_ADDRESS1 = self::PREFIX.'parcel_locker_address1';
    CONST PARCEL_LOCKER_ADDRESS2 = self::PREFIX.'parcel_locker_address2';
    CONST SHIPMENT_ID = self::PREFIX.'inpost_parcel_locker';

    /** @var HookInitializer */
    private $hookInitializer;

    /**
     * Constructor for the gateway.
     */
    public function __construct($instance_id = 0) {
        $this->id = self::SHIPMENT_ID;
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Inpost Paczkomat - Cashbene Gateway', 'cashbene_gateway_plugin');
        $this->method_description = __('Cashbene Gateway inpost shipping method', 'cashbene_gateway_plugin');

        $this->supports = [
            'shipping-zones',
        ];

        $this->instance_form_fields = [
            'title'      => [
                'title' => __('Paczkomat', 'cashbene_gateway_plugin'),
                'type'        => 'text',
                'default' => __('Paczkomat', 'cashbene_gateway_plugin'),
                'desc_tip'    => false,
            ]
        ];
        $this->title = $this->get_option('title');
        $this->enabled = 'yes';
    }

    public function boot()
    {
        $this->hookInitializer = App::get('hookInitializer');
        $this->hookInitializer->addFilter('woocommerce_shipping_methods', $this, '_init');
        $this->hookInitializer->addFilter('woocommerce_admin_order_data_after_shipping_address', $this, 'displayAdminOrderMeta', 9);
    }

    public function _init($methods)
    {
        if (!is_cart() && !is_checkout() && !is_account_page()) {
            $methods[self::SHIPMENT_ID] = self::class;
        }

        return $methods;
    }

    /**
     * Show parcel locker address in admin panel order.
     *
     * @param $order
     */
    public function displayAdminOrderMeta($order)
    {
        if (!get_post_meta($order->get_id(), self::PARCEL_LOCKER_KEY, true)) {
            return;
        }
        remove_action('woocommerce_admin_order_data_after_shipping_address', 'inpost_paczkomaty_checkout_field_display_admin_order_meta');

        echo
            '<div>' .
            __('Selected parcel locker', 'cashbene_gateway_plugin') . ': ' . esc_attr(
            get_post_meta($order->get_id(), self::PARCEL_LOCKER_KEY, true)
            . ', ' .
            get_post_meta($order->get_id(), self::PARCEL_LOCKER_ADDRESS1, true)
            . ', ' .
            get_post_meta($order->get_id(), self::PARCEL_LOCKER_ADDRESS2, true)
        )
        .'</div>';
    }

    /**
     * Add meta to order to display on admin panel.
     *
     * @param $orderId
     * @param $lockerKey
     * @param $lockerAddress1
     * @param $lockerAddress2
     */
    public static function orderAddLockerAddress($orderId, $lockerKey, $lockerAddress1, $lockerAddress2)
    {
        update_post_meta($orderId, self::PARCEL_LOCKER_KEY, $lockerKey);
        update_post_meta($orderId, self::PARCEL_LOCKER_ADDRESS1, $lockerAddress1);
        update_post_meta($orderId, self::PARCEL_LOCKER_ADDRESS2, $lockerAddress2);
    }
}
