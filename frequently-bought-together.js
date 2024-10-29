// Pricing Calculator
function ccom_fbt_calculate() {

	// Loop FBT Items
	var price_addons = 0;
	var num_checked = 0;
	document.querySelectorAll( '.ccom_fbt_row .product' )
		.forEach( function( list_item ) {

		// Find Item Checkbox
		var checkbox = list_item.querySelector( 'input[type=checkbox]' );

		// Current Product Item
		if( checkbox && checkbox.checked ) {
			num_checked ++;
			price_addons += parseFloat( list_item.getAttribute( 'data-price' ) );
		}

	} );

	// Handle None Checked
	if( num_checked < 1 ) {
		document.getElementById( 'ccom_fbt_totals' ).style.display = 'none';
		return;				
	}

	// Calculate Totals
	var price_current = parseFloat(
		document.getElementById( 'ccom_fbt_self_id' ).getAttribute( 'data-price' )
	);
	var price_subtotal = price_current + price_addons;
	var price_discount = price_addons * 0.1;
	var price_total = price_subtotal - price_discount;

	// Display Totals Row
	document.getElementById( 'ccom_fbt_totals' ).style.display = 'block';

	// Refresh Currency Symbols
	document.querySelectorAll( '#ccom_fbt_totals span.woocommerce-Price-currencySymbol' )
		.forEach( function( span ) {
		const currency = document
			.querySelector( 'span.woocommerce-Price-currencySymbol' )
			.innerHTML;
		const currencyText = document.createTextNode( currency );
		span.innerHTML = '';
		span.appendChild( currencyText );
	} );

	// Display Subtotal Value
	document.getElementById( 'ccom_fbt_subtotal' ).innerHTML = '';
	document.getElementById( 'ccom_fbt_subtotal' ).appendChild(
		document.createTextNode( price_subtotal.toFixed( 2 ) )
	);

	// Display Discount Value
	document.getElementById( 'ccom_fbt_discount' ).innerHTML = '';
	document.getElementById( 'ccom_fbt_discount' ).appendChild(
		document.createTextNode( price_discount.toFixed( 2 ) )
	);

	// Display Total Value
	document.getElementById( 'ccom_fbt_total' ).innerHTML = '';
	document.getElementById( 'ccom_fbt_total' ).appendChild(
		document.createTextNode( price_total.toFixed( 2 ) )
	);

}

// DOM Loaded Event
document.addEventListener( 'DOMContentLoaded', function() {

	// Display Initially
	ccom_fbt_calculate();

	// Listen For Checkbox Changes
	document.querySelectorAll( '.ccom_fbt_row .product' )
		.forEach( function( list_item ) {
		var checkbox = list_item.querySelector( 'input[type=checkbox]' );
		if( checkbox ) {
			checkbox.addEventListener( 'change', function( event ) {
				ccom_fbt_calculate();
			} );
		}
	} );

	// Variations Form Found
	const variations_form = document.querySelector( 'form.variations_form' );
	if( variations_form !== null ) {

		// Get Variation Pricing Data
		var product_variations = variations_form.getAttribute( 'data-product_variations' );
		product_variations = JSON.parse( product_variations );

		// Loop Drop Downs
		document.querySelectorAll( 'table.variations select' )
			.forEach( function( select ) {

			// Variation Change Event
			// VanillaJS Can't Hear jQuery So We Use jQuery Binding
			jQuery( select ).change( function() {

				// Get Pricing For This Option
				product_variations.forEach( function( row ) {

					// Selected Variation Only
					if( row.attributes[select.name] == select.value ) {

						// Update Self Product ID
						document.getElementById( 'ccom_fbt_self_id' )
							.querySelector( 'input[type=hidden]' ).value = row.variation_id;

						// Update First Item Price Data
						document.getElementById( 'ccom_fbt_self_id' )
							.setAttribute( 'data-price', row.display_price );

						// Recalculate Totals
						ccom_fbt_calculate();

					}

				} );

			} ); // End Variation Change Event

			// Initially Fire Change Event - Multi-Currency Compatibility
			let changeEvent = new Event( 'change' );
			select.dispatchEvent( changeEvent );

		} ); // End Loop Drop Downs

	} // End Variations Form Found

} ); // End DOM Loaded Event