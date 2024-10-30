=== Cashbene Payment Gateway ===
Contributors: InterSynergy
Donate link:
Tags: payment, cashbene, payment gateway, blik, card, inpost, paczkomaty, inpost paczkomaty
Requires at least: 5.3
Tested up to: 6.2
Requires PHP: 7.1 - 7.4.33
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Cashbene payment gateway plugin for Woocommerce.

== Description ==

With Cashbene, you can access multiple stores with just one account. No need to sign up for individual stores, simply enter your email address and start shopping right away. Enjoy the convenience of accessing payments through the Cashbene.com gateway!

== Screenshots ==

1. [Step one] Product page
2. [Verification] Enter your email
3. [First purchase] First purchase registration form
4. [Subsequent purchase] Verify account via email and password
5. [Step two] Order modal
6. [Step three] Success

== Frequently Asked Questions ==

= Inpost paczkomaty =

Once the plugin is enabled, navigate to Woocommerce -> Settings -> Shipping -> Country and add a new shipping option of type "Paczkomaty (Cashbene)".

= Settings =
Plugin works correctly with PHP versions: 7.1 up to 7.4.33.
If you are using PHP 8.0, you need to update symfony/serializer to version 5.4.22 or higher. Plugin was tested with version 5.4.22 and possibly works with version 5.3.12.

Choose the "Cahsbene Gateway" tab from the left menu and fill out all the fields. You can obtain the access key for Cashbene applications through PHP Intl module.
Plugin requires PHP Intl module

= Shipping methods =

Ensure that the delivery methods from Cashbene are correctly assigned to the corresponding delivery methods in WooCommerce in the plugin settings.
= 1.0.5 =
* Remove button to add credit card.
* Change link for payments.

= 1.0.4 =
* Update Settings in FAQ. Add information about PHP and Symfony versions

== Changelog ==

= 1.0.3 =
* Change shortcut php tag to full php tag
* Add esc_attr before variables in box.php
* Plugin tested with Wordpress 6.2 version

= 1.0.2 =
* Fix attributes in product DTO

= 1.0.1 =
* Add dev mode for payU in sandbox environment

= 1.0.0 =
* Hello Cashbene!

== Upgrade Notice ==

= 1.0.0 =
* Hello Cashbene!