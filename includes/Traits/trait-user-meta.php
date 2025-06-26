<?php

namespace Aquapress\Pagarme\Traits;

/**
 * Trait user meta data.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Traits\User_Meta trait.
 *
 * @since 1.0.0
 */
trait User_Meta {

	/**
	 * Get user Pagar.me user meta.
	 *
	 * @param  int  $user_id
	 * @param  string  $meta_key
	 * @param  bool  $testmode
	 * @return mixed
	 */
	public static function get_user_option( $user_id, $meta_key, $testmode = false ) {
		if ( strpos( $meta_key, 'pagarme_' ) !== 0 ) {
			$meta_key = 'pagarme_' . $meta_key;
		}
		if ( $testmode === true || in_array( $testmode, ['yes', 'on'] ) ) {
			$meta_key = "{$meta_key}_sandbox";
		}
		
		return get_user_meta( $user_id, $meta_key, true );
	}

	/**
	 * Set user Pagar.me user meta.
	 *
	 * @param  int  $user_id
	 * @param  string  $meta_key
	 * @param  string  $meta_value
	 * @param  bool  $testmode
	 * @return mixed
	 */
	public static function set_user_option( $user_id, $meta_key, $meta_value, $testmode = false ) {
		if ( strpos( $meta_key, 'pagarme_' ) !== 0 ) {
			$meta_key = 'pagarme_' . $meta_key;
		}
		if ( $testmode === true || in_array( $testmode, ['yes', 'on'] ) ) {
			$meta_key = "{$meta_key}_sandbox";
		}
		
		return update_user_meta( $user_id, $meta_key, $meta_value );
	}

	/**
	 * Get WP user by Pagar.me recipient ID.
	 *
	 * @param  string  $recipient_id
	 * @param  bool  $testmode
	 * @return WP_User|false
	 */
	public static function get_user_by_recipient_id( $recipient_id, $testmode = false ) {
		// Define meta to load users
		$meta_key = "pagarme_recipient_id";
		if ( ! ( false === $testmode || 'no' === $testmode ) ) {
			$meta_key = "{$meta_key}_sandbox";
		}
		// Check if the recipient ID was provided
		if ( $recipient_id ) {
			// Search for the user by meta_key and meta_value
			$user_query = new \WP_User_Query(
				array(
					'meta_key'   => $meta_key ,
					'meta_value' => $recipient_id,
					'number'     => 1,
				)
			);

			// Check for results
			if ( ! empty( $user_query->get_results() ) ) {
				return $user_query->get_results()[0]; // Retorna o primeiro usuário encontrado
			}
		}

		// Returns false if there is no match or if $recipient_id is empty
		return false;
	}
}
