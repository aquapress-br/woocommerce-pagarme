<?php

namespace Aquapress\Pagarme\Webhooks;

/**
 * Child class to update webhook data.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Webhooks\Update_Orders class.
 *
 * @extends Aquapress\Pagarme\Abstracts\Webhook.
 */
class Update_Orders extends \Aquapress\Pagarme\Abstracts\Webhook {

	use \Aquapress\Pagarme\Traits\Order_Meta;

	/**
	 * Webhook identifier.
	 *
	 * @var string
	 */
	public $id = 'update_orders';

	/**
	 * Webhook restricted events.
	 *
	 * @var string
	 */
	public $events = array( 'order.paid', 'order.payment_failed', 'order.canceled' );
	
	/**
	 * Process webhook events and update order status accordingly.
	 *
	 * This method handles the processing of webhook events received from Pagar.me 
	 * and updates the WooCommerce order status based on the event type.
	 *
	 * - If the event is `order.paid`, the order status will be set to `processing` 
	 *   or `completed` if it is not already in one of these states.
	 * - If the event is `order.payment_failed`, the order status will be set to `failed`.
	 * - If the event is `order.canceled`, the order status will be set to `cancelled`.
	 *
	 * @param string $event The type of webhook event received (e.g., `order.paid`, `order.payment_failed`, etc.).
	 * @param array  $data  The webhook payload data, which includes transaction details such as the `id`.
	 *
	 * @return void|false Returns false if the required data (e.g., transaction ID) is missing 
	 *                    or if the order cannot be found.
	 */
	public function process( $event = '', $data = array() ) {
		//Check if pagarme transaction id exists.
		if ( ! isset( $data['id'] ) ) {
			return false;
		}		
		//Get the order if exists.
		$order = static::get_order_by_transaction_id( $data['id'] );
		if ( ! $order ) {
			return false;
		}
		// Updates the WooCommerce order status based on the event type.
		if ( 'order.paid' === $event ) {
			if ( !in_array( $order->get_status(), array( 'processing', 'completed' ) ) ) {
				$order->payment_complete();
			}
		} else if ( 'order.payment_failed' === $event ) {
			$order->update_status(
				'failed',
				__( 'Pagar.me: A transação foi rejeitada.', 'wc-pagarme' )
			);
		} else if ( 'order.canceled' === $event ) {
			if ( !in_array( $order->get_status(), array( 'refunded' ) ) ) {
				$order->update_status(
					'cancelled',
					__( 'Pagar.me: A transação foi cancelada.', 'wc-pagarme' )
				);
			}
		}
	}

}
