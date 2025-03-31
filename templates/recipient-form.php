<?php
/**
 * Pagar.me Recipient Form Template
 */
?>

<div id="pagarme-recipient-form">
	<div class="pagarme-feedback"></div>
	<section class="pagarme-form-content">
		<div class="pagarme-form-content-header">
			<h3 class="pagarme-form-title"><?php _e( 'Transferências de Pagamentos', 'wc-pagarme' ); ?></h3>
			<p class="pagarme-form-subtitle"><?php _e( 'Receba os pagamentos de suas vendas diretamente na sua conta bancária. Os dados fornecidos serão utilizados para depositar os valores das vendas. Todas as transferências são processadas pela <a href="https://pagar.me/">Pagar.me</a> ao utilizar, você concorda com os <a href="https://pagar.me/documentos/termos-de-uso.pdf">termos de uso do serviço</a>. Confira abaixo as pendências para a validação do seu cadastro:', 'wc-pagarme' ); ?></p>
		</div>
		<div class="pagarme-form-content-main">
			<div class="pagarme-form-content-main-section">
				<div class="pagarme-form-card-section <?php $recipient_id ? print( 'section-completed section-close' ) : print( 'section-open' ); ?>">
					<div class="pagarme-form-section-intro">
						<div class="pagarme-form-card-section-title">  
							<span class="section-number">1. </span><?php _e( 'Dados Comerciais', 'wc-pagarme' ); ?>
						</div>
						<div class="pagarme-form-card-section-text">
							<span class="intro-close"><?php _e( 'Seus dados comerciais foram cadastrados com sucesso.', 'wc-pagarme' ); ?></span>
							<span class="intro-open"><?php _e( 'Preencha o formulário abaixo com seus dados comerciais ou da sua empresa.', 'wc-pagarme' ); ?></span>
						</div>
					</div>
					<div class="pagarme-form-section-status">
						<?php if ( $recipient_id ) : ?>
						<strong class="status-completed"><?php _e( 'Concluído', 'wc-pagarme' ); ?></strong>
						<?php else : ?>
						<strong class="status-pending"><?php _e( 'Pendente', 'wc-pagarme' ); ?></strong>
						<?php endif; ?>
					</div>
					<div class="pagarme-form-section-content">
						<div class="pagarme-form-group">
							<label for="account_type"><?php _e( 'Tipo da Conta', 'wc-pagarme' ); ?></label>
							<select id="account_type" name="pagarme_recipient_account_type" class="valid">
								<option value="individual" <?php selected( $user_info->pagarme_recipient_account_type, 'individual' ); ?>><?php _e( 'Pessoa Física', 'wc-pagarme' ); ?></option>
								<option value="corporation" <?php selected( $user_info->pagarme_recipient_account_type, 'corporation' ); ?>><?php _e( 'Pessoa Jurídica', 'wc-pagarme' ); ?></option>
							</select>
						</div>
						<div id="corporation_fields" class="hidden">
							<div class="pagarme-form-group pagarme-form-group-row-first">
								<label for="document_cnpj"><?php _e( 'CNPJ', 'wc-pagarme' ); ?></label>
								<input type="text" id="document_cnpj" name="pagarme_recipient_document_cnpj" value="<?php echo $user_info->pagarme_recipient_document_cnpj; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-last">
								<label for="company_legal_name"><?php _e( 'Razão social da empresa', 'wc-pagarme' ); ?></label>
								<input type="text" id="company_legal_name" name="pagarme_recipient_company_legal_name" value="<?php echo $user_info->pagarme_recipient_company_legal_name; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-first">
								<label for="company_name"><?php _e( 'Nome Fantasia da Empresa', 'wc-pagarme' ); ?></label>
								<input type="text" id="company_name" name="pagarme_recipient_company_name" value="<?php echo $user_info->pagarme_recipient_company_name; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-last">
								<label for="annual_revenue"><?php _e( 'Receita anual da empresa (R$)', 'wc-pagarme' ); ?></label>
								<input type="text" id="annual_revenue" name="pagarme_recipient_annual_revenue" value="<?php echo $user_info->pagarme_recipient_annual_revenue; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-first">
								<label for="main_address_zipcode"><?php _e( 'CEP', 'wc-pagarme' ); ?></label>
								<input type="tel" id="main_address_zipcode" name="pagarme_recipient_main_address_zipcode" value="<?php echo $user_info->pagarme_recipient_main_address_zipcode; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-last">
								<label for="main_address_state"><?php _e( 'Estado', 'wc-pagarme' ); ?></label>
								<select id="main_address_state" name="pagarme_recipient_main_address_state">
									<?php foreach ( $states as $state ) : ?>
										<option value="<?php echo $state['abbreviation']; ?>" <?php selected( $user_info->pagarme_recipient_main_address_state, $state['abbreviation'] ); ?>><?php echo $state['name']; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="pagarme-form-group">
								<label for="main_address_street"><?php _e( 'Logradouro', 'wc-pagarme' ); ?></label>
								<input type="text" id="main_address_street" name="pagarme_recipient_main_address_street" value="<?php echo $user_info->pagarme_recipient_main_address_street; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-first">
								<label for="main_address_street_number"><?php _e( 'Número', 'wc-pagarme' ); ?></label>
								<input type="tel" id="main_address_street_number" name="pagarme_recipient_main_address_street_number" value="<?php echo $user_info->pagarme_recipient_main_address_street_number; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-last">
								<label for="main_address_neighborhood"><?php _e( 'Bairro', 'wc-pagarme' ); ?></label>
								<input type="text" id="main_address_neighborhood" name="pagarme_recipient_main_address_neighborhood" value="<?php echo $user_info->pagarme_recipient_main_address_neighborhood; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-first">
								<label for="main_address_city"><?php _e( 'Cidade', 'wc-pagarme' ); ?></label>
								<input type="text" id="main_address_city" name="pagarme_recipient_main_address_city" value="<?php echo $user_info->pagarme_recipient_main_address_city; ?>">
							</div>
						</div>
					</div>
				</div>
				<div class="pagarme-form-card-section <?php $recipient_id ? print( 'section-completed section-close' ) : print( 'section-open' ); ?>">
					<div class="pagarme-form-section-intro">
						<div class="pagarme-form-card-section-title">  
							<span class="section-number">2. </span><?php _e( 'Representante Legal', 'wc-pagarme' ); ?>
						</div>
						<div class="pagarme-form-card-section-text">
							<span class="intro-close"><?php _e( 'Seus dados comerciais foram cadastrados com sucesso.', 'wc-pagarme' ); ?></span>
							<span class="intro-open"><?php _e( 'Se a conta for de pessoa física, preencha com seus dados pessoais. Se for de pessoa jurídica, use os dados do proprietário ou representante legal.', 'wc-pagarme' ); ?></span>
						</div>
					</div>
					<div class="pagarme-form-section-status">
						<?php if ( $recipient_id ) : ?>
						<strong class="status-completed"><?php _e( 'Concluído', 'wc-pagarme' ); ?></strong>
						<?php else : ?>
						<strong class="status-pending"><?php _e( 'Pendente', 'wc-pagarme' ); ?></strong>
						<?php endif; ?>
					</div>
					<div class="pagarme-form-section-content">
						<div class="">
							<div class="pagarme-form-group pagarme-form-group-row-first">
								<label for="full_name"><?php _e( 'Nome Completo', 'wc-pagarme' ); ?></label>
								<input type="text" id="full_name" name="pagarme_recipient_full_name" value="<?php echo $user_info->pagarme_recipient_full_name; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-last">
								<label for="document_cpf"><?php _e( 'CPF', 'wc-pagarme' ); ?></label>
								<input type="text" id="document_cpf" name="pagarme_recipient_document_cpf" value="<?php echo $user_info->pagarme_recipient_document_cpf; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-last">
								<label for="birthdate"><?php _e( 'Data de Aniversário', 'wc-pagarme' ); ?></label>
								<input type="date" id="birthdate" name="pagarme_recipient_birthdate" value="<?php echo $user_info->pagarme_recipient_birthdate; ?>">
							</div>
							<div class="pagarme-form-group pagarme-form-group-row-first">
								<label for="monthly_income"><?php _e( 'Renda Mensal (R$)', 'wc-pagarme' ); ?></label>
								<input type="text" id="monthly_income" name="pagarme_recipient_monthly_income" value="<?php echo $user_info->pagarme_recipient_monthly_income; ?>">
							</div>
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-first">
							<label for="email"><?php _e( 'E-mail', 'wc-pagarme' ); ?></label>
							<input type="email" id="email" name="pagarme_recipient_email" value="<?php echo $user_info->pagarme_recipient_email ?: $user_info->user_email; ?>">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last">
							<label for="phone"><?php _e( 'Celular', 'wc-pagarme' ); ?></label>
							<input type="tel" id="phone" name="pagarme_recipient_phone" value="<?php echo $user_info->pagarme_recipient_phone; ?>">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-first">
							<label for="address_zipcode"><?php _e( 'CEP', 'wc-pagarme' ); ?></label>
							<input type="tel" id="address_zipcode" name="pagarme_recipient_address_zipcode" value="<?php echo $user_info->pagarme_recipient_address_zipcode; ?>">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last">
							<label for="address_state"><?php _e( 'Estado', 'wc-pagarme' ); ?></label>
							<select id="address_state" name="pagarme_recipient_address_state">
								<?php foreach ( $states as $state ) : ?>
									<option value="<?php echo $state['abbreviation']; ?>" <?php selected( $user_info->pagarme_recipient_address_state, $state['abbreviation'] ); ?>><?php echo $state['name']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pagarme-form-group">
							<label for="address_street"><?php _e( 'Logradouro', 'wc-pagarme' ); ?></label>
							<input type="text" id="address_street" name="pagarme_recipient_address_street" value="<?php echo $user_info->pagarme_recipient_address_street; ?>">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-first">
							<label for="address_street_number"><?php _e( 'Número', 'wc-pagarme' ); ?></label>
							<input type="tel" id="address_street_number" name="pagarme_recipient_address_street_number" value="<?php echo $user_info->pagarme_recipient_address_street_number; ?>">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last">
							<label for="address_neighborhood"><?php _e( 'Bairro', 'wc-pagarme' ); ?></label>
							<input type="text" id="address_neighborhood" name="pagarme_recipient_address_neighborhood" value="<?php echo $user_info->pagarme_recipient_address_neighborhood; ?>">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-first">
							<label for="address_city"><?php _e( 'Cidade', 'wc-pagarme' ); ?></label>
							<input type="text" id="address_city" name="pagarme_recipient_address_city" value="<?php echo $user_info->pagarme_recipient_address_city; ?>">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last">
							<label for="occupation"><?php _e( 'Ocupação Profissional', 'wc-pagarme' ); ?></label>
							<select id="occupation" name="pagarme_recipient_occupation">
								<?php foreach ( $occupations as $occupation ) : ?>
									<option value="<?php echo $occupation; ?>" <?php selected( $user_info->pagarme_recipient_occupation, $occupation ); ?>><?php echo $occupation; ?></option>';
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
				<div class="pagarme-form-card-section <?php $bank_account_id ? print( 'section-completed section-close' ) : print( 'section-open' ); ?>">
					<div class="pagarme-form-section-intro">
						<div class="pagarme-form-card-section-title">  <span class="section-number">3. </span><?php _e( 'Configurações de Transferência', 'wc-pagarme' ); ?></div>
						<div class="pagarme-form-card-section-text">
							<!--<span class="intro-close"><?php _e( '<a href="javascript:;">Clique aqui</a> para editar seus dados de conta bancária e frequência de transferências.', 'wc-pagarme' ); ?></span>-->
							<span class="intro-close"><?php _e( 'Seus dados de conta bancária foram cadastrados com sucesso.', 'wc-pagarme' ); ?></span>
							<span class="intro-open"><?php _e( 'A conta deve estar no nome do titular do documento cadastrado. Informe a agência e conta bancária com dígito verificador (se houver).', 'wc-pagarme' ); ?></span>
						</div>
					</div>
					<div class="pagarme-form-section-status">
						<?php if ( $bank_account_id ) : ?>
						<strong class="status-completed"><?php _e( 'Concluído', 'wc-pagarme' ); ?></strong>
						<?php else : ?>
						<strong class="status-pending"><?php _e( 'Pendente', 'wc-pagarme' ); ?></strong>
						<?php endif; ?>
					</div>
					<div class="pagarme-form-section-content">
						<div class="pagarme-form-group pagarme-form-group-row-first">
							<label for="operation_type"><?php _e( 'Tipo de Operação', 'wc-pagarme' ); ?></label>
							<select id="operation_type" name="pagarme_recipient_operation_type">
								<option value="checking" <?php selected( $user_info->pagarme_recipient_operation_type, 'checking' ); ?>><?php _e( 'Conta Corrente', 'wc-pagarme' ); ?></option>
								<option value="savings" <?php selected( $user_info->pagarme_recipient_operation_type, 'savings' ); ?>><?php _e( 'Conta Poupança', 'wc-pagarme' ); ?></option>
							</select>
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last">
							<label for="bank_number"><?php _e( 'Nome do Banco', 'wc-pagarme' ); ?></label>
							<select id="bank_number" name="pagarme_recipient_bank_number">
								<?php foreach ( $banks as $bank ) : ?>
									<option value="<?php echo $bank['code']; ?>" <?php selected( $user_info->pagarme_recipient_bank_number, $bank['code'] ); ?>><?php echo sprintf( '%d - %s', $bank['code'], $bank['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-first">
							<label for="branch_number"><?php _e( 'Número da Agência (Com o Dígito se Houver)', 'wc-pagarme' ); ?></label>
							<input type="tel" id="branch_number" name="pagarme_recipient_branch_number" value="<?php echo $user_info->pagarme_recipient_branch_number; ?>" placeholder="Exemplo: 123456-9">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last">
							<label for="account_number"><?php _e( 'Número da Conta (Com Dígito se Houver)', 'wc-pagarme' ); ?></label>
							<input type="tel" id="account_number" name="pagarme_recipient_account_number" value="<?php echo $user_info->pagarme_recipient_account_number; ?>" placeholder="Exemplo: 1234-9">
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-first">
							<label for="transfer_interval"><?php _e( 'Periodicidade da Transferencia', 'wc-pagarme' ); ?></label>
							<select id="transfer_interval" name="pagarme_recipient_transfer_interval">
								<option value="daily" <?php selected( $user_info->pagarme_recipient_transfer_interval, 'daily' ); ?>><?php _e( 'Diária', 'wc-pagarme' ); ?></option>
								<option value="weekly" <?php selected( $user_info->pagarme_recipient_transfer_interval, 'weekly' ); ?>><?php _e( 'Semanal', 'wc-pagarme' ); ?></option>
								<option value="monthly" <?php selected( $user_info->pagarme_recipient_transfer_interval, 'monthly' ); ?>><?php _e( 'Mensal', 'wc-pagarme' ); ?></option>
							</select>
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last weekly_transfer_day hidden">
							<label for="weekly_transfer_day"><?php _e( 'Dia da Semana (Ocorrência)', 'wc-pagarme' ); ?></label>
							<select id="weekly_transfer_day" name="pagarme_recipient_weekly_transfer_day">
								<option value="1" <?php selected( $user_info->pagarme_recipient_weekly_transfer_day, '1' ); ?>><?php _e( 'Segunda-feira', 'wc-pagarme' ); ?></option>
								<option value="2" <?php selected( $user_info->pagarme_recipient_weekly_transfer_day, '2' ); ?>><?php _e( 'Terça-feira', 'wc-pagarme' ); ?></option>
								<option value="3" <?php selected( $user_info->pagarme_recipient_weekly_transfer_day, '3' ); ?>><?php _e( 'Quarta-feira', 'wc-pagarme' ); ?></option>
								<option value="4" <?php selected( $user_info->pagarme_recipient_weekly_transfer_day, '4' ); ?>><?php _e( 'Quinta-feira', 'wc-pagarme' ); ?></option>
								<option value="5" <?php selected( $user_info->pagarme_recipient_weekly_transfer_day, '5' ); ?>><?php _e( 'Sexta-feira', 'wc-pagarme' ); ?></option>
							</select>
						</div>
						<div class="pagarme-form-group pagarme-form-group-row-last monthly_transfer_day hidden">
							<label for="monthly_transfer_day"><?php _e( 'Dia do Mês (Ocorrência)', 'wc-pagarme' ); ?></label>
							<select id="monthly_transfer_day" name="pagarme_recipient_monthly_transfer_day">
								<?php for ( $x = 1; $x <= 30; $x++ ) : ?>
									<option value="<?php echo $x; ?>" <?php selected( $user_info->pagarme_recipient_monthly_transfer_day, $x ); ?>><?php echo $x; ?></option>
								<?php endfor; ?>
							</select>
						</div>
					</div>
				</div>
				<?php if ( in_array( $recipient_status, array( 'affiliation' ) ) ) : ?>
					<div class="pagarme-form-card-section" style="margin-top: 20px">
						<div class="pagarme-form-section-intro">
							<div class="pagarme-form-card-section-title">  <span class="section-number">3. </span><?php _e( 'Validação de Identidade', 'wc-pagarme' ); ?></div>
							<div class="pagarme-form-card-section-text">
								<?php if ( $recipient_kyc_status == 'approved' || 'active' == $recipient_status ) : ?>
								<span class="intro-close"><?php _e( 'O processo de verificação foi concluído com sucesso!', 'wc-pagarme' ); ?></span>
								<?php endif; ?>
								<?php if ( $recipient_kyc_status == 'denied' && 'active' != $recipient_status ) : ?>
								<span class="intro-close"><?php _e( 'Existem várias razões que podem ter levado a que a validação do seu documento tenha falhado entre em contato com o admistrador do site mais detalhes.', 'wc-pagarme' ); ?></span>
								<?php endif; ?>
								<?php if ( ! in_array( $recipient_kyc_status, array( 'approved', 'denied' ) ) && 'active' != $recipient_status ) : ?>
								<span class="intro-close"><?php _e( '<a href="javascript:;">Clique aqui</a> para validar sua identidade.', 'wc-pagarme' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<div class="pagarme-form-section-status">
							<?php if ( $recipient_kyc_status == 'approved' || 'active' == $recipient_status ) : ?>
							<strong class="status-completed"><?php _e( 'Concluído', 'wc-pagarme' ); ?></strong>
							<?php endif; ?>
							<?php if ( $recipient_kyc_status == 'denied' && 'active' != $recipient_status ) : ?>
							<strong class="status-denied"><?php _e( 'Reprovado', 'wc-pagarme' ); ?></strong>
							<?php endif; ?>
							<?php if ( ! in_array( $recipient_kyc_status, array( 'approved', 'denied' ) ) && 'active' != $recipient_status ) : ?>
							<strong class="status-pending"><?php _e( 'Pendente', 'wc-pagarme' ); ?></strong>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
				<div class="clear"></div>
			</div>
		</div>
	</section>
	<?php if ( ! $recipient_id ) : ?>
		<input type="submit" id="pagarme-form-submit" class="dokan-btn dokan-btn-theme dokan-btn-danger" value="<?php _e( 'Salvar Configurações', 'wc-pagarme' ); ?>">
	<?php endif; ?>
</div>
