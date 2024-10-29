<?php
/**
 * Plugin Name: Up-sell Trio for WooCommerce
 * Plugin URI: https://codedcommerce.com/shop/
 * Description: High efficiency WooCommerce up-sell products bundle featuring frequently bought together, out of stock alternatives, cart and checkout order bump.
 * Version: 1.8.2
 * Author: Coded Commerce, LLC
 * Author URI: https://codedcommerce.com
 * WC requires at least: 6.0
 * WC tested up to: 9.3.1
 * License: GPLv2 or later
 */

// Declare Support For HPOS
add_action( 'before_woocommerce_init', function() {
	if( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables', __FILE__, true
		);
	}
} );

// Plugins Page Link To Settings
add_filter(
	'plugin_action_links_ccom-upsell-trio/ccom-upsell-trio.php',
	function( $links ) {
	$settings = [
		'settings' => sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'edit.php?post_type=wp_block' ),
			__( 'Reusable blocks', 'ccom-upsell-trio' )
		),
	];
	return array_merge( $settings, $links );
} );

// Require Plugin Files
require_once( 'class.action-schedules.php' );
require_once( 'class.order-bump.php' );
require_once( 'class.out-of-stock-upsells.php' );
require_once( 'class.frequently-bought-together.php' );
require_once( 'class.submissions.php' );
