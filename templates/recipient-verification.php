<?php
/**
 * Pagar.me Recipient verification Page Template
 */
?>

<article>
	<div style="text-align: center;padding: 25px;max-width: 900px;margin: auto;">
		<h2><?php _e( 'Por favor, verifique a sua identidade.', 'wc-pagarme' ); ?></h2>
		<p style="font-size: 20px;max-width: 1000px;margin: auto;text-align: center;"><?php _e( 'A verificação de identidade é necessária para permitir o recebimento dos pagamentos de suas vendas diretamente na sua conta bancária.', 'wc-pagarme' ); ?></p>
		<div style="margin-top: 30px;"><a href="<?php echo $recipient_kyc_link; ?>" class="dokan-btn dokan-btn-theme dokan-btn-danger"><?php _e( 'VERIFICAR CONTA', 'wc-pagarme' ); ?></a></div>
		<img style="width: 45%;max-width: 500px;" src="<?php echo WC_PAGARME_URI . 'assets/img/user-verification-bg.jpg'; ?>" />
	</div>
</article>
