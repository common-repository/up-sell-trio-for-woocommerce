<?php

//
// Short code `ccom_fbt` displays Frequently Bought Together on the front-end single product pages.
//

// Hook Into Single Products
add_action( 'woocommerce_after_single_product_summary', function() {

	// Determine Cross Sells Default
	$ccom_fbt_cross_sells = get_option( 'ccom_fbt_cross_sells', true )
		? get_option( 'ccom_fbt_cross_sells', true ) : 'no';

	// Output
	echo do_shortcode(
		sprintf(
			'[ccom_fbt cross_sells="%s"]',
			$ccom_fbt_cross_sells
		)
	);

} );

// Add Shortcode
add_shortcode( 'ccom_fbt', function( $atts ) {

	// Setup Default Settings
	$atts = shortcode_atts(
		[
			'cross_sells' => 'no',
			'heading' => __(
				'Frequently purchased with ' . get_the_title(),
				'ccom-upsell-trio'
			),
			'heading_tag' => 'h2',
		], $atts, 'ccom_fbt'
	);

	// Front End Or Bail
	if( is_admin() ) {
		return;
	}

	// Coupons Must Be Enabled In WooCommerce
	if( ! get_option( 'woocommerce_enable_coupons' ) === 'yes' ) {
		return;
	}

	// Woo Product Or Bail
	global $product;
	if( ! $product || ! method_exists( $product, 'is_purchasable' ) || ! method_exists( $product, 'is_in_stock' ) ) {
		return;
	}

	// Must Be Published And In Stock
	if( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return;
	}

	// Maybe Front Pad With Cross Sells
	$priority_one = $atts['cross_sells'] === 'yes'
		? $product->get_cross_sell_ids()
		: [];

	// Get Cross-Sells And Recommended Product IDs
	$recommendations = array_merge(

		// Priority One: Cross-Sells
		(array) $priority_one,

		// Priority Two: Our Data
		(array) $product->get_meta( 'ccom_recommendations', true ),

		// Priority Three: End Pad WooCommerce Defaults
		(array) wc_get_related_products( $product->get_id(), 5 ),

		// Priority Four: Random Products
		(array) wc_get_products(
			[ 'limit' => 5, 'return' => 'ids', 'orderby' => 'rand' ]
		)

	);

	// Bail When No Recommendations Found
	if( ! $recommendations ) {
		return;
	}

	// Enqueue Blocks Checkbox Styles For Classic Themes
	wp_enqueue_style( 'wc-blocks-packages-style' );

	// Enqueue JavaScripts
	wp_enqueue_script(
		'ccom_fbt',
		plugin_dir_url( __FILE__ ) . 'frequently-bought-together.js',
		[], null, [ 'in_footer' => true ]
	);

	// Loop Recommended Products
	foreach( $recommendations as $i => $rec_product_id ) {

		// Ensure Recommended Product Is Queryable
		$rec_product = wc_get_product( $rec_product_id );
		if(
			! $rec_product
			|| ! method_exists( $rec_product, 'is_in_stock' )
			|| ! method_exists( $rec_product, 'is_purchasable' )
			|| ! method_exists( $rec_product, 'is_type' )
			|| ! method_exists( $rec_product, 'is_visible' )
		) {
			unset( $recommendations[$i] );
			continue;
		}

		// Remove A Non-Purchaseable Or Hidden Recommendation
		if(
			! $rec_product->is_in_stock()
			|| ! $rec_product->is_purchasable()
			|| ! $rec_product->is_visible()
		) {
			unset( $recommendations[$i] );
			continue;
		}

	}

	// Bail When No Recommendations Remain
	if( ! $recommendations ) {
		return;
	}

	// Current Product Output
	$this_product = sprintf(
		'
			<div id="ccom_fbt_self_id" class="product" data-price="%s">
				%s<br>%s: %s
				<input type="hidden" name="ccom_fbt_a2c[]" value="%d">
			</div>
		',
		$product->get_price(),
		$product->get_image( 'medium', [ 'style' => 'display:inline-block;' ] ),
		__( 'This product', 'ccom-upsell-trio' ),
		$product->get_name(),
		$product->get_id()
	);

	// Unique Recommendations Only
	$recommendations = array_unique( $recommendations );

	// Cut To Top Three Recommendations
	$recommendations = array_slice( $recommendations, 0, 3 );

	// Offers Accumulator
	$offers = [];

	// Loop Recommended Products
	foreach( $recommendations as $rec_product_id ) {
		$rec_product = wc_get_product( $rec_product_id );
		$rating = $rec_product->get_rating_count()
			? sprintf(
				'%s (%s)',
				wc_get_rating_html(
					$rec_product->get_average_rating(),
					$rec_product->get_rating_count()
				),
				$rec_product->get_rating_count()
			)
			: '';

		// Variable Product Maybe Swap To Default Variation
		if( $rec_product->is_type( 'variable' ) ) {
			$default_attributes = $rec_product->get_default_attributes();
			if( $default_attributes ) {
				$prefixed_slugs = array_map(
					function( $pa_name ) {
						return 'attribute_'. $pa_name;
					}, array_keys( $default_attributes )
				);
				$default_variation_id = ( new \WC_Product_Data_Store_CPT() )->find_matching_product_variation(
					$rec_product, array_combine( $prefixed_slugs, $default_attributes )
				);
				if( $default_variation_id ) {
					$rec_product_id = $default_variation_id;
					$rec_product = wc_get_product( $default_variation_id );
				}
			}
		}

		// Variation Product Maybe Switch To Default Variation
		else if( $rec_product->is_type( 'variation' ) ) {
			$rec_product_parent = wc_get_product( $rec_product->get_parent_id() );
			$rating = sprintf(
				'%s (%s)',
				wc_get_rating_html(
					$rec_product_parent->get_average_rating(),
					$rec_product_parent->get_rating_count()
				),
				$rec_product_parent->get_rating_count()
			);
			$default_attributes = $rec_product_parent->get_default_attributes();
			if( $default_attributes ) {
				$prefixed_slugs = array_map(
					function( $pa_name ) {
						return 'attribute_'. $pa_name;
					}, array_keys( $default_attributes )
				);
				$default_variation_id = ( new \WC_Product_Data_Store_CPT() )->find_matching_product_variation(
					$rec_product_parent, array_combine( $prefixed_slugs, $default_attributes )
				);
				if( $default_variation_id ) {
					$rec_product_id = $default_variation_id;
					$rec_product = wc_get_product( $default_variation_id );
				}
			}
		}

		// Accumulate Recommended Product Markup
		$offers[] = sprintf(
			'
				<div class="product" data-price="%s">
					<p>
						<a href="%s">
							%s
						</a>
					</p>
					%s
					<p class="wc-block-components-checkbox">
						<label>
							<input class="wc-block-components-checkbox__input" type="checkbox" aria-invalid="false" name="ccom_fbt_a2c[]" value="%d" checked="checked">
							<svg class="wc-block-components-checkbox__mark" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 20"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"></path></svg>
							<span class="wc-block-components-checkbox__label">+ %s %s</span>
						</label>
					</p>
				</div>
			',
			$rec_product->get_price(),
			$rec_product->get_permalink(),
			$rec_product->get_image( 'thumbnail', [ 'style' => 'display:inline-block;' ] ),
			$rating,
			$rec_product_id,
			$rec_product->get_name(),
			wc_price( $rec_product->get_price() )
		);

	}

	$cta_info = sprintf(
		'
			%s
			<span class="price">
				<ins>
					<span class="woocommerce-Price-amount amount">
						<bdi>
							<span class="woocommerce-Price-currencySymbol">$</span><span
								id="ccom_fbt_total" style="text-decoration:underline;"></span>
						</bdi>
					</span>
				</ins>
				&nbsp;
				<del>
					<span class="woocommerce-Price-amount amount">
						<bdi>
							<span class="woocommerce-Price-currencySymbol">$</span><span
								id="ccom_fbt_subtotal" style="text-decoration:line-through;"></span>
						</bdi>
					</span>
				</del>
				&nbsp;
				<span class="you-save">
					%s 10%%
					(<span class="woocommerce-Price-amount amount">
						<bdi>
							<span class="woocommerce-Price-currencySymbol">$</span><span
								id="ccom_fbt_discount"></span>
						</bdi>
					</span>)
				</span>
			</span>
		',
		__( 'Price for selected combo:', 'ccom-upsell-trio' ),
		__( 'You Save', 'ccom-upsell-trio' )
	);

	// Get And Set Template
	$output = file_get_contents(
		plugin_dir_path( __FILE__ ) . 'frequently-bought-together.html'
	);
	$output = str_replace( 'THIS_PRODUCT', $this_product, $output );
	$output = str_replace( 'OFFER_1', isset( $offers[0] ) ? $offers[0] : '', $output );
	$output = str_replace( 'OFFER_2', isset( $offers[1] ) ? $offers[1] : '', $output );
	$output = str_replace( 'OFFER_3', isset( $offers[2] ) ? $offers[2] : '', $output );
	$output = str_replace( 'CTA_INFO', $cta_info, $output );
	$output = str_replace( 'CTA_TEXT', __( 'Add Selected Combo to Cart', 'ccom-upsell-trio' ), $output );

	// Output Form And Row Of Products
	return sprintf(
		'<form method="POST" id="ccom_fbt_form" action="%s" style="clear:both;">%s %s</form>',
		wc_get_cart_url(),
		sprintf(
			'<%s>%s</%s>',
			esc_html( $atts['heading_tag'] ),
			esc_html( $atts['heading'] ),
			esc_html( $atts['heading_tag'] )
		),
		apply_filters( 'the_content', $output )
	);

} );