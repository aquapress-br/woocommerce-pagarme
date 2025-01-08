<?php defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_pagarme_sanitize_string' ) ) {
	/**
	 * Sanitize text.
	 *
	 * @param  string|int $string String to sanitize.
	 * @return string
	 */
	function wc_pagarme_sanitize_string( $string ) {
		// Sanitize string
		$string = sanitize_text_field( $string );

		return $string;
	}
}

if ( ! function_exists( 'wc_pagarme_split_digit' ) ) {
	/**
	 * Split number with digit.
	 *
	 * @param  string|int $string String to convert.
	 * @return array
	 */
	function wc_pagarme_split_digit( $string ) {
		$value = explode( '-', $string );

		if ( isset( $value[0], $value[1] ) ) {
			return array(
				'number' => $value[0],
				'digit'  => $value[1],
			);
		}

		return array(
			'number' => $string,
			'digit'  => false,
		);
	}
}

if ( ! function_exists( 'wc_pagarme_only_numbers' ) ) {
	/**
	 * Get only numbers in a string.
	 *
	 * @param  string|int $string String to convert.
	 * @return string
	 */
	function wc_pagarme_only_numbers( $string ) {
		return preg_replace( '([^0-9])', '', $string );
	}
}

if ( ! function_exists( 'wc_pagarme_asaas_date_formatter' ) ) {

	function wc_pagarme_asaas_date_formatter( $value ) {
		// Converte a data brasileira para objeto DateTime
		$date = DateTime::createFromFormat( 'Y-m-d', $value );
		// Formata a data para o formato americano (dd/mm/yyyy)
		$value = $date->format( 'd/m/Y' );

		return $value;
	}
}

if ( ! function_exists( 'wc_pagarme_get_banks_list' ) ) {
	/**
	 * Get banks list.
	 *
	 * @return array banks array
	 */
	function wc_pagarme_get_banks_list() {
		$file = file_get_contents( WC_PAGARME_PATH . 'assets/js/marketplace/banks.json' );
		$json = json_decode( $file, true );

		return $json;
	}
}

if ( ! function_exists( 'wc_pagarme_get_occupations_list' ) ) {
	/**
	 * Get occupations list.
	 *
	 * @return array occupations array
	 */
	function wc_pagarme_get_occupations_list() {
		$file = file_get_contents( WC_PAGARME_PATH . 'assets/js/marketplace/occupations.json' );
		$json = json_decode( $file, true );

		return $json;
	}
}

if ( ! function_exists( 'wc_pagarme_get_states_list' ) ) {
	/**
	 * Get states list.
	 *
	 * @return array states array
	 */
	function wc_pagarme_get_states_list() {
		$file = file_get_contents( WC_PAGARME_PATH . 'assets/js/marketplace/states.json' );
		$json = json_decode( $file, true );

		return $json;
	}
}

if ( ! function_exists( 'wc_pagarme_get_phone_information' ) ) {
	/**
	 * Get information from a Brazilian phone number.
	 *
	 * @param string $number The phone number in the format '+55 (xx) xxxxx-xxxx'.
	 * @param string $type The type of information desired: 'number', 'area_code', or 'country_code'.
	 *
	 * @return string|int Returns the requested information. If $type is 'number', it returns only the local number.
	 */
	function wc_pagarme_get_phone_information( $number, $type ) {
		// Remove any non-numeric characters from the phone number
		$cleaned_number = preg_replace( '/\D/', '', $number );

		// Add the country code '+55' if not present
		if ( strlen( $cleaned_number ) <= 11 && substr( $cleaned_number, 0, 2 ) !== '55' ) {
			$cleaned_number = '55' . $cleaned_number;
		}

		// Extract information from the number
		switch ( $type ) {
			case 'number':
				// Remove the 'country_code' and 'area_code' to return only the local number
				return substr( $cleaned_number, 4 );
			case 'area_code':
				return substr( $cleaned_number, 2, 2 );
			case 'country_code':
				return '55';
			default:
				return false;
		}
	}
}

if ( ! function_exists( 'wc_pagarme_add_checkout_notice' ) ) {
	/**
	 * Add checkout notice.
	 *
	 * @param string $notice The text of notice.
	 * @param string $type   The notice type.
	 */
	function wc_pagarme_add_checkout_notice( $notice, $type = 'error' ) {
		if ( is_checkout() ) {
			wc_add_notice( $notice, $type );
		}
	}
}

if ( ! function_exists( 'wc_pagarme_add_days_to_date' ) ) {
	/**
	 * Adds a specified number of days to the current date and returns it in ISO 8601 format.
	 * The function works with the timezone of Brasília (America/Sao_Paulo).
	 *
	 * @param int $days The number of days to add to the current date.
	 * @return string The resulting date in the format "YYYY-MM-DDT00:00:00Z".
	 */
	function wc_pagarme_add_days_to_date( $days ) {
		// Define the timezone for Brasília (America/Sao_Paulo)
		$timezone = new DateTimeZone( 'America/Sao_Paulo' );

		// Create a DateTime instance for the current date and time in the specified timezone
		$date = new DateTime( 'now', $timezone );

		// Add the specified number of days to the date
		$date->modify( "+$days days" );

		// Return the date formatted as "YYYY-MM-DDT00:00:00Z"
		return $date->format( 'Y-m-d\T00:00:00\Z' );
	}
}
