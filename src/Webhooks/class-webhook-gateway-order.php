<?php

namespace Aquapress\Pagarme\Tasks;

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
class Update_Orders extends Aquapress\Pagarme\Abstracts\Webhook {

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
	public $events = array( 'order.paid', 'order.payment_failed', 'order.created', 'order.canceled' );
	
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
		
	}
}
