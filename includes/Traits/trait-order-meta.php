<?php

namespace Aquapress\Pagarme\Traits;

/**
 * Trait order meta data.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Traits\Order_Meta trait.
 *
 * @since 1.0.0
 */
trait Order_Meta {

	/**
	 * Get woocommerce order by pagarme transaction ID.
	 *
	 * @param  string  $transaction_id
	 * @return void
	 */
	public static function get_order_by_transaction_id( $transaction_id ) {
		if ( ! is_null( $transaction_id ) ) {
			// Search for orders with the meta_key '_pagarme_transaction_id' and the corresponding value
			$orders = wc_get_orders(
				array(
					'meta_key'   => '_pagarme_transaction_id',
					'meta_value' => $transaction_id,
					'limit'      => 1, // Ensure that only one request is returned
				)
			);

			// Returns the order if found
			if ( ! empty( $orders ) && is_a( $orders[0], 'WC_Order' ) ) {
				return $orders[0];
			}
		}

		return false;
	}
	
	/**
	 * Get woocommerce order by pagarme gateway ID.
	 *
	 * @param  string  $transaction_id
	 * @return void
	 */
	public static function get_order_by_gateway_id( $gateway_id ) {
		if ( ! is_null( $gateway_id ) ) {
			// Search for orders with the meta_key '_pagarme_charge_gateway_id' and the corresponding value
			$orders = wc_get_orders(
				array(
					'meta_key'   => '_pagarme_charge_gateway_id',
					'meta_value' => $gateway_id,
					'limit'      => 1, // Ensure that only one request is returned
				)
			);

			// Returns the order if found
			if ( ! empty( $orders ) && is_a( $orders[0], 'WC_Order' ) ) {
				return $orders[0];
			}
		}

		return false;
	}

}
