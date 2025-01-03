(function( $ ) {
	'use strict';
	$( function() {
		// Store the installment options.
		$.data( document.body, 'pagarme_card_installments', $( '#pagarme-card-payment-form #pagarme-installments' ).html() );				$(document).ready(function(){			// Add jQuery.Payment support for Elo and Aura.			if ( $.payment.cards ) {				var cards = [];				$.each( $.payment.cards, function( index, val ) {					cards.push( val.type );				});				if ( typeof $.payment.cards[0].pattern === 'undefined' ) {					if ( -1 === $.inArray( 'aura', cards ) ) {						$.payment.cards.unshift({							type: 'aura',							patterns: [5078],							format: /(\d{1,6})(\d{1,2})?(\d{1,11})?/,							length: [19],							cvcLength: [3],							luhn: true						});					}				} else {					if ( -1 === $.inArray( 'elo', cards ) ) {						$.payment.cards.push({							type: 'elo',							pattern: /^(636[2-3])/,							format: /(\d{1,4})/g,							length: [16],							cvcLength: [3],							luhn: true						});					}					if ( -1 === $.inArray( 'aura', cards ) ) {						$.payment.cards.unshift({							type: 'aura',							pattern: /^5078/,							format: /(\d{1,6})(\d{1,2})?(\d{1,11})?/,							length: [19],							cvcLength: [3],							luhn: true						});					}								}			}		});

		/**
		 * Set the installment fields.
		 *
		 * @param {String} card
		 */
		function setInstallmentsFields( card ) {
			var installments = $( '#pagarme-payment-form #pagarme-installments' );
			$( '#pagarme-payment-form #pagarme-installments' ).empty();
			$( '#pagarme-payment-form #pagarme-installments' ).prepend( $.data( document.body, 'pagarme_card_installments' ) );
						if ( 'discover' === card ) {
				$( 'option', installments ).not( '.pagarme-at-sight' ).remove();
			}
		}

		// Set on update the checkout fields.
		$( document.body ).on( 'ajaxComplete', function() {
			$.data( document.body, 'pagarme_card_installments', $( '#pagarme-card-payment-form #pagarme-installments' ).html() );
			setInstallmentsFields( $( 'body #pagarme-card-payment-form #pagarme-card-brand option' ).first().val() );
		});
		// Set on change the card brand.
		$( document.body ).on( 'change', '#pagarme-card-payment-form #pagarme-card-number', function() {
			setInstallmentsFields( $.payment.cardType( $( this ).val() ) );
		});		  		 // Process the credit card data when submit the checkout form. 		$( 'body' ).on( 'click', '#place_order', function(e) {			if ( ! $( '#payment_method_wc_pagarme_creditcard' ).is( ':checked' ) || $( '.pagarme-card-option:checked' ).val() ) {				return true;			}						if ( ! $( '#add_payment_method, .checkout.woocommerce-checkout' )[0].checkValidity() ) {				$( '#add_payment_method, .checkout.woocommerce-checkout' )[0].reportValidity();								e.preventDefault();			} 							/* var process = {				success: function(data) {					console.log(data);					return true;				},				fail: function(error) {					console.error(error);				},			}					PagarmeCheckout.init( process.success, process.fail );			*/		});
	});
}( jQuery ));
