<?php

namespace Cashbene\GatewayWordpress\App\Component;

use Cashbene\GatewayWordpress\Kernel\App;

class PaymentBox extends BaseComponent {

    public function boot()
    {
        if (App::get('credentialsValid')) {
            $this->hookInitializer->addAction('woocommerce_before_add_to_cart_button', $this, 'renderButton');
            $this->hookInitializer->addAction('woocommerce_proceed_to_checkout', $this, 'renderButton');
            $this->hookInitializer->addAction('wp_footer', $this, 'renderModal');
        }
    }

    public function renderButton()
    {
        echo App::get('templateLoader')->getTemplateContent('box');
    }

    public function renderModal()
    {
        if (function_exists('is_product') && (is_product() || is_cart())) {
            echo App::get('templateLoader')->includeHtmlFile('modal');
        }
    }
}
