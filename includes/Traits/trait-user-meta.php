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
	 * Get WP user by Pagar.me recipient ID.
	 *
	 * @param  string  $recipient_id
	 * @return WP_User|false
	 */
	public static function get_user_by_recipient_id( $recipient_id ) {
		// Check if the recipient ID was provided
		if ( $recipient_id ) {
			// Search for the user by meta_key and meta_value
			$user_query = new \WP_User_Query(
				array(
					'meta_key'   => 'pagarme_recipient_id',
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
