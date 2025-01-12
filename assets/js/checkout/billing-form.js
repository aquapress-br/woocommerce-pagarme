(function( $ ) {
	'use strict';
	
	$( function() {
		
		$( '.form-row.hidden' ).hide();		
		$( '#billing_nationality' ).select2()

		$( '#billing_persontype, #billing_nationality' ).on( 'change', function () {
			var persontype = $( '#billing_persontype' ).val(),
				country = $( '#billing_nationality' ).val();

			$( '#billing_cpf_field, #billing_birthdate_field, #billing_company_field, #billing_cnpj_field, #billing_taxvat_field' ).hide().removeClass( 'validate-required' ).find( 'label .optional' ).hide();

			if ( 'BR' === country ) {				
				if ( '1' === persontype ) {
					$( '#billing_nationality_field' ).show();
					$( '#billing_nationality + .select2' ).css( 'width', '100%' );
					$( '#billing_birthdate_field' ).show().find( 'label .optional' ).show();
					$( '#billing_cpf_field' ).show().addClass( 'validate-required' );
					
					$( '#billing_cpf_field label .required' ).remove();
					$( '#billing_cpf_field label' ).append( ' <abbr class="required">*</abbr>' );
				}

				if ( '2' === persontype ) {
					$( '#billing_company_field, #billing_cnpj_field' ).show().addClass( 'validate-required' );

					$( '#billing_company_field label .required, #billing_cnpj_field label .required' ).remove();
					$( '#billing_company_field label, #billing_cnpj_field label' ).append( ' <abbr class="required">*</abbr>' );
					$( '#billing_nationality_field' ).hide();
					$( '#billing_nationality' ).val( 'BR' );
				}
			} else {				
				$( '#billing_taxvat_field' ).show().addClass( 'validate-required' );
				$( '#billing_taxvat_field label .required' ).remove();
				$( '#billing_taxvat_field label' ).append( ' <abbr class="required">*</abbr>' );
					
				if ( '1' === persontype ) {
					$( '#billing_birthdate_field' ).show().find( 'label .optional' ).show();					
				}

				if ( '2' === persontype ) {
					$( '#billing_company_field' ).show().addClass( 'validate-required' );

					$( '#billing_company_field label .required' ).remove();
					$( '#billing_company_field label' ).append( ' <abbr class="required">*</abbr>' );
				}
			}

		}).change().select2();
		
		$( '#billing_phone' ).intlTelInput({ initialCountry: 'BR' }).on( 'countrychange', function() {
			var countryData = $( '#billing_phone' ).intlTelInput( 'getSelectedCountryData' );
			$( '#billing_phone_country' ).val( countryData.dialCode );
		} );
		
		$( '#billing_phone_country' ).val( $( '#billing_phone' ).intlTelInput( 'getSelectedCountryData' ).dialCode );  
	});

}( jQuery ));