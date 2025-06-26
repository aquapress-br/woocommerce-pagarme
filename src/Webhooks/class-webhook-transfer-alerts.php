<?php

namespace Aquapress\Pagarme\Webhooks;

/**
 * Child class to transfer alerts webhook data.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Webhooks\Transfer_Alerts class.
 *
 * @extends Aquapress\Pagarme\Abstracts\Webhook.
 */
class Transfer_Alerts extends \Aquapress\Pagarme\Abstracts\Webhook {

	use \Aquapress\Pagarme\Traits\Order_Meta;

	/**
	 * Webhook identifier.
	 *
	 * @var string
	 */
	public $id = 'transfer_alerts';

	/**
	 * Webhook restricted events.
	 *
	 * @var string
	 */
	public $events = array( 'transfer.processing', 'transfer.paid', 'transfer.failed' );

	/**
	 * Execute the webhook actions.
	 *
	 * Run child class additional actions hooks.
	 *
	 * @param string $event Webhook event.
	 * @param array $data Webhook data.
	 *
	 * @return void
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
		if ( 'transfer.processing' === $event ) {
			
		} elseif ( 'transfer.paid' === $event ) {
			
		} elseif ( 'transfer.failed' === $event ) {
			
		}
	}
}
