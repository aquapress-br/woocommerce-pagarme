<?php
/**
 * A utility class for managing API payloads, allowing easy
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Pagarme_Payload class.
 *
 * @since 1.0.0
 */
class WC_Pagarme_Model_Payload {

	/**
	 * The payload data.
	 *
	 * @var array
	 */
	public $payload = array();

	/**
	 * Constructor.
	 *
	 * Initializes the payload with the provided data.
	 *
	 * @param array $payload Optional. Initial payload data. Default is an empty array.
	 */
	public function __construct( $payload = array() ) {
		$this->payload = $payload;
	}

	/**
	 * Add a new key-value pair to the payload.
	 *
	 * @param string|null $key The key to add.
	 * @param mixed  $value The value to associate with the key.
	 * @return void
	 */
	public function add( $value, $key = null ) {
		if ( $key ) {
			isset( $this->payload[ $key ] ) {
				$this->payload[ $key ] = array_merge( $this->payload[ $key ], $value );
			} else {
				$this->payload[ $key ] = $value;
			}
		} else {
			$this->payload = array_merge( $this->payload, $value );
		}
	}

	/**
	 * Retrieve the entire payload as an array.
	 *
	 * @return array The full payload data.
	 */
	public function get() {
		return $this->payload;
	}
}
