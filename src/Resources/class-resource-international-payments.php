<?php

namespace Aquapress\Pagarme\Resources;

/**
 * Resources for international payments.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Resources\International_Payments class.
 *
 * @extends Aquapress\Pagarme\Abstracts\Resource.
 */
class International_Payments extends \Aquapress\Pagarme\Abstracts\Resource {
	/**
	 * Running the connector actions.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'woocommerce_after_checkout_form', array( $this, 'enqueue_scripts' ), 100 );
		add_filter( 'woocommerce_billing_fields', array( $this, 'add_checkout_fields' ), 100 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_order_meta_fields' ) );
		add_action( 'woocommerce_admin_print_order_meta_fields', array( $this, 'print_order_meta_fields' ) );
		add_filter( 'wcbcf_disable_checkout_validation', array( $this, 'disable_wcbcf_validation' ), 100 );
	}

	/**
	 * Checkout scripts.
	 */
	public function enqueue_scripts() {
		// Vendor dependencies.
		wp_enqueue_script( 'jquery-intlTelInput', WC_PAGARME_URI . 'assets/vendor/intlTelInput/js/intlTelInput.min.js', array( 'jquery' ), '17.0.0', true );
		wp_enqueue_style( 'jquery-intlTelInput', WC_PAGARME_URI . 'assets/vendor/intlTelInput/css/intlTelInput.min.css', array(), '17.0.0', 'all' );
		// Checkout dependencies.
		wp_enqueue_script( 'wc-pagarme-billing-form', WC_PAGARME_URI . 'assets/js/checkout/billing-form.js', array( 'jquery-intlTelInput' ), WC_PAGARME_VERSION, true );
		// Disable dependencies.
		wp_dequeue_script( 'woocommerce-extra-checkout-fields-for-brazil-front' );
		wp_dequeue_style( 'woocommerce-extra-checkout-fields-for-brazil-front' );
	}
	
	/**
	 * Add checkout fields for international purchases.
	 *
	 * @param    array    $fields    All checkout fields
	 * @return   array    $fields    Updated fields
	 */
	public function add_checkout_fields( $fields ) {
		$fields['billing_nationality']['type'] = 'select';
		$fields['billing_nationality']['default'] = 'BR';
		$fields['billing_nationality']['label'] = __( 'Nacionalidade', 'wc-pagarme' );
		$fields['billing_nationality']['class'] = array( 'form-row-wides' );
		$fields['billing_nationality']['required'] = apply_filters( 'checkout_field_billing_nationality_is_required', false );
		$fields['billing_nationality']['priority'] = ( $fields['billing_persontype']['priority'] ?? 0 ) + 1;
		$fields['billing_nationality']['options'] = WC()->countries->get_countries();

		$fields['billing_taxvat']['label'] = __( 'TaxID' );
		$fields['billing_taxvat']['required'] = apply_filters( 'checkout_field_billing_taxvat_is_required', false );
		$fields['billing_taxvat']['class'] = array( 'form-row-wide' );
		$fields['billing_taxvat']['priority'] = $fields['billing_company']['priority'] ?? ( $fields['billing_cpf']['priority'] ?? 0 ) + 1;
		
		$fields['billing_phone_country']['label'] = __( 'Codigo de telefone do país', 'wc-pagarme' );
		$fields['billing_phone_country']['required'] = apply_filters( 'checkout_field_billing_phone_country_is_required', false );
		$fields['billing_phone_country']['class'] = array( 'form-row-wide hidden' );
		$fields['billing_phone_country']['priority'] = 100;
		
		$fields['billing_first_name']['label'] = __( 'Nome completo' );
		$fields['billing_first_name']['class'] = array( 'form-row-wide', 'form-row-first' );
		
		return $fields;
	}
	
	/**
	 * Save checkout fields in order meta.
	 *
	 * @param  WC_Order $order WooCommerce order object.
	 */
	public function save_order_meta_fields( $post_id ) {
		$order = wc_get_order( $post_id );
		// Old format. Will be removed in beve.
		$order->update_meta_data( '_billing_taxvat', sanitize_text_field( wp_unslash( $_POST['billing_taxvat'] ?? $order->billing_taxvat ) ) );
		$order->update_meta_data( '_billing_nationality', sanitize_text_field( wp_unslash( $_POST['billing_nationality'] ?? $order->billing_nationality ) ) );
		$order->update_meta_data( '_billing_phone_country', sanitize_text_field( wp_unslash( $_POST['billing_phone_country'] ?? $order->billing_phone_country ) ) );
		// New format.
		$order->update_meta_data( 'billing_taxvat', sanitize_text_field( wp_unslash( $_POST['billing_taxvat'] ?? $order->billing_taxvat ) ) );
		$order->update_meta_data( 'billing_nationality', sanitize_text_field( wp_unslash( $_POST['billing_nationality'] ?? $order->billing_nationality ) ) );
		$order->update_meta_data( 'billing_phone_country', sanitize_text_field( wp_unslash( $_POST['billing_phone_country'] ?? $order->billing_phone_country ) ) );
		
		$order->save();
	}
	
	/**
	 * Print extra checkout fields in admin order details.
	 *
	 * @param  WC_Order $order WooCommerce order object.
	 */
	public function print_order_meta_fields( $order ) {
		?>
		<script>
			(function( $ ) {
				'use strict';
				$( '.wcbcf-address p' ).append(' <?php if ( $order->get_meta( '_billing_nationality', false ) ) : ?><strong><?php esc_html_e( 'Nationality', 'wc-pagarme' ); ?>: </strong><?php echo esc_html( $order->get_meta( '_billing_nationality' ) ); ?><br /> <?php if ( isset( $settings['_billing_taxvat'] ) ) : ?> <strong><?php esc_html_e( 'Taxpayer', 'wc-pagarme' ); ?>: </strong><?php echo esc_html( $order->get_meta( '_billing_taxvat' ) ); ?><br /> <?php endif; ?><?php endif; ?> ');
			}( jQuery ));
		</script>
		<?php
	}
	
	/**
	 * Disable CPF AND CNPJ checkout validation.
	 * 
	 * @see Brazilian Market on WooCommerce plugin.
	 *
	 * @param bool $is_disabled Default value.
	 */
	public function disable_wcbcf_validation( $is_disabled ) {			
		return true;
	}
}
