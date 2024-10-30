<?php

namespace Cashbene\GatewayWordpress\App\Component;

use Cashbene\GatewayWordpress\Kernel\App;

class ErrorNotice extends BaseComponent {

    public function boot()
    {
        if (!App::get('credentialsValid')) {
            $this->hookInitializer->addAction('admin_notices', $this, 'renderError');
        } else {
            $this->hookInitializer->addAction('admin_notices', $this, 'checkShippmentConfigured');
        }
    }

    public function renderError()
    {
        $link = menu_page_url(AdminPage::PAGE_SLUG, false);
        $message = sprintf(
            __('To use the Cashbene Payment Gateway, you need to configure your <a href="%s">merchant access data and shipping methods.</a>', 'cashbene_gateway_plugin'),
            $link
        );

        echo App::get('templateLoader')->getTemplateContent('admin/empty_merchant_data_notice', ['message' => $message]);
    }

    public function checkShippmentConfigured() {
        $cashbeneShippingMethods = App::get('cashbeneGateway')->shopContext()->getMerchantShippingMethods();

        $missingShippingConfig = [];
        foreach ($cashbeneShippingMethods as $cashbeneShippingMethod) {
            if(!$cashbeneShippingMethod->active) continue;
            if (isset(App::get('databaseSettings')['shipping_methods'][$cashbeneShippingMethod->id]) && App::get('databaseSettings')['shipping_methods'][$cashbeneShippingMethod->id] != '') {

            } else {
                $missingShippingConfig[] = $cashbeneShippingMethod->name;
            }
        }

        if (!empty($missingShippingConfig)) {
            $link = menu_page_url(AdminPage::PAGE_SLUG, false);
            $message = sprintf(
                __('Cashbene Gateway - Some of shipping methods are not configured <a href="%s">check</a>', 'cashbene_gateway_plugin'),
                $link
            );

            echo App::get('templateLoader')->getTemplateContent('admin/empty_merchant_data_notice', ['message' => $message]);
        }
    }
}
