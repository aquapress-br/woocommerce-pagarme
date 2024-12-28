<?php
/**
 * Dokan Settings Payment Template
 *
 * @package dokan
 */

?>

<?php if ( isset( $status_message ) && ! empty( $status_message ) ) : ?>
	<div class="dokan-alert <?php echo ( 'success' === $connect_status ) ? 'dokan-alert-success' : 'dokan-alert-danger'; ?>">
		<?php echo wp_kses_post( $status_message ); ?>
	</div>
<?php endif; ?>

<a href="<?php echo esc_url_raw( dokan_get_navigation_url( 'settings/payment' ) ); ?>">
	&larr; <?php esc_html_e( 'Back', 'dokan-lite' ); ?>
</a>

<form method="post" id="payment-form" action="" class="dokan-form-horizontal">

	<?php wp_nonce_field( 'dokan_payment_settings_nonce' ); ?>

	<fieldset class="payment-field-<?php echo esc_attr( $method_key ); ?>">
		<div class="dokan-form-group">
			<div class="dokan-w12">
					<?php call_user_func( $method['callback'], $profile_info ); ?>
				</div>
		</div>
	</fieldset>
</form>
