<?php

namespace Aquapress\Pagarme\Resources;

/**
 * Resources for sale without address.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Resources\Sale_Without_Address class.
 *
 * @extends Aquapress\Pagarme\Abstracts\Resource.
 */
class Sale_Without_Address extends \Aquapress\Pagarme\Abstracts\Resource {

	/**
	 * Running the connector actions.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_filter( 'wc_pagarme_transaction_data', array( $this, 'filter_transaction_data' ), 150, 3 );
		add_filter( 'wc_pagarme_gateway_form_fields', array( $this, 'filter_form_fields' ), 10, 2 );
	}

	/**
	 * Change transaction details to use store address details.
	 *
	 * @param array                                $payload  Regular payment data.
	 * @param mixed                                $order    WooCommerce Order ID or WC_Order object.
	 * @param Aquapress\Pagarme\Abstracts\Gateway  $context  The Pagar.me gateway object.
	 *
	 * @return array
	 */
	public function filter_transaction_data( $payload, $order, $context ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order instanceof WC_Order ) {
			return $payload;
		}

		// Only creditcard payments are supported.
		if ( 'wc_pagarme_creditcard' !== $context->id && 'yes' !== $context->get_option( 'without_address' ) ) {
			return $payload;
		}

		// Check if all products in the order are virtual.
		$all_virtual = true;
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product || ! $product->is_virtual() ) {
				$all_virtual = false;
				break;
			}
		}

		if ( $all_virtual ) {
			// Get store info from WooCommerce settings.
			$store_country_state = get_option( 'woocommerce_default_country' );
			list( $store_country, $store_state ) = explode( ':', $store_country_state );

			$payload['payments'][0]['credit_card']['card']['billing_address'] = array(
				'city'          => get_option( 'woocommerce_store_city' ),
				'neighborhood'  => '', // Optional: not available by default
				'street'        => get_option( 'woocommerce_store_address' ),
				'street_number' => '', // Optional: not available by default
				'zip_code'      => get_option( 'woocommerce_store_postcode' ),
				'line_1'        => get_option( 'woocommerce_store_address' ),
				'line_2'        => get_option( 'woocommerce_store_address_2' ),
				'state'         => $store_state,
				'country'       => $store_country,
			);
		}

		return $payload;
	}

	/**
	 * Set gateway option.
	 *
	 * @param array                                $fields  Gateway settings fields.
	 * @param Aquapress\Pagarme\Abstracts\Gateway  $context  The Pagar.me gateway object.
	 *
	 * @return array
	 */
	public function filter_form_fields( $fields, $context ) {
		// Only creditcard payments are supported.
		if ( 'wc_pagarme_creditcard' !== $context->id ) {
			return $fields;
		}

		// Inserir antes da chave "debug"
		if ( isset( $fields['debug'] ) ) {
			$position = array_search( 'debug', array_keys( $fields ), true );

			if ( $position !== false ) {
				$before = array_slice( $fields, 0, $position, true );
				$after  = array_slice( $fields, $position, null, true );

				// Novo campo a ser inserido
				$insert = array(
					'without_address' => array(
						'title'       => __( 'Endereço Padrão', 'wc-pagarme' ),
						'type'        => 'checkbox',
						'label'       => __( 'Venda sem os campos de endereço', 'wc-pagarme' ),
						'default'     => 'no',
						'description' => __( 'Use o endereço físico da sua loja como endereço de cobrança padrão. Marcar esta opção permite remover os campos de endereço do checkout. O antifraude deve estar desativado.', 'wc-pagarme' ),
					),
				);

				$fields = array_merge( $before, $insert, $after );
			}
		}

		return $fields;
	}
}
