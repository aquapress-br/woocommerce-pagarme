<?php

namespace Aquapress\Pagarme\Migrations;

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Migrations\Dokan_Deactivate_Seller_Products class.
 *
 * Unpublishes products from sellers who have been previously marked as deactivated
 * using the meta 'wc_pagarme_auto_deactivate_seller_processed'.
 *
 * @since 1.0.0
 * @version 1.0.0
 */
class Dokan_Deactivate_Seller_Products extends \Aquapress\Pagarme\Abstracts\Migration {

	/**
	 * Target version of the migration.
	 *
	 * @var string
	 */
	public $version = '1.0.0-dokan_deactivate_seller_products';

	/**
	 * Executes the product deactivation migration.
	 *
	 * @param string $current_version Current dashboard version.
	 * @return bool True if migration was successful, false otherwise.
	 */
	public function process( $current_version ): bool {
		$args = array(
			'role'       => 'seller',
			'fields'     => 'ID',
			'number'     => -1,
			'orderby'    => 'ID',
			'order'      => 'ASC',
			'meta_query' => array(
				array(
					'key'   => 'wc_pagarme_auto_deactivate_seller_processed',
					'value' => 'yes',
				),
			),
		);

		$user_query = new \WP_User_Query( $args );
		$user_ids   = $user_query->get_results();

		if ( empty( $user_ids ) ) {
			return true;
		}

		foreach ( $user_ids as $user_id ) {
			$products = get_posts( array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'author'      => $user_id,
				'numberposts' => -1,
				'fields'      => 'ids',
			) );

			foreach ( $products as $product_id ) {
				// Update product to draft and mark as processed
				wp_update_post( array(
					'ID'          => $product_id,
					'post_status' => 'draft',
				) );

				update_post_meta( $product_id, 'wc_pagarme_auto_deactivate_product_processed', 'yes' );
			}
		}

		return true;
	}
}
