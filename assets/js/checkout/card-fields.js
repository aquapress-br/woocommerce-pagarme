(function( $ ) {
	'use strict';

	$( function() {
		// Add mask to form fields
		function InitPagarmeMaskFields() {
			$('#pagarme-card-expiry').mask('00-0000');
		}
		
		// Reload input mask in checkout page 
		$(document.body).on('load updated_checkout', function(event, data) {
			if ( $( '#payment_method_wc_pagarme_creditcard' ).is( ':checked' ) ) { 
				setTimeout(InitPagarmeMaskFields(), 1000);
			}
		});

		// Load input mask in myaccount method add
		if( $(document.body).find('#add_payment_method').length ) {
			if ( $( '#payment_method_wc_pagarme_creditcard' ).is( ':checked' ) ) { 
				InitPagarmeMaskFields();
			}
		}
	});

}( jQuery ));
