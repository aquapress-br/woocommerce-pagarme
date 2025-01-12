<?php
/**
 * Pagar.me Recipient Calendar Template
 */
?>

<div class="pagarme-calendar-content">
	<div class="pagarme-calendar-header">
		<h5> 
			<?php _e( 'Você tem um total de', 'wc-pagarme' ); ?>
			<strong>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php echo ( $balance['waiting_funds_amount'] >= 0 ) ? '<span></span>' : '<span>-&nbsp;</span>'; ?>
					</span>
					<?php echo wc_price( str_replace( '-', '', $balance['waiting_funds_amount'] ) / 100 ); ?>
				</strong>
			</strong>
			<?php _e( 'para receber', 'wc-pagarme' ); ?>
		</h5>
		<br>
	</div>
	
	<div id="payables-calendar" class="pagarme-calendar-payables">
	</div>

	<div id="payables-summary" class="pagarme-calendar-summary">
		<strong class="payables-summary-title"><?php _e( 'RECEBIMENTOS DO DIA', 'wc-pagarme' ); ?></strong>
		<div>
			<ul class="order-list"></ul>
		</div>
	</div>

	<div class="pagarme-calendar-footer">
		<div class="label-total">
			<strong>Recebimentos para o mês</strong>
			<strong id="month-total">R$ 0.00</strong>
		</div>
	</div>
</div>