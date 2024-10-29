<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

// Scheduler
add_action( 'admin_notices', function() {

	// Require Action Scheduler
	if( ! function_exists( 'as_has_scheduled_action' ) ) {
		printf(
			'
				<div class="notice notice-warning is-dismissible">
					<p>%s</p>
				</div>
			',
			__(
				'Warning: WooCommerce is required by Upsell Trio for WooCommerce plugin.',
				'ccom-upsell-trio'
			)
		);
		return;
	}

	// Schedule: Delete Expired Coupons
	$hook = 'ccom_delete_expired_coupons';
	if( ! as_has_scheduled_action( $hook ) ) {

		// Set New Schedule
		as_schedule_recurring_action(
			current_time( 'timestamp', 1 ),
			DAY_IN_SECONDS,
			$hook
		);

	}

	// Count Published Products
	$args = [ 'limit' => -1, 'return' => 'ids', 'status' => 'publish' ];
	$products = wc_get_products( $args );

	// Loop Product Batches
	$limit = 500;
	for( $i = 0; $i < sizeof( $products ); $i += $limit ) {

		// Schedule: FBT Builder Per Product Batch
		$hook = 'ccom_fbt_builder';
		$args = [ 'limit' => $limit, 'offset' => $i ];
		if( ! as_has_scheduled_action( $hook, $args ) ) {
			as_schedule_recurring_action(
				current_time( 'timestamp', 1 ),
				WEEK_IN_SECONDS,
				$hook, $args
			);
		}

	} // End Product Batch Loop

} );

// Run: Delete Expired Coupons
add_action( 'ccom_delete_expired_coupons', function() {

	$args = [
		'posts_per_page' => -1,
		'post_type' => 'shop_coupon',
		'post_status' => 'publish',
		'meta_query' => [
			'relation' => 'AND',
			[
				'key' => 'date_expires',
				'value' => '',
				'compare' => '!='
			],
			[
				'key' => 'date_expires',
				'value' => current_time( 'timestamp' ),
				'compare' => '<='
			]
		]
	];
	$coupons = get_posts( $args );
	if( ! $coupons ) {
		return;
	}
	foreach( $coupons as $coupon ) {
		wp_trash_post( $coupon->ID );
	}

} );

// Run: Recommended Products Builder
add_action( 'ccom_fbt_builder', function( $limit, $offset ) {

	// Require WooCommerce
	if( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Get Product IDs By Email Address
	global $wpdb;
	$sql = OrderUtil::custom_orders_table_usage_is_enabled()
		? $wpdb->prepare(
			"
				SELECT o.billing_email AS billing_email,
					GROUP_CONCAT( oim.meta_value ) AS product_ids
				FROM {$wpdb->prefix}woocommerce_order_items oi
				JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim
					ON oi.order_item_id = oim.order_item_id
					AND oim.meta_key = '_product_id'
				JOIN {$wpdb->prefix}wc_orders o
					ON oi.order_id = o.ID
					AND o.billing_email IS NOT NULL
				GROUP BY o.billing_email
			"
		) : $wpdb->prepare(
			"
				SELECT pm.meta_value AS billing_email,
					GROUP_CONCAT( oim.meta_value ) AS product_ids
				FROM {$wpdb->prefix}woocommerce_order_items oi
				JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim
					ON oi.order_item_id = oim.order_item_id
					AND oim.meta_key = '_product_id'
				JOIN {$wpdb->prefix}postmeta pm
					ON oi.order_id = pm.post_id
					AND pm.meta_key = '_billing_email'
				GROUP BY pm.meta_value
			"
		);
	$results = $wpdb->get_results( $sql, ARRAY_A );

	// Remove Duplicate Products
	foreach( $results as &$row ) {
		$row['product_ids']
			= array_unique( explode( ',', $row['product_ids'] ) );
	}

	// Remove Single Product Purchasers
	foreach( $results as $i => $row ) {
		if( sizeof( $row['product_ids'] ) < 2 ) {
			unset( $results[$i] );
		}
	}

	// Get Batch Of Published Products
	$args = [
		'limit' => $limit,
		'offset' => $offset,
		'status' => 'publish',
	];
	$products = wc_get_products( $args );

	// Loop Products
	foreach( $products as $product ) {
		$product_id1 = $product->get_id();

		// Search For Relationships
		$relatives = [];
		foreach( $results as $result ) {
			if( ! in_array( $product_id1, $result['product_ids'] ) ) {
				continue;
			}
			foreach( $result['product_ids'] as $product_id2 ) {
				if( (string) $product_id2 === (string) $product_id1 ) {
					continue;
				}
				if( empty( $relatives[$product_id2] ) ) {
					$relatives[$product_id2] = 0;
				}
				$relatives[$product_id2] ++;
			}
		}

		// None Found
		if( ! $relatives ) {
			continue;
		}

		// Sort
		arsort( $relatives );

		// Get Top Five Relatives
		$top_five = array_keys( array_slice( $relatives, 0, 5, true ) );

		// Save Meta Data
		$product->update_meta_data( 'ccom_recommendations', $top_five );
		$product->save();

	} // End Loop Products

}, 10, 2 );