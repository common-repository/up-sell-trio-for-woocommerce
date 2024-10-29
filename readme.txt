=== Up-sell Trio for WooCommerce ===
Contributors: seanconklin
Donate link: https://codedcommerce.com/donate
Tags: woocommerce, products, upsell, order bumps, recommended products, out of stock alternatives
Requires at least: 6.0
Tested up to: 6.6.1
Requires PHP: 7.4
Stable tag: 1.8.2
License: GPLv2 or later

Minimalist and high-efficiency plugin under 1,000 lines of code packed with WooCommerce up-sell features: Frequently Bought Together, Out-of-stock Alternatives, Order Bumps for Cart and Checkout.

== Feature: Frequently Bought Together and Product Recommendations ==

Generates recommended product relationships from purchase history by email address using the Action Scheduler.

Creates a short code and dynamically inserts a frequently bought together promotion offering customers a percentage discount on add-on products.

Uses real dynamically generated WooCommerce Coupons to aide with reporting and other compatibilities, thus coupons must be enabled in WooCommerce settings.

== Feature: Out-of-stock Alternatives ==

Displays two up-sell products above the add-to-cart form on single product pages when out of stock. Converts traffic to otherwise dead end discontinued or temporarily unavailable product pages.

== Feature: Order Bumps on Cart and Checkout ==

Inserts a block based order bump offer onto cart and checkout pages when a trigger product is in cart and offer product(s) are not in cart.

== Frequently Asked Questions ==

= Where can I see examples? =

* [Best Chinese Medicines](https://bestchinesemedicines.com/ "Best Chinese Medicines")

= How do I configure these features? =

Decisions not options! There is no settings page!

Frequently Bought Together and Product Recommendations:

Activating this plugin will insert the Frequently Bought Together section into the single product page via the WooCommerce hook `woocommerce_after_single_product_summary`.

If you need to insert the Frequently Bought Together feature via shortcode, such as with a page builder like Elementor, please follow this format: `[ccom_fbt cross_sells="no" heading="Frequently purchased together…" heading_tag="h2"]`.

Cross sells feature will give preference to any products linked as cross-sells in the product settings.

Recommendations data can also be used for your Related Products by adding the following into your [Code Snippets](https://wordpress.org/plugins/code-snippets/ "Code Snippets") or child theme `functions.php` file:

~~~
add_filter( 'woocommerce_related_products', function( $related_posts, $product_id ) {
	$product = wc_get_product( $product_id );
	$recommendations = (array) $product->get_meta( 'ccom_recommendations', true );
	return array_merge( $recommendations, $related_posts );
}, 10, 2 );
~~~

Out-of-stock Alternatives:

Up-sell products configured in the WooCommerce product edit screen will be shown on out-of-stock single product pages above the empty add-to-cart form.

Order Bumps on Cart and Checkout:

Order bumps are configured on the Reusable Blocks page located within the Appearance admin menu.

Create one or more reusable blocks containing your order bump design using any WooCommerce Blocks that you wish to, such as the Hand Picked Products block. Name your reusable block "Product Name -and- Product Name -for- Product Name" where the product names represent the product(s) in your design followed by the trigger product.

Your offer will display on the cart and checkout pages when the trigger product is on cart and no other products titled in the reusable block are in the cart.

You may also display the order bump(s) using the shortcode `[ccom_order_bump]`. This is useful with the new WooCommerce Cart and Checkout blocks.

= How is this plugin funded? =

This plugin is funded by clients of Coded Commerce, LLC funding feature requests for development. When we develop useful code under GPL licensing we share it on our site as Code Snippets and in some cases package great features like these into free plugins so everybody can benefit, including the originating client via bug fixes and others' funded feature requests.

We also welcome donations via the "Donate to this plugin" button towards the bottom of the right sidebar on the WordPress.org plugin page.

= Which themes and plugins has this been tested with? =

* Themes we've tested with: WooCommerce Storefront (default theme), Twenty Twenty-Two (block theme), Hello Elementor (basic theme for a popular page builder).
* Related plugins we've tested with: [WooCommerce Multi-Currency](https://woocommerce.com/products/multi-currency/ "WooCommerce Multi-Currency")

We suggest installing onto a staging or sandbox environment to test compatibilities. We recommend sandbox environments using LocalWP and disabling all other plugins except those being tested.

Staging environments for eCommerce sites can be problematic (customers finding it, emails going out, integrations connecting, etc.). Sandbox environments with all non critical plugins enabled is safest.

= Where do I go for help with any issues? =

To report bugs, please click the Support tab, search for any preexisting report, and add yourself to it by commenting or open a new issue.

To request new compatibilities or features, please consider hiring the developer of this plugin or another developer who can provide us with code enhancements for review.

Paid premium support is also available for those looking for one-on-one help with their specific WordPress installation. Visit our website in the link above and contact us from there.

== Screenshots ==

1. Frequently Bought Together feature on a single product page.
2. Frequently Bought Together dynamic coupon in the cart.
3. Out-of-stock up-sells displaying on a single product page.
4. Reusable Blocks menu listing an order bump for a product.
5. Reusable Blocks editor showing an order bump being designed.
6. Cart page showing an example order bump design in blocks.

== Changelog ==

= 1.8.2 on 2024-09-17 =
* Fixed: Extra HTML paragraph tags within the FBT template.

= 1.8.1 on 2024-09-09 =
* Fixed: HPOS incompatibility of Frequent Bought Together data populater.

= 1.8 on 2024-04-05 =
* Added: Padding of random products for FBT when no calculated nor default recommendations exist.
* Updated: Block HTML for FBT needed updates, especially mobile breaking of the offer row.
* Fixed: Avoid showing (0) rating when no ratings exist.

= 1.7 on 2024-01-06 =
* Added: Support for using product slugs within order bump titles.
* Added: Support for multiple order bump display, sorted alphabatically.

= 1.6 on 2023-10-18 =
* Added: Woo product ratings onto FBT offers.
* Updated: FBT design is now powered by WordPress blocks and uses larger Woo Checkout Block checkboxes.
* Fixed: FBT calculation problem when offer is a variation other than the default variation.

= 1.5 on 2023-07-28 =
* Added: Tested and declared support for WooCommerce core HPOS / COT feature.

= 1.4 on 2023-07-19 =
* Updated: FBT heading to include reference to the product in view for SEO.

= 1.3.3 on 2023-07-12 =
* Fixed: FBT should not show catalog visibility hidden product recommendations.

= 1.3.2 on 2023-05-17 =
* Fixed: Rare PHP error after upgrading to WP 6.2.1 when using FBT shortcode within a blockified single product block template.

= 1.3.1 on 2023-04-26 =
* Fixed: removed stock status check for FBT parent for blocks compatibility.
* Fixed: missing ARIA labels on FBT checkbox fields.

= 1.3 on 2023-03-20 =
* Added: New attribute heading_tag for the FBT shortcode.
* Updated tested-to for both WP and Woo cores.

= 1.2.1 on 2023-01-07 =
* Fixed: PHP crash on order bump when Woo cart isn't booted.

= 1.2 on 2022-11-25 =
* Added WP option to enable cross-sells pre-padding on FBT in non short code cases.

= 1.1.1 on 2022-11-22 =
* Fixed: Crash in out-of-stock up-sells feature when an up-sell linked product DNE.

= 1.1 on 2022-11-19 =
* Added: Scheduled action for FBT expired coupon auto trash.
* Fixed: Some missing gettext / translation wrappers.

= 1.0.1 on 2022-11-10 =
* Fixed: error editing cart page containing shortcode for OB feature.
* Fixed: FBT to only display unique products, no duplicates.
* Fixed: order bump match variation product parent titles and return output for shortcode.

= 1.0 on 2022-11-09 =
* Initial commit of plugin already running on two client sites and tested in a sandbox environment as well.
