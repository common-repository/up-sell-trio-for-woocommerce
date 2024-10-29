<?php

//
// Short code that offers specified products on carts that don't contain it already.
//

add_action( 'admin_menu', function() {

	add_submenu_page(
		'themes.php',
		__( 'Reusable blocks', 'ccom-upsell-trio' ),
		__( 'Reusable blocks', 'ccom-upsell-trio' ),
		'manage_options',
		'edit.php?post_type=wp_block'
	);

} );

add_action( 'woocommerce_before_cart', function() {
	echo do_shortcode( '[ccom_order_bump]' );
} );

add_action( 'woocommerce_before_checkout_form', function() {
	echo do_shortcode( '[ccom_order_bump]' );
} );

add_shortcode( 'ccom_order_bump', function( $atts ) {

	// WooCommerce Or Bail
	if( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Front End Or Bail
	if( is_admin() ) {
		return;
	}

	// Bail On Null Or Empty Cart
	if( is_null( WC()->cart ) || WC()->cart->is_empty() ) {
		return;
	}

	// Get And Loop Block Patterns
	$output = '';
	$args = [
		'order' => 'ASC',
		'orderby' => 'title',
		'post_type' => 'wp_block',
		'posts_per_page' => -1,
		'status' => 'publish',
	];
	foreach( get_posts( $args ) as $block ) {

		// Only With -for- Within Post Slug
		if( strstr( $block->post_name, '-for-' ) === false ) {
			continue;
		}

		// Get Upsell And Trigger Products
		list( $upsell_product_names, $trigger_product_name )
			= explode( ' -for- ', $block->post_title, 2 );
		$upsell_product_names = explode( ' -and- ', $upsell_product_names );

		// Upsell Products Must Not Be On Cart
		foreach( $upsell_product_names as $upsell_product_name ) {
			foreach( WC()->cart->get_cart() as $cart_item ) {
				$cart_item_product =  wc_get_product( $cart_item['data']->get_id() );
				if( $cart_item_product->is_type( 'variation' ) ) {
					$cart_item_product = wc_get_product( $cart_item_product->get_parent_id() );
				}	
				if(
					$cart_item_product->get_title() === $upsell_product_name
					|| $cart_item_product->get_slug() === $upsell_product_name
				) {
					continue 3;
				}
			}
		}

		// Match Trigger Product
		$trigger_product = false;
		foreach( WC()->cart->get_cart() as $cart_item ) {
			$cart_item_product = wc_get_product( $cart_item['data']->get_id() );
			if( $cart_item_product->is_type( 'variation' ) ) {
				$cart_item_product = wc_get_product( $cart_item_product->get_parent_id() );
			}
			if(
				$cart_item_product->get_title() === $trigger_product_name
				|| $cart_item_product->get_slug() === $trigger_product_name
			) {
				$trigger_product = true;
			}
		}
		if( $trigger_product ) {
			$output .= apply_filters( 'the_content', $block->post_content );
		}

	}

	return $output;

} );