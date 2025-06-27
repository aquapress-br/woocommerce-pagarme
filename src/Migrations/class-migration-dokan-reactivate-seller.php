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
 * \Aquapress\Pagarme\Migrations\Dokan_Reactivate_Seller class.
 *
 * @extends \Aquapress\Pagarme\Abstracts\Migration.
 */
class Dokan_Reactivate_Seller extends \Aquapress\Pagarme\Abstracts\Migration {

	/**
	 * @var string The target version for the migration.
	 */
	public $version = '1.0.0-dokan_reactivate_seller';

	/**
	 * Process the migration to the specified version.
	 *
	 * @param string $current_version The current version of the dashboard.
	 * @return bool Returns true if the migration was successful, otherwise false.
	 */
	public function process( $current_version ): bool {
		$args = array(
			'role'    => 'seller',
			'fields'  => 'ID',
			'number'  => -1,
			'orderby' => 'ID',
			'order'   => 'ASC',
		);

		$user_query = new \WP_User_Query( $args );
		$user_ids   = $user_query->get_results();

		if ( empty( $user_ids ) ) {
			return true;
		}

		foreach ( $user_ids as $user_id ) {
			$auto_deactivate_seller = get_user_meta( $user_id, 'wc_pagarme_auto_deactivate_seller_processed', true );

			if ( 'yes' == $auto_deactivate_seller ) {
				update_user_meta( $user_id, 'dokan_enable_selling', 'yes' );
			}
		}

		return true;
	}
}
