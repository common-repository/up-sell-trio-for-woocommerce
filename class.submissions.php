<?php

//
// Accepts ccom_fbt_a2c add-to-cart requests from FBT front end, set-up temporary coupon, adds to cart.
//

add_action( 'template_redirect', function() {

	// Arguments Required
	if(
		empty( $_REQUEST['ccom_fbt_a2c'] )
		|| ! is_array( $_REQUEST['ccom_fbt_a2c'] )
	) {
		return;
	}

	// Loop Requests
	$added_to_cart = [];
	foreach( $_REQUEST['ccom_fbt_a2c'] as $product_id ) {

		// Sanitize
		$product_id = intval( $product_id );

		// Add To Cart
		$added_to_cart[$product_id] = WC()->cart->add_to_cart( $product_id, 1 );

	}

	// Cart Message
	$added_to_cart_pid = array_pop( array_reverse( array_keys( $added_to_cart ) ) );
	if( $added_to_cart_pid ) {
		wc_add_to_cart_message( [ $added_to_cart_pid => 1 ], true );
	}

	// Withhold Current Product From Discount
	unset( $added_to_cart[$added_to_cart_pid] );

	// Create Unique Coupon
	$coupon_code = sprintf(
		__(
			'Frequently Bought Together %04X%04X',
			'ccom-upsell-trio'
		),
		strtoupper( mt_rand( 0, 65535 ) ),
		strtoupper( mt_rand( 0, 65535 ) )
	);
	$coupon = new WC_Coupon();
	$coupon->set_code( $coupon_code );
	$coupon->set_description(
		__(
			'Frequently Bought Together discount',
			'ccom-upsell-trio'
		)
	);
	$coupon->set_discount_type( 'percent' ); // fixed_cart|percent|fixed_product
	$coupon->set_amount( 10 );
	$coupon->set_free_shipping( false );
	$coupon->set_date_expires( current_time( 'timestamp' ) + WEEK_IN_SECONDS );
	//$coupon->set_minimum_amount( );
	//$coupon->set_maximum_amount( );
	$coupon->set_individual_use( false );
	$coupon->set_exclude_sale_items( false );
	$coupon->set_product_ids( array_keys( $added_to_cart ) );
	//$coupon->set_excluded_product_ids( );
	//$coupon->set_product_categories( );
	//$coupon->set_excluded_product_categories( );
	//$coupon->set_email_restrictions( );
	$coupon->set_usage_limit( 1 );
	//$coupon->set_limit_usage_to_x_items( );
	//$coupon->set_usage_limit_per_user( );
	$coupon->save();

	// Apply Coupon
	WC()->cart->apply_coupon( $coupon_code );

} );