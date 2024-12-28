<?php
/**
 * Pagar.me Recipient Moviments Template
 */
?>

<div class="pagarme-recipient-transactions-cards">
	<div class="pagarme-card pagarme-card-green">
			<strong>
				<?php _e( 'Saldo atual:', 'wc-pagarme' ); ?>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php  echo ( $balance['available_amount'] > 0  ) ? "<span></span>" : "<span>-&nbsp;</span>"; ?>
					</span>
					<?php  echo wc_price( str_replace( "-", "", $balance['available_amount'] ) / 100 ); ?>
				</strong>
			</strong>
	</div>

	<div class="pagarme-card pagarme-card-yellow">
			<strong>
				<?php _e( 'À receber:', 'wc-pagarme' ); ?>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php  echo ( $balance['waiting_funds_amount'] > 0  ) ? "<span></span>" : "<span>-&nbsp;</span>"; ?>
					</span>
					<?php  echo wc_price( str_replace( "-", "", $balance['waiting_funds_amount'] ) / 100 ); ?>
				</strong>
			</strong>
	</div>

	<div class="pagarme-card pagarme-card-red">
			<strong>
				<?php _e( 'Transferidos:', 'wc-pagarme' ); ?>
				<strong class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">
						<?php  echo ( $balance['transferred_amount'] > 0  ) ? "<span></span>" : "<span>-&nbsp;</span>"; ?>
					</span>
					<?php  echo wc_price( str_replace( "-", "", $balance['transferred_amount'] ) / 100 ); ?>
				</strong>
			</strong>
	</div>
</div>

<?php if ( ! empty( $operations['data'] ) ) : ?>
    <form id="operations-filter" method="POST">
        <table>
		   <thead>
                <tr>
                    <th id="cb" class="manage-column column-cb check-column">
                    </th>
                    <th><?php _e( 'Descrição', 'wc-pagarme' ); ?></th>
                    <th><?php _e( 'Valor', 'wc-pagarme' ); ?></th>
                    <th><?php _e( 'Status', 'wc-pagarme' ); ?></th>
                    <th><?php _e( 'Data', 'wc-pagarme' ); ?></th>
                    <th><?php _e( 'Ordem', 'wc-pagarme' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $operations['data'] as $operation ) : ?>
                    <tr>
						<td></td>
                        <td class="dokan-mov-description" >
                            <?php 
								switch ( $operation["type"] ) {
									case "transfer":
										_e( 'Remessa de Crédito para Conta do Vendedor', 'wc-pagarme' );
										break;
									case "anticipation":
										_e( 'Crédito de Antecipação para Saldo do Vendedor', 'wc-pagarme' );
										break;
									case "payable":
										if ( $operation["movement_object"]["type"] == "credit" ) {
											_e( 'Crédito de Venda para Saldo do Vendedor', 'wc-pagarme' );
										}
										else if ( $operation["movement_object"]["type"] == "refund" ) {
											_e( 'Débito de Reembolso para Saldo do Vendedor', 'wc-pagarme' );
										}
										break;
								}
							
							?>
                        </td>
                        <td class="dokan-mov-value" >
                            <strong class="woocommerce-Price-amount amount" style="<?php echo ( $operation["amount"] > 0 ) ? 'color:#5cb85c' : 'color:#c9302c' ?>">
								<span class="woocommerce-Price-currencySymbol">
									<?php  echo ( $operation["amount"] > 0  ) ? "<span>&nbsp;&nbsp;&nbsp;</span>" : "<span>-&nbsp;</span>"; ?>
								</span>
								<?php  echo wc_price( str_replace( "-", "", $operation["amount"] ) / 100 ); ?>
							</strong>
                        </td>
						<td class="dokan-mov-status" >
								<?php 
									switch ( $operation["status"] ) {
										case "waiting_funds":
											echo '<span class="dokan-label dokan-label-warning">PENDENTE</span>';
											break;
										case "available":
										case "transferred":
											echo '<span class="dokan-label dokan-label-success">REALIZADO</span>';
											break;
									}
								?>
                        </td>
						<td class="dokan-mov-date" >
							<abbr title="">
								<?php echo date("d/M/Y", strtotime( $operation["created_at"] ) ); ?>
							</abbr>
                        </td>
                        <td class="dokan-mov-order" >
							<?php 
								$parent_order = Ddfp_Helper::get_order_by_transaction_id( $operation['movement_object']['id'] );
								$seller_order = Ddfp_Helper::get_seller_order( $access['saller'], $parent_order );
							?>
                            <?php if ( current_user_can( 'dokan_view_order' ) ): ?>
                                <?php if ( !is_null( $seller_order ) ) : ?>
									<?php echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'order_id' => $seller_order->ID ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) ) . '"><strong>' . sprintf( __( 'Ordem %s', 'dokan-lite' ), esc_attr( $seller_order->ID ) ) . '</strong></a>'; ?>
								<?php endif; ?>
							<?php else: ?>
                                <?php echo '<strong>' . sprintf( 'Ordem %s', esc_attr( $seller_order->ID ) ) . '</strong>'; ?>
							<?php endif ?>
                        </td>
					</tr>
				<?php endforeach; ?>
			</tbody>
        </table>
    </form>
	
	<div class="pagination-wrap" style="text-align:center">
		<ul class="pagination">		
			<?php
				$operation_count = (int) $_GET['operations-page'] ?: 1;
				if ( $operation_count > 1 ) {
					echo '<li><a class="prev page-numbers" href="?operations-page=' . ( $operation_count - 1 )  . '&start_date=' . $_GET['start_date'] . '&end_date=' . $_GET['end_date'] . '">« Anterior</a></li>';
				}
				if ( $operation_count < 100 ) {
					echo '<li><a class="next page-numbers" href="?operations-page=' . ( $operation_count + 1 )  . '&start_date=' . $_GET['start_date'] . '&end_date=' . $_GET['end_date'] . '">Próximo »</a></li>';
				}
			?>
		</ul>
	</div>
	
<?php  else : ?>

	<div style="margin-bottom: 90px;">
		<strong><?php __( 'Não existem mais dados para mostrar.', 'wc-pagarme' ); ?></strong>
	</div>
	
	<div class="pagination-wrap" style="text-align:center">
		<ul class="pagination">		
			<?php
				if ( !empty( $_GET['operations-page'] ) ) {
					$operation_count = (int) $_GET['operations-page'] ?: 1;
					echo '<li><a class="prev page-numbers" href="?operations-page=' . ( $operation_count - 1 ) . '&start_date=' . $_GET['start_date'] . '&end_date=' . $_GET['end_date'] . '">« Anterior</a></li>';
				}
			?>
		</ul>
	</div>
	
<?php endif; ?>

