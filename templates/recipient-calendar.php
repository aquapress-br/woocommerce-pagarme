<?php
/**
 * Pagar.me Recipient Calendar Template
 */
?>

<div class="pagarme-calendar-content">
	<div class="pagarme-calendar-header">
		<h5> 
			<?php _e( 'Você tem um total de', 'wc-pagarme' ) ; ?>
			<strong>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php  echo ( $balance['waiting_funds_amount'] >= 0  ) ? "<span></span>" : "<span>-&nbsp;</span>"; ?>
					</span>
					<?php echo wc_price( str_replace( "-", "",  $balance['waiting_funds_amount'] ) / 100 );  ?>
				</strong>
			</strong>
			<?php _e( 'para receber', 'wc-pagarme' ) ; ?>
		</h5>
		<br>
	</div>
	
	<div id="payables-calendar" class="pagarme-calendar-payables">
	</div>

	<div id="payables-summary" class="pagarme-calendar-summary">
		<strong class="payables-summary-title"><?php _e( 'RELAÇÃO DE RECEBIMENTOS NO DIA', 'wc-pagarme' ); ?></strong>
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

<style>

.pagarme-calendar-content {
  display: grid; 
  grid-template-columns: 1fr 1fr 1fr; 
  grid-template-rows: auto auto auto; 
  gap: 0px 0px; 
  grid-template-areas: 
    "pagarme-calendar-header pagarme-calendar-header pagarme-calendar-header"
    "pagarme-calendar-payables pagarme-calendar-payables pagarme-calendar-summary"
    "pagarme-calendar-footer pagarme-calendar-footer pagarme-calendar-footer"; 
}

.pagarme-calendar-header { 
	grid-area: pagarme-calendar-header; 
}

.pagarme-calendar-payables {
	grid-area: pagarme-calendar-payables;
}

.pagarme-calendar-summary { 
	grid-area: pagarme-calendar-summary;
	overflow: hidden;
	overflow-y: scroll;
	height: auto;
	max-height: 540px;
}

.pagarme-calendar-footer { 
	grid-area: pagarme-calendar-footer;
}

</style>
