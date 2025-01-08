(function( $ ) {

	'use strict';

	$( function() {
		$( document ).ready( function(){
			var pixContaner = $( document ).find( '.pagarme-pix-instructions-container' );
			// Check if the PIX payment instructions screen is visible.
			if ( pixContaner.length > 0 ) {
				// Copy text to clip board.
				$( '#pagarme-copy-button' ).on( 'click', function() {
					// Get PIX qrcode key.
					var qrcode = $( this ).data( 'qrcode' );
					// Copy text and return result.
					var successful = copyTextToClipboard( qrcode );
					// Toggle button state.
					if ( successful ) {
						// Add success class to the button
						$( this ).addClass( 'copy-successful' );
					}
				}).on( 'mouseout', function() { 
					// Get button reerence.
					var $buttonCopy = $( this );
					// Disable successful copy button state.
					setTimeout( function() {
						$buttonCopy.removeClass( 'copy-successful' );
					}, 5000 );
				} );
				// Check order pay.
				setInterval( checkUpdatedOrderStatus, 7000 );
			}
		} );

		/**
		 * Copy text to clip board.
		 */
		function copyTextToClipboard( text ) {
			// Create a temporary textarea element
			var $textArea = $('<textarea>');

			// Apply styles to the textarea
			$textArea.css({
				position: 'fixed',
				top: 0,
				left: 0,
				width: '2em',
				height: '2em',
				padding: 0,
				border: 'none',
				outline: 'none',
				boxShadow: 'none',
				background: 'transparent',
			});

			// Set the text and append it to the body
			$textArea.val(text);
			$('body').append($textArea);

			// Select the text in the textarea
			$textArea.focus().select();

			try {
				// Execute the copy command
				var result = document.execCommand( 'copy' );
				// Return result command;
				return result;
			} catch ( err ) {
				return false;
			}

			// Remove the temporary textarea
			$textArea.remove();
		}
		
		/**
		 * Function to fetch the updated body content and check payment status.
		 * The payment confirmation screen is displayed seconds after payment automatically.
		 */
		function checkUpdatedOrderStatus() {
			// Get the current URL
			var currentUrl = window.location.href;
			// Load the updated body content from the current URL
			$.get( currentUrl, function ( html, status, xhr ) {
				if ( xhr.status == 200 ) {
					// Verify that the updated body does not contain the class "pagarme-pix-instructions-container".
					// Redirection occurs when the order is updated with a status other than on hold or pending.
					var isOrderPaid = $( html ).find( '.pagarme-pix-instructions-container' );
					// Reload the page if the order is paid.
					if ( isOrderPaid.length < 1 ) {
						location.reload(); //Your payment was successful.
					}
				} else {
					console.log( 'Failed to fetch updated content:', xhr.status, xhr.statusText );
				}
			});
		}

	});

}( jQuery ));

