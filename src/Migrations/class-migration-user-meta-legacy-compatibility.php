<?php

namespace Aquapress\Pagarme\Migrations;

/**
 * Child class for gateway version migration.
 * This migration is responsible for making the gateway compatible with legacy versions of the gateway (API V4).
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Migrations\User_Meta_Legacy_Compatibility class.
 *
 * @extends \Aquapress\Pagarme\Abstracts\Migration.
 */
class User_Meta_Legacy_Compatibility  extends \Aquapress\Pagarme\Abstracts\Migration {
    
	/**
     * @var string The target version for the migration.
     */
    public $version = '1.0.0-user_meta_legacy_compatibility';

    /**
     * Process the migration to the specified version.
     * 
     * Subclasses should implement this method to perform migration tasks.
     * 
     * @param string $current_version The current version of the dashboard.
     * @return bool Returns true if the migration was successful, otherwise false.
     */
    public function process( $current_version ): bool {
		// Update user meta for Pagar.me recipient data.
		return $this->update_pagarme_user_meta();
	}
	
	/**
	 * Update user meta for Pagar.me recipient and bank account IDs.
     * 
     * @return void.
     */
	function update_pagarme_user_meta() {
		// Get all users with the meta 'id_recipiente_pagarme'
		$users_with_id_recipiente_pagarme_meta = get_users([
			'meta_key' => 'id_recipiente_pagarme',
			'fields'   => ['ID'], // Retrieve only the user IDs
			'role__in' => ['seller', 'administrator'], // Limit to specific roles
		]);

		foreach ( $users_with_id_recipiente_pagarme_meta as $user ) {
			$user_id = $user->ID;

			// Get the value of 'id_recipiente_pagarme'
			$recipient_id = get_user_meta( $user_id, 'id_recipiente_pagarme', true );

			if ( $recipient_id ) {
				// Update the 'pagarme_recipient_id' meta with the same value
				update_user_meta( $user_id, 'pagarme_recipient_id', $recipient_id );
			}
		}

		// Get all users with the meta 'pagarme_recipiente_id'
		$users_with_pagarme_recipiente_id_meta = get_users([
			'meta_key' => 'pagarme_recipiente_id',
			'fields'   => ['ID'], // Retrieve only the user IDs
			'role__in' => ['seller', 'administrator'], // Limit to specific roles
		]);

		foreach ( $users_with_pagarme_recipiente_id_meta as $user ) {
			$user_id = $user->ID;

			// Get the value of 'pagarme_recipiente_id'
			$recipient_id = get_user_meta( $user_id, 'pagarme_recipiente_id', true );

			if ( $recipient_id ) {
				// Update the 'pagarme_recipient_id' meta with the same value
				update_user_meta( $user_id, 'pagarme_recipient_id', $recipient_id );
			}
		}

		// Get all users with the meta 'conta_bancaria_pagarme'
		$users_with_conta_bancaria_pagarme_meta = get_users([
			'meta_key' => 'conta_bancaria_pagarme',
			'fields'   => ['ID'], // Retrieve only the user IDs
			'role__in' => ['seller', 'administrator'], // Limit to specific roles
		]);

		foreach ( $users_with_conta_bancaria_pagarme_meta as $user ) {
			$user_id = $user->ID;

			// Get the value of 'conta_bancaria_pagarme'
			$bank_account_id = get_user_meta( $user_id, 'conta_bancaria_pagarme', true );

			if ( $bank_account_id ) {
				// Update the 'pagarme_recipient_bank_account_id' meta with the same value
				update_user_meta( $user_id, 'pagarme_recipient_bank_account_id', $bank_account_id );
			}
		}
		
		// Get all users with the meta 'pagarme_bank_account_id'
		$users_with_pagarme_bank_account_id_meta = get_users([
			'meta_key' => 'pagarme_bank_account_id',
			'fields'   => ['ID'], // Retrieve only the user IDs
			'role__in' => ['seller', 'administrator'], // Limit to specific roles
		]);

		foreach ( $users_with_pagarme_bank_account_id_meta as $user ) {
			$user_id = $user->ID;

			// Get the value of 'pagarme_bank_account_id'
			$bank_account_id = get_user_meta( $user_id, 'pagarme_bank_account_id', true );

			if ( $bank_account_id ) {
				// Update the 'pagarme_recipient_bank_account_id' meta with the same value
				update_user_meta( $user_id, 'pagarme_recipient_bank_account_id', $bank_account_id );
			}
		}
		
		return true;	
	}


}