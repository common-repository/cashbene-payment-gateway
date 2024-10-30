<?php

namespace Cashbene\GatewayWordpress\App\Component;

use Cashbene\Core\Dto\ShippingMethod;
use Cashbene\Core\Exception\HttpException;
use Cashbene\GatewayWordpress\App\Utils\InpostPaczkomatyIntegration;
use Cashbene\GatewayWordpress\App\Utils\WPDeskPaczkomatyInpostIntegration;
use Cashbene\GatewayWordpress\Kernel\App;

class AdminPage extends BaseComponent
{
    const PAGE_SLUG = 'cashbene-gateway-admin';
    const SETTINGS_GROUP = 'cashbene_gateway_plugin_settings_group';
    const MAIN_SETTINGS_SECTION = 'cashbene_gateway_plugin_main_settings_section';
    const SUPPORT_SETTINGS_SECTION = 'cashbene_gateway_plugin_main_support_section';
    const SHIPPING_METHOD_SETTINGS_SECTION = 'cashbene_gateway_plugin_shipping_method_settings_section';

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /** @var string */
    private $credentialsValidKey;

    /** @var mixed */
    private $optionsKey;

    /** @var array */
    private $availableLockerPlugins;

    /**
     * Start up
     */
    public function boot()
    {
        $this->optionsKey = App::get('DATABASE_OPTIONS_KEY');
        $this->options = App::get('databaseSettings');
        $this->credentialsValidKey = $this->optionsKey . '_credentials_valid';

        $this->hookInitializer->addAction('admin_menu', $this, 'addPluginPage');
        $this->hookInitializer->addAction('admin_init', $this, 'pageInit');
        $this->hookInitializer->addAction('update_option', $this, 'verifyCredentials', 100, 3);
        $this->hookInitializer->addAction("pre_update_option_{$this->optionsKey}", $this, 'removeShippingMethods', 100, 2);

        $this->availableLockerPlugins = array_filter([
            CashbeneInpostShipmentMethod::SHIPMENT_ID,
            InpostPaczkomatyIntegration::getShippingMethod(),
            WPDeskPaczkomatyInpostIntegration::getShippingMethod()
        ]);
    }

    /**
     * Add options page
     */
    public function addPluginPage()
    {
        add_menu_page(
            __('Cashbene Payment Gateway', 'cashbene_gateway_plugin'),
            __('Cashbene Gateway', 'cashbene_gateway_plugin'),
            'manage_options',
            self::PAGE_SLUG, //Page slug
            [$this, 'createAdminPage'], //Callback to print html
            'dashicons-cart', // Icon url
            68 // Position
        );
    }

    /**
     * Options page callback
     */
    public function createAdminPage()
    {
        echo App::get('templateLoader')->getTemplateContent('admin/admin_page');
    }

    /**
     * Register and add settings
     */
    public function pageInit()
    {
        register_setting(
            self::SETTINGS_GROUP, // Option group
            App::get('DATABASE_OPTIONS_KEY'), // Option name
            [$this, 'sanitize'] // Sanitize
        );

        add_settings_section(
            self::SUPPORT_SETTINGS_SECTION, // ID
            __('Technical Support', 'cashbene_gateway_plugin'), // Title
            [$this, 'displayTechnicalSupportLink'], // Callback
            self::PAGE_SLUG // Page
        );

        add_settings_section(
            self::MAIN_SETTINGS_SECTION, // ID
            __('Main Settings', 'cashbene_gateway_plugin'), // Title
            [$this, 'displaySectionInfo'], // Callback
            self::PAGE_SLUG // Page
        );

        $this->addStaticFields();

        if (isset($_GET['page']) && $_GET['page'] === self::PAGE_SLUG) {
            try {
                /** @throws HttpException */
                $cashbeneShippingMethods = App::get('cashbeneGateway')->shopContext()->getMerchantShippingMethods();
                $shippingMethods = $this->getShippingMethods();

                foreach ($cashbeneShippingMethods as $cashbeneShippingMethod) {
					if(!$cashbeneShippingMethod->active) continue;

                    if ($cashbeneShippingMethod->type === "POINT") {
                        $shippingMethodsFiltered = array_intersect_key($shippingMethods, array_flip($this->availableLockerPlugins));
                    } else {
                        $shippingMethodsFiltered = array_diff_key($shippingMethods, array_flip($this->availableLockerPlugins));
                    }

                    $field = [
                        'name'          => $cashbeneShippingMethod->name,
                        'fieldType'     => 'select',
                        'id'            => "shipping_methods[$cashbeneShippingMethod->id]",
                        'selectOptions' => $shippingMethodsFiltered
                    ];

                    add_settings_field(
                        $cashbeneShippingMethod->id, // Id
                        $cashbeneShippingMethod->name, // Title
                        [$this, 'fieldCallback'], // Callback
                        self::PAGE_SLUG, // Page
                        self::SHIPPING_METHOD_SETTINGS_SECTION, // Section
                        $field // Args
                    );
                }

                add_settings_section(
                    self::SHIPPING_METHOD_SETTINGS_SECTION, // ID
                    __('Shipping Method Settings', 'cashbene_gateway_plugin'), // Title
                    [$this, 'displaySectionInfo'], // Callback
                    self::PAGE_SLUG // Page
                );
            } catch (HttpException $httpException) {
                add_settings_section(
                    self::SHIPPING_METHOD_SETTINGS_SECTION, // ID
                    __('Shipping Method Settings', 'cashbene_gateway_plugin'), // Title
                    [$this, 'displayCredentialNotice'], // Callback
                    self::PAGE_SLUG // Page
                );
            }
        }
    }

    /**
     * @return array[]
     */
    private function getStaticFields()
    {
        return [
            'merchant_id' => [
                'name' =>  __('Merchant ID', 'cashbene_gateway_plugin'),
                'fieldType' => 'text'
            ],
            'client_id' => [
                'name' => __('Client ID', 'cashbene_gateway_plugin'),
                'fieldType' => 'text'
            ],
            'client_secret' => [
                'name' => __('Client secret', 'cashbene_gateway_plugin'),
                'fieldType' => 'password'
            ],
            'secret_key' => [
                'name' => __('Secret key', 'cashbene_gateway_plugin'),
                'fieldType' => 'password'
            ],
            'environment' =>   [
                'name' => __('Environment', 'cashbene_gateway_plugin'),
                'fieldType' => 'select',
                'selectOptions' => [
                    'sandbox' => __('Sandbox', 'cashbene_gateway_plugin'),
                    'production' => __('Production', 'cashbene_gateway_plugin')
                ]
            ],
            'terms_and_conditions_page' =>   [
                'name' => __('Terms and conditions page', 'cashbene_gateway_plugin'),
                'fieldType' => 'select',
                'selectOptions' => $this->getPageArray()
            ],
            'privacy_policy_page' =>   [
                'name' => __('Privacy policy page', 'cashbene_gateway_plugin'),
                'fieldType' => 'select',
                'selectOptions' => $this->getPageArray()
            ]
        ];
    }

    /**
     * @return array[]
     */
    private function getPageArray()
    {
        $pagesArray = [];
        foreach (get_pages() as $page) {
            $pagesArray[$page->ID] = $page->post_title;
        }

        return $pagesArray;
    }

    /**
     * @return void
     */
    private function addStaticFields()
    {
        $staticFields = $this->getStaticFields();

        foreach ($staticFields as $id => $field) {
            add_settings_field(
                $id, // Id
                $field['name'], // Title
                [$this, 'fieldCallback'], // Callback
                self::PAGE_SLUG, // Page
                self::MAIN_SETTINGS_SECTION, // Section
                $field + ['id' => $id] // Args
            );
        }
    }

    /**
     * @return array
     */
    private function getShippingMethods()
    {
        $registeredShippingMethods = WC()->shipping()->get_shipping_methods();

        $shippingMethods = [];
        foreach ($registeredShippingMethods as $shippingMethod) {
            $shippingMethods[$shippingMethod->id] = $shippingMethod->method_title;
        }

        return $shippingMethods;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array|null $inputs Contains all settings fields as array keys
     */
    public function sanitize($inputs)
    {
        $newInputs = [];
        foreach ($inputs as $key => $input) {
            if ($key == 'client_secret' || is_array($input)) {
                $newInputs[$key] = $input;
            } else {
                $newInputs[$key] = sanitize_text_field($input);
            }
        }

        return $newInputs;
    }

    /**
     * Print the Section text
     *
     * @return void
     */
    public function displaySectionInfo()
    {
        echo __('Enter your settings below:', 'cashbene_gateway_plugin');
    }

    /**
     * Print the Section with technical support link
     *
     * @return void
     */
    public function displayTechnicalSupportLink()
    {
        echo 'If there are problems using the plug-in, please send a ticket via <a href="https://pacificorg.atlassian.net/servicedesk/customer/portal/4" target="_blank">technical support form.</a><br>';
    }

    /**
     * Print the merchant credential notice
     *
     * @return void
     */
    public function displayCredentialNotice()
    {
        echo __('You must set the correct access credentials before setting this section', 'cashbene_gateway_plugin');
    }

    /**
     * @param $args
     * @return void
     * @throws \Exception
     */
    public function fieldCallback($args)
    {
        echo App::get('templateLoader')->getTemplateContent(
            "admin/{$args['fieldType']}_input",
            $args + ['options' => $this->options]
        );
    }

    public function removeShippingMethods($value, $oldValue)
    {
        $baseArray = [
            'merchant_id' => "",
            'client_id' => "",
            'client_secret' => "",
            'environment' => ""
        ];

        if ($oldValue == false) {
            $oldValue = [];
        }

        $newValue = array_intersect_key($value, $baseArray);
        $oldValue = array_intersect_key($oldValue, $baseArray);

        if ($newValue !== $oldValue) {
            if (isset($value['shipping_methods'])) {
                unset($value['shipping_methods']);
            }
        };

       return $value;
    }

    public function verifyCredentials($option, $oldValue, $value)
    {
        if ($option == $this->optionsKey) {
            $validCredentials = true;

            if (
                empty($value['merchant_id']) || empty($value['client_id'])
                || empty($value['client_secret']) || empty($value['secret_key'])
                || empty($value['environment'])
                || empty($value['terms_and_conditions_page']) || empty($value['privacy_policy_page'])
                || empty($value['shipping_methods']) || !array_filter($value['shipping_methods'])
            ) {
                $validCredentials = false;
            }

            update_option($this->credentialsValidKey, $validCredentials);
        }
    }
}
