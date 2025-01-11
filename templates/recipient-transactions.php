<?php
/**
 * Pagar.me Recipient Moviments Template
 */
?>

<div class="pagarme-transactions-cards">
	<div class="pagarme-transactions-card green">
			<strong>
				<?php _e( 'Saldo atual:', 'wc-pagarme' ); ?>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php echo ( $balance['available_amount'] > 0 ) ? '<span></span>' : '<span>-&nbsp;</span>'; ?>
					</span>
					<?php echo wc_price( str_replace( '-', '', $balance['available_amount'] ) / 100 ); ?>
				</strong>
			</strong>
	</div>

	<div class="pagarme-transactions-card orange">
			<strong>
				<?php _e( 'À receber:', 'wc-pagarme' ); ?>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php echo ( $balance['waiting_funds_amount'] > 0 ) ? '<span></span>' : '<span>-&nbsp;</span>'; ?>
					</span>
					<?php echo wc_price( str_replace( '-', '', $balance['waiting_funds_amount'] ) / 100 ); ?>
				</strong>
			</strong>
	</div>

	<div class="pagarme-transactions-card red">
			<strong>
				<?php _e( 'Transferidos:', 'wc-pagarme' ); ?>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php echo ( $balance['transferred_amount'] > 0 ) ? '<span></span>' : '<span>-&nbsp;</span>'; ?>
					</span>
					<?php echo wc_price( str_replace( '-', '', $balance['transferred_amount'] ) / 100 ); ?>
				</strong>
			</strong>
	</div>
</div>
<?php if ( ! empty( $operations['data'] ) ) : ?>
	<div class="pagarme-transactions-table">
		<br>
		<form id="operations-filter" method="POST">
			<table>
				<thead>
					<tr>
						<th>
						</th>
						<th><?php _e( 'Descrição', 'wc-pagarme' ); ?></th>
						<th><?php _e( 'Valor', 'wc-pagarme' ); ?></th>
						<th><?php _e( 'Status', 'wc-pagarme' ); ?></th>
						<th><?php _e( 'Data', 'wc-pagarme' ); ?></th>
						<th><?php _e( 'Pedido', 'wc-pagarme' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $operations['data'] as $operation ) : ?>
						<tr>
							<td></td>
							<td class="pagarme-operations-table-column-description" >
								<?php
								switch ( $operation['type'] ) {
									case 'transfer':
										_e( 'Remessa de Crédito para Conta do Vendedor', 'wc-pagarme' );
										break;
									case 'anticipation':
										_e( 'Crédito de Antecipação para Saldo do Vendedor', 'wc-pagarme' );
										break;
									case 'payable':
										if ( $operation['movement_object']['type'] == 'credit' ) {
											_e( 'Crédito de Venda para Saldo do Vendedor', 'wc-pagarme' );
										} elseif ( $operation['movement_object']['type'] == 'refund' ) {
											_e( 'Débito de Reembolso para Saldo do Vendedor', 'wc-pagarme' );
										}
										break;
								}
								?>
							</td>
							<td class="pagarme-operations-table-column-value" >
								<strong class="woocommerce-Price-amount amount" style="<?php echo ( $operation['amount'] > 0 ) ? 'color:#5cb85c' : 'color:#c9302c'; ?>">
									<span class="woocommerce-Price-currencySymbol">
										<?php echo ( $operation['amount'] > 0 ) ? '<span>&nbsp;&nbsp;&nbsp;</span>' : '<span>-&nbsp;</span>'; ?>
									</span>
									<?php echo wc_price( str_replace( '-', '', $operation['amount'] ) / 100 ); ?>
								</strong>
							</td>
							<td class="pagarme-operations-table-column-status" >
									<?php
									switch ( $operation['status'] ) {
										case 'waiting_funds':
											echo '<span class="pagarme-transaction-label orange">PENDENTE</span>';
											break;
										case 'available':
										case 'transferred':
											echo '<span class="pagarme-transaction-label green">REALIZADO</span>';
											break;
									}
									?>
							</td>
							<td class="pagarme-operations-table-column-date" >
								<abbr title="">
									<?php echo date( 'd/M/Y', strtotime( $operation['created_at'] ) ); ?>
								</abbr>
							</td>
							<td class="pagarme-operations-table-column-order" style="text-align:center">
								<?php do_action( 'wc_pagarme_recipient_transactions_table_column_order', $operation ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	</div>
	<div class="pagarme-transactions-pagination">
		<br>
		<ul>		
			<?php
			$operation_count = (int) ( $_GET['operations-page'] ?? 1 );
			
			if ( $operation_count > 1 ) {
				echo '<li><a class="prev" href="?operations-page=' . ( $operation_count - 1 )  . '&start_date=' . ( $_GET['start_date'] ?? '' ) . '&end_date=' . ( $_GET['end_date'] ?? '' ) . '">« Anterior</a></li>';
			}
			if ( $operation_count < 100 ) {
				echo '<li><a class="next" href="?operations-page=' . ( $operation_count + 1 )  . '&start_date=' . ( $_GET['start_date'] ?? '' ) . '&end_date=' . ( $_GET['end_date'] ?? '' ) . '">Próximo »</a></li>';
			}
			?>
		</ul>
	</div>
<?php else : ?>
	<div>
		<strong><?php _e( 'Não existem mais dados para mostrar.', 'wc-pagarme' ); ?></strong>
	</div>
	<div class="pagarme-transactions-pagination">
		<br>
		<ul>		
			<?php
			$operation_count = (int) ( $_GET['operations-page'] ?? 0 );
			
			if ( $operation_count > 1 ) {
				echo '<li><a class="prev" href="?operations-page=' . ( $operation_count - 1 )  . '&start_date=' . ( $_GET['start_date'] ?? '' ) . '&end_date=' . ( $_GET['end_date'] ?? '' ) . '">« Anterior</a></li>';
			}
			?>
		</ul>
	</div>
<?php endif; ?>
