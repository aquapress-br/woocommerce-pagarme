<?php

/**
 * Card form.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>

<section id="pagarme-card-payment-form" class="pagarme-payment-form">
	<ul class="payment_methods" style="border: none;">
		<?php if ( $saved_cards && $is_checkout ) : ?>		
				<?php
				foreach ( $saved_cards as $index => $card_data ) :
					$brand = \Aquapress\Pagarme\Gateways\CreditCard::get_card_brand_name( $card_data->get_card_type() );
					?>
				<li>
					<input id="<?php echo 'pagarme_card_save_' . $card_data->get_token(); ?>" type="radio" class="input-radio pagarme-card-option" name="<?php echo $card_id; ?>" value="<?php echo $card_data->get_token(); ?>">
					<label class="pagarme-card-brand pagarme-card-brand-<?php echo $card_data->get_card_type(); ?>" for="<?php echo 'pagarme_card_save_' . $card_data->get_token(); ?>"><?php echo sprintf( __( '%1$s ending in %2$s (expires %3$s)', 'wc-pagarme' ), $brand, $card_data->get_last4(), $card_data->get_expiry_month() . '/' . $card_data->get_expiry_year() ); ?> </label>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
		<li id="save pagarme-card-add">
			<?php if ( $saved_cards && $is_checkout ) : ?>
				<input id="pagarme_card_new" type="radio" class="input-radio pagarme-card-option" name="<?php echo $card_id; ?>" value="" checked>
				<label for="pagarme_card_new"><?php _e( 'Use um novo cartão para pagamento', 'wc-pagarme' ); ?></label>
				<hr style="margin: 5px 0;">
			<?php else : ?>
				<input type="hidden" class="pagarme-card-option" name="<?php echo $card_id; ?>" value="">
			<?php endif; ?>
			<p class="form-row form-row-first">
				<label for="pagarme-card-number"><?php _e( 'Número do Cartão', 'wc-pagarme' ); ?> <span class="required">*</span></label>
				<input value="" id="pagarme-card-number" name="<?php echo $card_number; ?>" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="22" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" required pattern="^\s*(?:\d\s*){14,25}$" title="<?php _e( 'Enter a valid card number', 'wc-pagarme' ); ?>" style="font-size: 1.5em; padding: 8px;max-height: 41px;" />
			</p>
			<p class="form-row form-row-last">
				<label for="pagarme-card-holder-name"><?php _e( 'Titular do Cartão', 'wc-pagarme' ); ?> <span class="required">*</span></label>
				<input value="" id="pagarme-card-holder-name" name="<?php echo $card_name; ?>" class="input-text" type="text" autocomplete="off" required pattern="^[a-zA-Z\s]{2,}$" title="<?php _e( 'Please enter a valid name', 'wc-pagarme' ); ?>" style="font-size: 1.5em; padding: 8px;max-height: 41px;" />
			</p>
			<div class="clear"></div>
			<p class="form-row form-row-first">
				<label for="pagarme-card-expiry"><?php _e( 'Data de Validade (MM-YYYY)', 'wc-pagarme' ); ?> <span class="required">*</span></label>
				<input value="" id="pagarme-card-expiry" name="<?php echo $card_expiry; ?>" class="input-text" type="tel" autocomplete="off" placeholder="<?php _e( 'MM - YYYY', 'wc-pagarme' ); ?>" required pattern="(0[1-9]|1[0-2])-(\d{2}|20\d{2}|2[1-9]\d{2})" title="<?php _e( 'Insira uma data válida no formato MM-AAAA', 'wc-pagarme' ); ?>" style="font-size: 1.5em; padding: 8px;max-height: 41px;" />
			</p>
			<p class="form-row form-row-last">
				<label for="pagarme-card-cvc"><?php _e( 'Código de Segurança', 'wc-pagarme' ); ?> <span class="required">*</span></label>
				<input value="" id="pagarme-card-cvc" name="<?php echo $card_cvc; ?>" class="input-text wc-credit-card-form-card-cvc" type="tel" autocomplete="off" placeholder="<?php _e( 'CVC', 'wc-pagarme' ); ?>" required pattern="\d{3,4}" title="<?php _e( 'Insira um código de segurança válido (3 ou 4 dígitos)', 'wc-pagarme' ); ?>" style="font-size: 1.5em; padding: 8px;max-height: 41px;" />
			</p>
			<?php if ( $is_checkout && $tokenize_card == 'ask_before_saving' ) : ?>
				<p class="form-row form-row-wide" class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<label for="save-card">
						<input id="save-card" name="<?php echo $card_save_option; ?>" type="checkbox" value="1" checked class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"/><span><?php _e( 'Salvar cartão para minha próxima compra', 'wc-pagarme' ); ?></span>
					</label>
				</p>
			<?php endif; ?>
		</li>
	</ul>
	<?php if ( $installments && $is_checkout ) : ?>
		<p class="form-row form-row-wide">
			<label for="pagarme-installments"><?php _e( 'Parcelas', 'wc-pagarme' ); ?> <span class="required">*</span></label>
			<?php echo $installments; ?>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</section>
