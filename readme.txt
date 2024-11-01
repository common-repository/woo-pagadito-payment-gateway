=== WooCommerce Pagadito Payment Gateway ===
Contributors: pgdevelopers
Tags: pagadito, ecommerce, e-commerce, woocommerce, payment, gateway, checkout, credit card, debit card, visa, mastercard, bitcoin, crypto, shop, shopping
Requires at least: 4.9
Tested up to: 6.6.1
Requires PHP: 5.6
Stable tag: 6.5
License: LGPLv3 or later
License URI: https://www.gnu.org/licenses/lgpl.html

Pagadito allows you to pay online in a safe, easy and reliable way.

== Description ==

It is the easy and secure platform to sell through your own website, withdraw funds in your local bank account and pay online!

= Take payments with credit, debit cards and cryptocurrencies easily and directly in your store. =

Accept all Visa and MasterCard cards and now also Bitcoin directly in your store with the Pagadito payment gateway for WooCommerce.
Without worrying about the exchange rate, receive your payments in local currency.

The Pagadito add-on for WooCommerce allows you to accept payments directly in your store through the Pagadito API.

Pagadito is available in the following countries:

* United States
* Mexico
* El Salvador
* Guatemala
* Honduras
* Nicaragua
* Costa Rica
* Panama
* Belize
* Uruguay
* Ecuador
* Puerto Rico
* Dominican Republic
* Trinidad and Tobago
* Guyana
* Suriname
* Bahamas
* Sint Maarten
* [and receive payments from more than 60 countries](https://comercios.pagadito.com/)

Pagadito allows you to pay online safely, easily and reliably.

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater
* WordPress 4.9 or greater
* WooCommerce 3.7 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "WooCommerce Pagadito" and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favorite FTP application. The
WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

If on the off-chance you do encounter issues with the shop/category pages after an update you simply need to flush the permalinks by going to WordPress > Settings > Permalinks and hitting 'save'. That should return things to normal.

== Frequently Asked Questions ==

= What is Pagadito? =

Pagadito is an online payment system that allows you, quickly and safely, to receive and make online payments from all of Central America and other countries in the world, transfer and withdraw your money from bank accounts in your area in any of the countries where it operates. The Pagadito payment gateway can be integrated into a website or send payments by email. It is endorsed and certified by PCI DSS, Level 1, which guarantees compliance with the highest quality and security standards to make and receive secure payments.

For more information visit: [https://bit.ly/3HbYjIC](https://bit.ly/3HbYjIC).

= Why use Pagadito? =

Pagadito has no installation fee, no monthly fees or hidden costs: You are only charged when you earn money! The earnings are transferred to your Pagadito Comercio account where you choose when you want to transfer your money to your local bank.

Pagadito allows the reuse of cards. When a customer pays, a Pagadito Persona account is created. If they create another order, they can pay using the Pagadito Persona account where the card is already associated. Massive time savings for returning customers.

= Does this support both production mode and sandbox mode for testing? =

Yes it does - production and sandbox mode is driven by how you connect.  You may choose to connect in either mode, and disconnect and reconnect in the other mode whenever you want.

= Where can I find documentation? =

For help setting up and configuring, please refer to our [user guide](https://dev.pagadito.com/index.php?mod=docs/)

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the Plugin Forum.

= Will this plugin work with my theme? =

Yes, this plugin will work with any theme, but may require some styling to make it match nicely. If you're
looking for a theme with built in WooCommerce integration we recommend [Storefront](http://www.woothemes.com/storefront/).

= Where can I request new features or report bugs? =

New feature requests and bugs reports can be made in the plugin forum.

== Screenshots ==

1. Panel to configure the Pagadito payment gateway.
2. Form to Checkout and select payment method.
3. Screen to proceed with the payment.
4. Data form of the credit card.
5. Billing form with the option to create or not a Pagadito person account.
6. Form for payment with Pagadito account.
7. Selection of associated cards or entry of a new one.
8. Form for payment with Bitcoin.
9. Selection of associated wallets and type of cryptocurrency.
10. Payment information to apply from your wallet.
11. Successful payment confirmation screen.
12. Return to website and payment confirmation completed.
13. Payment notification sent to the customer and merchant.

== Changelog ==

= 6.1.1 - 2024-08-27 =
* Fix - Compatibility of headers in webhooks
* Tweak - WooCommerce 9.2.3 compatibility

= 6.1 - 2024-04-29 =
* Enhancement - Added support for local currency in Guatemala
* Tweak - WooCommerce 8.6.1 compatibility

= 6.0 - 2024-01-29 =
* Tweak - WordPress 6.4.2 compatibility
* Tweak - WooCommerce 8.5.0 compatibility
* Add - Support for WooCommerce Blocks

= 5.1.2 - 2021-12-23 =
* Tweak - WordPress 5.8.2 compatibility
* Tweak - WooCommerce 6.0.0 compatibility
* Tweak - Change of logos Pagadito with Bitcoin icon
* Tweak - Add question in readme file FAQ - What is Pagadito?

= 5.1.1 - 2021-06-30 =
* Tweak - WooCommerce 5.4.1 compatibility
* Fix - Remove language files woo-pagadito-payment-gateway-es_ES

= 5.1 - 2021-05-26 =
* Enhancement - Validation in the minimum version of compatibility with WooCommerce
* Tweak - Update to use get_shipping_total()
* Tweak - Update to use get_coupon_codes()
* Tweak - Update to use wc_get_page_id()
* Tweak - WooCommerce plugin download link change
* Tweak - Change in the minimal version of WooCommerce 3.7
* Tweak - Change in the minimal version of Wordpress 4.9
* Tweak - Minor changes to text

= 5.0 - 2021-03-23 =
* Tweak - Wordpress 5.7 compatibility
* Tweak - WooCommerce 5.1.0 compatibility
* Tweak - Change of Pagadito theme
* Tweak - Change of icon and screenshots
* Tweak - Change from Text Domain to woo-pagadito-payment-gateway
* Tweak - Minor changes to text

= 4.4.3 - 2020-06-04 =
* Tweak - Update to use $order->get_status().
* Tweak - Update Approval Number label

= 4.4.2 - 2020-06-03 =
* Tweak - WooCommerce 4.2 compatibility

= 4.4.1 - 2020-05-08 =
* Tweak - Description change in readme file and in Pagadito class

= 4.4 - 2020-04-15 =
* Enhancement - Sending of custom parameters to the account in Pagadito was enabled

= 4.3.1 - 2019-07-16 =
* Fix - Remove language files es_ES due to translation error

= 4.3 - 2019-06-24 =
* Enhancement - Added Pagadito Number Approval on the received order page
* Tweak - Sections of the control panel (Return URL and Webhooks)

= 4.2 - 2019-06-11 =
* Fix - When adding shipping cost, it showed error PG2002
* Fix - When adding discount coupon, it showed error PG2002

= 4.1.1 - 2019-05-07 =
* Fix - Translation a english of new features of version 1.1

= 4.1 - 2019-05-07 =
* Enhancement - Added Return URL
* Enhancement - Webhooks URL added
* Fix - Change of logos of Pagadito
* Tweak - Change of description in Pagadito logo
* Tweak - Update of PHP version in readme file

= 4.0.1 - 2019-04-29 =
* Tweak - Readme file in English

= 4.0 - 2019-04-29 =
* New Stable version

[See changelog for all versions](https://dev.pagadito.com/index.php?mod=docs&hac=des).
