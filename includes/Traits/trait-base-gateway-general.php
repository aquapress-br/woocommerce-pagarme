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
		// Get order.
		$order = wc_get_order( $order_id );
		// Set transaction meta data.
		$order->add_meta_data( 'PAGARME_TRANSACTION_ID', $transaction['id'] ?? '', true );
		$order->add_meta_data( 'PAGARME_CUSTOMER_ID', $transaction['customer']['id'] ?? '', true );
		$order->add_meta_data( 'PAGARME_CHARGE_ID', $transaction['charges'][0]['last_transaction']['id'] ?? '', true );
		$order->add_meta_data( 'PAGARME_PAYMENT_METHOD', $transaction['charges'][0]['last_transaction']['transaction_type'] ?? '', true );
		$order->add_meta_data( 'PAGARME_GATEWAY_ID', $transaction['charges'][0]['last_transaction']['gateway_id'] ?? '', true );
		$order->add_meta_data( 'PAGARME_CARD_ID', $transaction['charges'][0]['last_transaction']['card']['id'] ?? '', true );
		$order->add_meta_data( 'PAGARME_CARD_INSTALLMENTS', $transaction['charges'][0]['last_transaction']['installments'] ?? '', true );
		$order->add_meta_data( 'PAGARME_CARD_OPERATION_TYPE', $transaction['charges'][0]['last_transaction']['operation_type'] ?? '', true );
		$order->add_meta_data( 'PAGARME_BOLETO_URL', $transaction['charges'][0]['last_transaction']['pdf'] ?? '', true );
		$order->add_meta_data( 'PAGARME_BOLETO_DUE_AT', $transaction['charges'][0]['last_transaction']['due_at'] ?? '', true );
		$order->add_meta_data( 'PAGARME_PIX_QRCODE', $transaction['charges'][0]['last_transaction']['qr_code'] ?? '', true );
		$order->add_meta_data( 'PAGARME_PIX_QRCODE_URL', $transaction['charges'][0]['last_transaction']['qr_code_url'] ?? '', true );
		$order->add_meta_data( 'PAGARME_PIX_EXPIRES_AT', $transaction['charges'][0]['last_transaction']['expires_at'] ?? '', true );
		// Save transaction meta data.
		$order->save();
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
