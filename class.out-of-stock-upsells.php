<?php

//
// Out of stock product summaries show any up-sells as alternatives.
//

add_action( 'woocommerce_before_template_part', function( $template_name ) {

	// Specific Template Insertions
	switch( $template_name ) {
		case 'single-product/add-to-cart/simple.php':
		case 'single-product/add-to-cart/variable.php':
			break;
		default:
			return;
	}

	// Out Of Stock Only
	global $product;
	if( ! $product || $product->is_in_stock() ) {
		return;
	}

	// Get Upsells
	$upsells = $product->get_upsells();
	if( ! $upsells ) {
		return;
	}

	// Variations Yield To Parent Product IDs
	foreach( $upsells as &$product_id_inner ) {
		$product_inner = wc_get_product( $product_id_inner );
		if( ! $product_inner || ! method_exists( $product_inner, 'is_type' ) ) {
			unset( $product_id_inner );
			continue;
		}
		if( $product_inner->is_type( 'variation' ) ) {
			$product_id_inner = $product_inner->get_parent_id();
		}
	}

	// Show Upsells Template
	echo wpautop(
		__(
			'Currently <strong>out of stock</strong> – Consider these alternatives:',
			'ccom-upsell-trio'
		)
	);
	echo do_shortcode(
		sprintf(
			'[products limit="2" columns="2" ids="%s"]',
			implode( ',', $upsells )
		)
	);
	return;

} );