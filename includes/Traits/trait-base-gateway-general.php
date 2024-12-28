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
	 * @param int   $id Order ID.
	 * @param array $transaction Order transaction.
	 */
	public function save_order_meta_fields( $id, $transaction ) {
		// Transaction data.
		$order_data = array_map(
			'sanitize_text_field',
			array(
				'order_id'       => $transaction['id'],
				'customer_id'    => $transaction['customer']['id'],
				'charge_id'      => $transaction['charges'][0]['id'],
				'payment_method' => $transaction['charges'][0]['payment_method'],
				'operation_type' =>
					$transaction['charges'][0]['last_transaction']['operation_type'],
			)
		);

		// Transaction data.
		update_post_meta( $id, '_wc_pagarme_order_id', $transaction['id'] );
		update_post_meta( $id, '_wc_pagarme_order_data', $order_data );
	}

	/**
	 * Process the order status.
	 *
	 * @param WC_Order $order  Order data.
	 * @param string   $status Transaction status.
	 */
	public function process_order_status( $order, $status ) {
		wc_pagarme()->logger->add(
			'Payment status for order ' .
				$order->get_order_number() .
				' is now: ' .
				$status
		);

		switch ( $status ) {
			case 'paid':
				if (
					class_exists( 'WC_Subscriptions_Manager' ) &&
					function_exists( 'wcs_order_contains_subscription' ) &&
					function_exists( 'wcs_order_contains_renewal' )
				) {
					$order_type =
						true === wcs_order_contains_renewal( $order )
							? 'renewal'
							: 'parent';
					if ( wcs_order_contains_subscription( $order, $order_type ) ) {
						WC_Subscriptions_Manager::process_subscription_payments_on_order(
							$order
						);
					}
				}
				// Changing the order for processing and reduces the stock.
				$order->payment_complete();

				break;

			case 'pending':
				$order->update_status(
					'on-hold',
					__(
						'Pagar.me: Maybe you should manually review this order to continue the payment flow, access your panel for that!',
						'wc-pagarme'
					)
				);

				break;

			case 'failed':
				$order->update_status(
					'failed',
					__( 'Pagar.me: The transaction was rejected.', 'wc-pagarme' )
				);

				if (
					class_exists( 'WC_Subscriptions_Manager' ) &&
					function_exists( 'wcs_order_contains_subscription' )
				) {
					if ( wcs_order_contains_subscription( $order ) ) {
						WC_Subscriptions_Manager::process_subscription_payment_failure_on_order(
							$order
						);
					}
				}

				Wc_Pagarme_Helper::send_email(
					sprintf(
						esc_html__(
							'The transaction for order %s was rejected by the card company or by fraud',
							'wc-pagarme'
						),
						$order->get_order_number()
					),
					esc_html__( 'Transaction failed', 'wc-pagarme' ),
					sprintf(
						esc_html__(
							'Order %1$s has been marked as failed, because the transaction was rejected by the card company or by fraud, for more details, access you dashboard to see.',
							'wc-pagarme'
						),
						$order->get_order_number()
					)
				);

				break;

			case 'canceled':
				$order->update_status(
					'refunded',
					__(
						'Pagar.me: The transaction was refunded/canceled.',
						'wc-pagarme'
					)
				);

				if (
					class_exists( 'WC_Subscriptions_Manager' ) &&
					function_exists( 'wcs_order_contains_subscription' )
				) {
					if ( wcs_order_contains_subscription( $order ) ) {
						WC_Subscriptions_Manager::cancel_subscriptions_for_order(
							$order
						);
					}
				}

				Wc_Pagarme_Helper::send_email(
					sprintf(
						esc_html__(
							'The transaction for order %s refunded',
							'wc-pagarme'
						),
						$order->get_order_number()
					),
					esc_html__( 'Transaction refunded', 'wc-pagarme' ),
					sprintf(
						esc_html__(
							'Order %1$s has been marked as refunded by Pagar.me, for more details, access you dashboard to see.',
							'wc-pagarme'
						),
						$order->get_order_number()
					)
				);

				break;
			default:
				break;
		}
	}
}
