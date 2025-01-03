<?php

namespace Aquapress\Pagarme\Traits;

/**
 * Trait generals payments method.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Traits\Base_Method_General trait.
 *
 * @since 1.0.0
 */
trait Base_Gateway_General {

	/**
	 * Save order meta fields.
	 * Save fields as meta data to display on order's admin screen.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $transaction Order transaction.
	 */
	public function save_order_meta_fields( $order_id, $transaction ) {
		// Transaction data.
		$transaction_data = array_map(
			'sanitize_text_field',
			array(
				'transaction_id' => $transaction['id'] ?? '',
				'customer_id'    => $transaction['customer']['id'] ?? '',
				'charge_id'      => $transaction['charges'][0]['id'] ?? '',
				'payment_method' => $transaction['charges'][0]['payment_method'] ?? '',
				'gateway_id'     => 
					$transaction['charges'][0]['last_transaction']['gateway_id'] ?? '',
				'operation_type' =>
					$transaction['charges'][0]['last_transaction']['operation_type'] ?? '',
			)
		);

		// Transaction data.
		update_post_meta( $order_id, '_wc_pagarme_transaction_id', $transaction['id'] );
		update_post_meta( $order_id, '_wc_pagarme_transaction_data', $transaction_data );
	}

	/**
	 * Process the order status.
	 *
	 * @param mixed    $the_order  Woocommerce Order ID or Object WC_Order.
	 * @param string   $status Transaction status.
	 */
	public function process_order_status( $the_order, $status ) {
		// Get order data.
		$order = wc_get_order( $the_order );
		// Process Woocommerce order status.
		switch ( $status ) {
			case 'paid':
				// Changing the order for processing and reduces the stock.
				$order->payment_complete();
				break;
			case 'pending':
				$order->update_status(
					'on-hold',
					__(
						'Pagar.me: Talvez você deva revisar manualmente este pedido para continuar o fluxo de pagamento, acesse seu painel para isso!',
						'wc-pagarme'
					)
				);

				break;

			case 'failed':
				$order->update_status(
					'failed',
					__( 'Pagar.me: A transação foi rejeitada.', 'wc-pagarme' )
				);

				break;

			case 'canceled':
				$order->update_status(
					'refunded',
					__(
						'Pagar.me: A transação foi reembolsada/cancelada.',
						'wc-pagarme'
					)
				);

				break;

			default:
				break;
		}
	}
}
