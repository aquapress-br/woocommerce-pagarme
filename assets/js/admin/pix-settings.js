jQuery(function( $ ) {
	'use strict';
	
	const ID_PREFIX = '#woocommerce_';

	/**
	 * Checkbox ID.
	 */
	var wc_pagarme_testmode = ID_PREFIX + 'wc_pagarme_pix_testmode';

	/**
	 * Object to handle Cielo admin functions.
	 */
	var wc_pagarme_admin = {
		isTestMode: function() {
			return $( wc_pagarme_testmode ).is( ':checked' );
		},

		/**
		 * Initialize.
		 */
		init: function() {
			$( document.body ).on( 'change', wc_pagarme_testmode, function() {
				var public_key = $( ID_PREFIX + 'wc_pagarme_pix_public_key' ).parents( 'tr' ).eq( 0 ),
					secret_key = $( ID_PREFIX + 'wc_pagarme_pix_secret_key' ).parents( 'tr' ).eq( 0 ),
					public_key_sandbox = $( ID_PREFIX + 'wc_pagarme_pix_public_key_sandbox' ).parents( 'tr' ).eq( 0 ),
					secret_key_sandbox = $( ID_PREFIX + 'wc_pagarme_pix_secret_key_sandbox' ).parents( 'tr' ).eq( 0 );
		
				if ( $( this ).is( ':checked' ) ) {
					secret_key_sandbox.show();
					public_key_sandbox.show();
					secret_key.hide();
					public_key.hide();
				} else {
					secret_key_sandbox.hide();
					public_key_sandbox.hide();
					secret_key.show();
					public_key.show();
				}
			} );

			$( wc_pagarme_testmode  ).change();
		}
	};

	wc_pagarme_admin.init();

});