<?php

namespace Aquapress\Pagarme\Marketplaces;

/**
 * Abstract class that will be inherited by all payments methods.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Marketplaces\Dokan class.
 *
 * @extends \Aquapress\Pagarme\Abstracts\Gateway.
 */
class Dokan extends \Aquapress\Pagarme\Abstracts\Marketplace {

	/**
	 * Connector identifier.
	 *
	 * @var string
	 */
	public $id = 'dokan';

	/**
	 * Running the connector actions.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp_footer', array( $this, 'inline_script' ), 15 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 90 );
		add_action( 'wp_ajax_update_recipient_data', array( $this, 'update_recipient_data_action' ) );
		add_action( 'wp_ajax_get_recipient_payables', array( $this, 'get_recipient_payables_action' ) );

		add_action( 'dokan_settings_sections', array( $this, 'admin_settings' ) );
		add_action( 'dokan_settings_fields', array( $this, 'admin_settings_fieldsr' ) );
		add_action( 'dokan_dashboard_content_inside_before', array( $this, 'show_verification_alert' ) );
		add_action( 'dokan_load_custom_template', array( $this, 'load_custom_page_templates' ), 100 );

		add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_nav_links' ), 20 );
		add_filter( 'dokan_seller_wizard_steps', array( $this, 'seller_wizard_steps' ), 20 );
		add_filter( 'dokan_withdraw_methods', array( $this, 'withdraw_register_methods' ), 20 );
		add_filter( 'dokan_withdraw_method_icon', array( $this, 'withdraw_get_method_icon' ), 20, 2 );
		add_filter( 'dokan_withdraw_method_additional_info', array( $this, 'withdraw_get_method_additional_info' ), 20, 2 );
		add_filter( 'dokan_is_seller_connected_to_payment_method', array( $this, 'withdraw_seller_connected_to_payment_method' ), 20, 3 );
		add_filter( 'dokan_get_template_part', array( $this, 'replace_payment_method_template_part' ), 20, 3 );
		add_filter( 'dokan_query_var_filter', array( $this, 'set_custom_query_vars' ), 100 );
		add_action( 'dokan_order_details_after_customer_info', array( $this, 'set_vendor_order_summary_details' ), 100 );

		add_action( 'wc_pagarme_get_vendor_suborder', array( $this, 'filter_vendor_suborder' ), 10, 3 );
		add_action( 'wc_pagarme_get_vendor_suborder_url', array( $this, 'filter_vendor_suborder_url' ), 10, 2 );
		add_action( 'wc_pagarme_recipient_transactions_table_column_order', array( $this, 'print_recipient_transactions_column_order' ), 100 );
		
		add_action( 'pre_get_posts', array( $this, 'filter_invalids_vendor_products' ), 100 );
		add_action( 'woocommerce_check_cart_items', array( $this, 'filter_invalids_products_add_cart' ), 100 );
	}

	/**
	 * Set dokan admin section.
	 *
	 * @param array $sections All registered sections.
	 * @return array
	 */
	public function admin_settings( $sections ) {
		$sections[] = array(
			'id'                   => 'wc_pagarme_marketplace_settings',
			'title'                => __( 'Pagar.me', 'wc-pagarme' ),
			'description'          => __( 'Opções do Marketplace', 'wc-pagarme' ),
			'icon_url'             => WC_PAGARME_URI . 'assets/img/pagarme-icon.png',
			'settings_title'       => __( 'Configurações da Integração', 'wc-pagarme' ),
			'settings_description' => __( 'Personalize e gerencie as preferências de configuração para a integração com a Pagar.me.', 'wc-pagarme' ),
		);

		return $sections;
	}

	/**
	 * Set dokan admin section fields.
	 *
	 * @param array $settings_fields All registered fields.
	 * @return array
	 */
	function admin_settings_fieldsr( $settings_fields ) {
		$options  = array( '' => __( '--- Nenhum Usuário Selecionado ---', 'wc-pagarme' ) );
		$profiles = $blogusers = get_users( array( 'role__in' => array( 'administrator' ) ) );

		foreach ( $profiles as $profile ) {
			$options[ $profile->ID ] = $profile->display_name;
		}

		$settings_fields['wc_pagarme_marketplace_settings'] = array(
			'integration'  => array(
				'name'  => 'integration_settings',
				'label' => __( 'Configurações de Integração', 'wc-pagarme' ),
				'type'  => 'sub_section',
			),
			'public_key'   => array(
				'name'  => 'public_key',
				'label' => __( 'Chave Pública', 'wc-pagarme' ),
				'desc'  => sprintf( __( 'Por favor, insira sua chave de API Pública Pagar.me. Ela é necessária para receber pagamentos e criptografar dados de transações. É possível obter sua chave de API em %s.', 'wc-pagarme' ), '<a href="https://pagarme.helpjuice.com/p10-minha-conta/configura%C3%A7%C3%B5es-gestao-de-chaves" target="_blank">' . __( 'Pagar.me Dashboard > Configurações  > Dados da API', 'wc-pagarme' ) . '</a>' ),
			),
			'secret_key'   => array(
				'name'  => 'secret_key',
				'label' => __( 'Chave Secreta', 'wc-pagarme' ),
				'desc'  => sprintf( __( 'Por favor, insira sua chave de API Secreta Pagar.me. Ela é necessária para a homologação dos vendedores, relatórios de recebimentos e as notificações de URL. É possível obter sua chave de API em %s.', 'wc-pagarme' ), '<a href="https://pagarme.helpjuice.com/p10-minha-conta/configura%C3%A7%C3%B5es-gestao-de-chaves" target="_blank">' . __( 'Pagar.me Dashboard > Configurações  > Dados da API', 'wc-pagarme' ) . '</a>' ),
			),
			'commission'   => array(
				'name'  => 'commission',
				'label' => __( 'Configurações de Comissões', 'wc-pagarme' ),
				'type'  => 'sub_section',
			),
			'recipient_id' => array(
				'name'  => 'recipient_id',
				'label' => __( 'Recebedor ID', 'wc-pagarme' ),
				'desc'  => sprintf( __( 'Por favor, insira seu ID de recebedor Pagar.me. É necessário criar e configurar um recebedor para que o marketplace participe da divisão dos valores de venda. É possível criar um recebedor em %s.', 'wc-pagarme' ), '<a href="https://pagarme.helpjuice.com/pt_BR/p2-manual-da-dashboard/dashboard-%7C-criar-recebedores-e-validar-identidade" target="_blank">' . __( 'Pagar.me Dashboard > Recebedores', 'wc-pagarme' ) . '</a>' ),
			),
			'integration'  => array(
				'name'  => 'integration_settings',
				'label' => __( 'Configurações de Integração', 'wc-pagarme' ),
				'type'  => 'sub_section',
			),
			'marketplace'       => array(
				'name'  => 'marketplace',
				'label' => __( 'Configurações do marketplace', 'wc-pagarme' ),
				'type'  => 'sub_section',
			),
			'require_recipient_id' => array(
				'name'    => 'require_recipient_id',
				'label'   => __( 'Requer ID de Recebedor', 'wc-pagarme' ),
				'desc'    => __( 'Bloqueia a compra e a visualização de produtos para vendedores sem um ID de recebedor Pagar.me.', 'wc-pagarme' ),
				'type'    => 'switcher',
				'default' => 'no',
			),
			'others'       => array(
				'name'  => 'others',
				'label' => __( 'Configurações Adicionais', 'wc-pagarme' ),
				'type'  => 'sub_section',
			),
			'debug'        => array(
				'name'    => 'debug',
				'label'   => __( 'Habilitar Logs', 'wc-pagarme' ),
				'desc'    => sprintf( __( 'Registre eventos da Pagar.me como solicitações de API. Você pode verificar o log em %s', 'wc-pagarme' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( 'wc_pagarme_dokan' ) . '-' . sanitize_file_name( wp_hash( 'wc_pagarme_dokan' ) ) . '.log' ) ) . '">' . __( 'Status do sistema &gt; Logs', 'wc-pagarme' ) . '</a>' ),
				'type'    => 'switcher',
				'default' => 'yes',
			),
		);

		return $settings_fields;
	}

	/**
	 * Print Dokan inline scripts to recipient templates.
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @return void
	 */
	function inline_script() {
		global $wp_query;

		if ( ( isset( $wp_query->query['finances'] ) && in_array( $wp_query->query['finances'], array( 'calendar' ) ) )
			|| ( isset( $wp_query->query['settings'] ) && in_array( $wp_query->query['settings'], array( 'payment-manage-pagarme-edit', 'payment-manage-pagarme' ) ) ) ) {
			?>
		<script>
			var PAGARME_MKTPC = {
				ajaxurl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
				contenturl: '<?php echo esc_url( plugins_url( '/', dirname( __DIR__, 1 ) ) ); ?>',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wc_pagarme_verify_action' ) ); ?>'
			};
		</script>
			<?php
		}
	}

	/**
	 * Enqueue Dokan Dashboard Scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		global $wp_query;

		if ( isset( $wp_query->query['settings'] ) && in_array( $wp_query->query['settings'], array( 'payment-manage-pagarme-edit', 'payment-manage-pagarme' ) ) ) {
			wp_enqueue_style( 'pagarme-recipient-form-styles', WC_PAGARME_URI . 'assets/css/marketplace/recipient-form.css', true, WC_PAGARME_VERSION );
			wp_enqueue_script( 'pagarme-recipient-form-scripts', WC_PAGARME_URI . 'assets/js/marketplace/recipient-form.js', array( 'jquery' ), WC_PAGARME_VERSION, true );
			wp_enqueue_script( 'jquery-mask' );
		}
		if ( isset( $wp_query->query['finances'] ) && in_array( $wp_query->query['finances'], array( 'transactions' ) ) ) {
			wp_enqueue_style( 'pagarme-recipient-transactions-styles', WC_PAGARME_URI . 'assets/css/marketplace/recipient-transactions.css', true, WC_PAGARME_VERSION );
		}
		if ( isset( $wp_query->query['finances'] ) && in_array( $wp_query->query['finances'], array( 'calendar' ) ) ) {
			wp_enqueue_style( 'pagarme-recipient-calendar-styles', WC_PAGARME_URI . 'assets/css/marketplace/recipient-calendar.css', true, WC_PAGARME_VERSION );
			wp_enqueue_script( 'pagarme-recipient-calendar-scripts', WC_PAGARME_URI . 'assets/js/marketplace/recipient-calendar.js', array( 'jquery' ), WC_PAGARME_VERSION, true );
			wp_enqueue_style( 'fullcalendar', WC_PAGARME_URI . 'assets/vendor/fullcalendar/css/main.min.css', true, WC_PAGARME_VERSION );
			wp_enqueue_script( 'fullcalendar', WC_PAGARME_URI . 'assets/vendor/fullcalendar/js/main.min.js', true, array(), false );
			wp_enqueue_script( 'fullcalendar-locales', WC_PAGARME_URI . 'assets/vendor/fullcalendar/js/locales-all.min.js', array( 'fullcalendar' ), '5.1.0', false );
		}
	}

	/**
	 * Show verification alert for kyc process.
	 *
	 * @return void
	 */
	public function show_verification_alert() {
		$current_user_id   = get_current_user_id();
		$current_user_info = get_userdata( $current_user_id );

		$recipient_id         = static::get_user_option( $current_user_id, 'pagarme_recipient_id', $this->settings['testmode'] );
		$recipient_status     = static::get_user_option( $current_user_id, 'pagarme_recipient_status', $this->settings['testmode'] );
		$recipient_kyc_status = static::get_user_option( $current_user_id, 'pagarme_recipient_kyc_status', $this->settings['testmode'] );
		?>
		<?php if ( in_array( $recipient_status, array( 'affiliation' ) ) ) : ?>
			<div class="dokan-alert dokan-alert-warning">
				<?php _e( sprintf( 'Caro parceiro, agora é necessário validar alguns dados da sua conta para garantir a transferência das comissões de vendas. <a href="%s" style="color: black;font-weight: 800;text-decoration: underline;">→	Acesse a seguinte página a fim de realizar o processo de verificação e conclusão do cadastro.</a>', dokan_get_navigation_url( 'verification_kyc' ) ), 'wc-pagarme' ); ?>
			</div>
		<?php endif ?>

		<?php if ( ! $recipient_id ) : ?>
			<div class="dokan-alert dokan-alert-warning">
				<?php _e( sprintf( 'Caro parceiro, é necessario completar seu cadastro para receber os pagamentos de suas vendas. <a href="%s" style="color: black;font-weight: 800;text-decoration: underline;">→	Acesse a seguinte página para inserir as demais informações de pagamento.</a>', dokan_get_navigation_url( 'settings/payment-manage-pagarme-edit' ) ), 'wc-pagarme' ); ?>
			</div>
		<?php endif ?>
		
		<?php
	}

	/**
	 * Show verification link in navbar for kyc process.
	 *
	 * @return void
	 */
	public function add_nav_links( $nav_links ) {
		$current_user_id   = get_current_user_id();
		$current_user_info = get_userdata( $current_user_id );

		$recipient_id         = static::get_user_option( $current_user_id, 'pagarme_recipiente_id', $this->settings['testmode'] ); // TODO: change to "pagarme_recipient_id" in future
		$recipient_status     = static::get_user_option( $current_user_id, 'pagarme_recipient_status', $this->settings['testmode'] );
		$recipient_kyc_status = static::get_user_option( $current_user_id, 'pagarme_recipient_kyc_status', $this->settings['testmode'] );

		// Set kyc verification page when recipient status equals "affiliation".
		if ( in_array( $recipient_status, array( 'affiliation' ) ) ) {
			$nav_links['verification_kyc'] = array(
				'title' => __( 'Verificação', 'wc-pagarme' ),
				'icon'  => '<i class="fa fa-check-square-o"></i>',
				'url'   => dokan_get_navigation_url( 'verification_kyc' ),
				'pos'   => 51,
			);
		}

		// Set recipient form in payments settings submenu.
		if ( ! isset( $nav_links['settings']['submenu']['payment'] ) ) {
			$nav_links['settings']['submenu']['payments'] = array(
				'title' => __( 'Pagamento', 'wc-pagarme' ),
				'icon'  => '<i class="fa fa-usd"></i>',
				'url'   => dokan_get_navigation_url( 'settings/payment' ),
				'pos'   => 2,
			);
		}

		//Set dashboard menu for transactions and calendar.
		$nav_links['finances'] = array(
			'title' => sprintf( '%s <i class="fa fa-angle-right pull-right"></i>', __( 'Minhas Finanças', 'wc-pagarme' ) ),
			'icon'  => '<i class="fa fa-dollar"></i>',
			'url'   => dokan_get_navigation_url( 'finances/transactions' ),
			'pos'   => 51,
			'sub'   => array(
				'back'                  => array(
					'title' => __( 'Voltar para o Painel', 'wc-pagarme' ),
					'icon'  => '<i class="fa fa-long-arrow-left"></i>',
					'url'   => dokan_get_navigation_url(),
					'pos'   => 10,
				),
				'finances/transactions' => array(
					'title' => __( 'Movimentações', 'wc-pagarme' ),
					'icon'  => '<i class="fa fa-dollar"></i>',
					'url'   => dokan_get_navigation_url( 'finances/transactions' ),
					'pos'   => 10,
				),
				'finances/calendar'     => array(
					'title'      => __( 'Recebimentos', 'wc-pagarme' ),
					'icon'       => '<i class="fa fa-calendar"></i>',
					'url'        => dokan_get_navigation_url( 'finances/calendar' ),
					'pos'        => 30,
					'permission' => 'dokan_view_store_settings_menu',
				),
			),
		);

		// Active selected custom menus.
		global $wp;
		$request         = $wp->request;
		$active          = explode( '/', $request );
		$active_finances = ( in_array( 'transactions', $active ) && in_array( 'finances', $active ) ) || ( in_array( 'calendar', $active ) && in_array( 'finances', $active ) );

		if ( true === $active_finances ) {
			return $nav_links['finances']['sub'];
		}
		return $nav_links;
	}

	/**
	 * Show custom page templates for recipient features.
	 *
	 * @return void
	 */
	public function load_custom_page_templates( $dokan_menus ) {
		if ( isset( $dokan_menus['verification_kyc'] ) ) {
			$this->show_recipient_verification_template();
		} elseif ( isset( $dokan_menus['finances'] ) && $dokan_menus['finances'] == 'transactions' ) {
			$this->show_recipient_transactions_template();
		} elseif ( isset( $dokan_menus['finances'] ) && $dokan_menus['finances'] == 'calendar' ) {
			$this->show_recipient_calendar_template();
		}
	}

	/**
	 * Show verification page for kyc process.
	 *
	 * @return void
	 */
	public function set_custom_query_vars( $dokan_menus ) {
		$dokan_menus['finances']         = 'finances';
		$dokan_menus['verification_kyc'] = 'verification_kyc';

		return $dokan_menus;
	}

	/**
	 * Print payment method details in vendor order dashboard.
	 *
	 * @return void
	 */
	public function set_vendor_order_summary_details( $order ) {
		$vendor_order = ( $order->get_parent_id() != 0 ) ? wc_get_order( $order->get_parent_id() ) : $order;
		?>
		<?php if ( $vendor_order->get_payment_method() == 'wc_pagarme_creditcard' ) : ?>
			<li>
				<span><?php _e( 'Número de parcelas:', 'wc-pagarme' ); ?> </span>
				<?php echo $vendor_order->get_meta( 'PAGARME_CARD_INSTALLMENTS' ); ?>
			</li>
		<?php elseif ( $vendor_order->get_payment_method() == 'wc_pagarme_boleto' ) : ?>
			<li>
				<span><?php _e( 'URL do boleto:', 'wc-pagarme' ); ?> </span>
				<p><?php echo $vendor_order->get_meta( 'PAGARME_BOLETO_URL' ); ?></p>
			</li>
		<?php elseif ( $vendor_order->get_payment_method() == 'wc_pagarme_pix' ) : ?>
			<li>
				<span><?php _e( 'Código PIX:', 'wc-pagarme' ); ?> </span>
				<p><?php echo $vendor_order->get_meta( 'PAGARME_PIX_QRCODE_URL' ); ?></p>
			</li>
		<?php endif; ?>
		<?php
	}

	/**
	 * Replace the setup wizard.
	 *
	 * @return void
	 */
	public function seller_wizard_steps( $steps ) {
		unset( $steps['payment'] );

		return $steps;
	}

	/**
	 * Adds a callback function and title for the 'pagarme' withdrawal method.
	 *
	 * @param array $methods The array of existing withdrawal methods.
	 *
	 * @return array The modified array of withdrawal methods with the added 'pagarme' method details.
	 */
	public function withdraw_register_methods( $methods ) {
		$methods['pagarme']['title']    = __( 'Pagar.me', 'wc-pagarme' ); // title can be changed as per your need
		$methods['pagarme']['callback'] = array( $this, 'withdraw_method_callback' );

		return $methods;
	}

	/**
	 * Callback for Pagar.me in store settings
	 *
	 * @param array    $store_settings
	 *
	 * @return void
	 */
	public function withdraw_method_callback( $profile_info = array() ) {
		parent::output_recipient_form_template();
	}

	/**
	 * Get withdraw method formatted icon.
	 *
	 * @param string $method_key Withdraw Method key
	 *
	 * @return string
	 */
	public function withdraw_get_method_icon( $method_icon, $method_key ) {
		if ( 'pagarme' == $method_key ) {
			return WC_PAGARME_URI . 'assets/img/pagarme-icon.png';
		}

		return $method_icon;
	}

	/**
	 * Get withdraw method additional info.
	 *
	 * @param string $method_key Withdraw Method key
	 *
	 * @return string
	 */
	public function withdraw_get_method_additional_info( $method_info, $method_key ) {
		// TODO: It is only avalible when the seller has an initial configuration in $profile_info in "templates/settings/payment.php".
		if ( 'pagarme' == $method_key ) {
			return ''; // Empty until the future.
		}

		return $method_info;
	}

	/**
	 * Get if user with id $seller_id is connected to the payment method having $payment_method_id.
	 *
	 * @param bool   $is_connected
	 * @param string $payment_method_id
	 * @param int    $seller_id
	 *
	 * @return bool
	 */
	public function withdraw_seller_connected_to_payment_method( $is_connected, $payment_method_id, $seller_id ) {
		if ( 'pagarme' == $payment_method_id ) {
			return true; //Forces the inclusion of the payment method without initial configuration by the seller.
		}

		return $is_connected;
	}

	/**
	 * Change template directory path filter.
	 *
	 * @param string $template
	 * @param string $slug
	 * @param string $name
	 *
	 * @return string
	 */
	public function replace_payment_method_template_part( $template, $slug, $name ) {
		global $wp_query;

		if ( 'settings/payment' == $slug && 'manage' === $name ) {
			if ( isset( $wp_query->query['settings'] ) && in_array( $wp_query->query['settings'], array( 'payment-manage-pagarme-edit', 'payment-manage-pagarme' ) ) ) {
				return WC_PAGARME_PATH . 'templates/dokan/settings/payment-manage.php';
			}
		}

		return $template;
	}

	/**
	 * Get recipient transactions template.
	 *
	 * @return void
	 */
	public function show_recipient_transactions_template() {
		?>
		<div class="dokan-dashboard-wrap">
			<?php do_action( 'dokan_dashboard_content_before' ); ?>
			<div class="dokan-dashboard-content">
				<?php do_action( 'dokan_dashboard_content_inside_before' ); ?>
				<article>
					<header class="dokan-dashboard-header">
						<h1 class="entry-title"><?php _e( 'Movimentações', 'wc-pagarme' ); ?></h1>
					</header>
					<?php parent::output_recipient_transactions_template(); ?>
				</article>
				<?php do_action( 'dokan_pagarme_transactions_content_inside_after' ); ?>
			</div>
			<?php do_action( 'dokan_dashboard_content_after' ); ?>
		</div>
		<?php
	}

	/**
	 * Get recipient transactions template.
	 *
	 * @return void
	 */
	public function show_recipient_calendar_template() {
		?>
		<div class="dokan-dashboard-wrap">
			<?php do_action( 'dokan_dashboard_content_before' ); ?>
			<div class="dokan-dashboard-content">
				<?php do_action( 'dokan_dashboard_content_inside_before' ); ?>
				<article>
					<header class="dokan-dashboard-header">
						<h1 class="entry-title"><?php _e( 'Calendário de Recebimentos', 'wc-pagarme' ); ?></h1>
					</header>
					<?php parent::output_recipient_calendar_template(); ?>
				</article>
				<?php do_action( 'dokan_pagarme_calendar_content_inside_after' ); ?>
			</div>
			<?php do_action( 'dokan_dashboard_content_after' ); ?>
		</div>
		<?php
	}

	/**
	 * Get recipient verification kyc template.
	 *
	 * @return void
	 */
	public function show_recipient_verification_template() {
		?>
		<div class="dokan-dashboard-wrap">
			<?php do_action( 'dokan_dashboard_content_before' ); ?>
			<div class="dokan-dashboard-content">
				<article>
					<?php parent::output_recipient_verification_template(); ?>
				</article>
				<?php do_action( 'dokan_pagarme_transactions_content_inside_after' ); ?>
			</div>
			<?php do_action( 'dokan_dashboard_content_after' ); ?>
		</div>
		<?php
	}

	/**
	 * Build split rules for payment data.
	 *
	 * @param mixed                                  $the_order           Woocommerce Order ID or Object WC_Order.
	 * @param \Aquapress\Pagarme\Abstracts\Gateway   $context             The Pagarme gateway object.
	 *
	 * @return \Aquapress\Pagarme\Models\Split_Data Split data object.
	 */
	public function split_data( $the_order, $context ) {
		// Get empty split data object.
		$data = new \Aquapress\Pagarme\Models\Split_Data();
		// Get woocommerce order data.
		$order = wc_get_order( $the_order );
		// Get dokan orders data.
		$vendors_orders = $this->get_dokan_vendors_suborders( $order );
		// Calcule vendors commission.
		if ( is_array( $vendors_orders ) && ! empty( $vendors_orders ) ) {
			// Loop dokan orders data.
			foreach ( $vendors_orders as $tmp_order ) {
				$tmp_order_id = dokan_get_prop( $tmp_order, 'id' );
				$vendor_id    = dokan_get_seller_id_by_order( $tmp_order_id );
				// Get pagarme recipient id from user.
				$recipient_id = static::get_user_option( $vendor_id, 'pagarme_recipient_id', $this->settings['testmode'] );
				if ( ! $recipient_id ) {
					continue;
				}
				// Get order commission from dokan order.
				$sale_data = $this->get_dokan_commission_data( $tmp_order_id, $vendor_id );
				if ( ! $sale_data ) {
					continue;
				}
				// Get commission amount and add to split data.
				$vendor_order_amount = round( $sale_data->net_amount * 100, 0 );
				$data->add_to_split( $recipient_id, $vendor_order_amount );
			}
		}
		// Build marketplace split rule.
		if ( $data->get_data() ) {
			// Get parent order total.
			$order_total = $order->get_total() * 100;
			// Get all vendor commission amount to reduce in the order total.
			$vendors_commission = array_reduce(
				$data->get_data(),
				function ( $carry, $item ) {
					return isset( $item['amount'] ) ? $carry + $item['amount'] : $carry;
				},
				0
			);
			// Calculate marketplace commission.
			$marketplace_commission = round( ( $order_total - $vendors_commission ), 0 );
			// Set marketplace commission to split rule.
			$data->add_to_split( $this->settings['recipient_id'], $marketplace_commission, false, true, true );
		}

		return $data;
	}

	/**
	 * Get vendors orders by parent order id.
	 *
	 * @param  object  $parent_order
	 * @return array
	 */
	public function get_dokan_vendors_suborders( $parent_order ) {
		$all_orders   = array();
		$has_suborder = get_post_meta( $parent_order->get_id(), 'has_sub_order', true );
		// put orders in an array
		// if has sub-orders, pick only sub-orders
		// if it's a single order, get the single order only
		if ( $has_suborder == '1' ) {
			$suborders = get_children(
				array(
					'post_parent' => $parent_order->get_id(),
					'post_type'   => 'shop_order',
				)
			);

			foreach ( $suborders as $suborder ) {
				$sub_order    = wc_get_order( $suborder->ID );
				$all_orders[] = $sub_order;
			}
		} else {
			$all_orders[] = $parent_order;
		}

		return $all_orders;
	}

	/**
	 * Get vendor suborders by parent order id and vendor id.
	 *
	 * @param  int  $vendor_id
	 * @param  object  $parent_order
	 * @return array|false
	 */
	public function get_dokan_vendor_suborder( $vendor_id = false, $parent_order = false ) {

		if ( $vendor_id && $parent_order ) {

			$all_orders = $this->get_dokan_vendors_suborders( $parent_order );

			foreach ( $all_orders as $order ) {
				$order_id        = dokan_get_prop( $order, 'id' );
				$order_vendor_id = dokan_get_seller_id_by_order( $order_id );

				if ( $order_vendor_id == $vendor_id ) {
					return $order;
				}
			}
		}

		return false;
	}

	/**
	 * Get dokan order details
	 *
	 * @param  int  $order_id
	 * @param  int  $seller_id
	 * @return array|null
	 */
	public function get_dokan_commission_data( $order_id, $seller_id ) {
		global $wpdb;

		$sql = "SELECT *
		FROM {$wpdb->prefix}dokan_orders AS do
		WHERE
		do.seller_id = %d AND
		do.order_id = %d";

		return $wpdb->get_row( $wpdb->prepare( $sql, $seller_id, $order_id ) );
	}

	/**
	 * Print dokan order link in operations table.
	 *
	 * @param  array  $data
	 * @return void
	 */
	public function print_recipient_transactions_column_order( $operation ) {
		$parent_order = parent::get_order_by_gateway_id( $operation['movement_object']['gateway_id'] );
		$vendor_order = $this->get_dokan_vendor_suborder( $parent_order );
		?>
			<?php if ( current_user_can( 'dokan_view_order' ) ) : ?>
				<?php if ( ! is_null( $vendor_order ) ) : ?>
					<?php echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'order_id' => $vendor_order->ID ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) ) . '"><strong>' . esc_attr( $vendor_order->ID ) . '</strong></a>'; ?>
				<?php endif; ?>
				<?php else : ?>
					<?php echo '<strong>' . sprintf( 'Ordem %s', esc_attr( $vendor_order->ID ) ) . '</strong>'; ?>
				<?php endif ?>
		<?php
	}

	/**
	 * Filter vendor suborders by parent order id and vendor id.
	 *
	 * @param  int  $vendor_id
	 * @param  object  $parent_order
	 *
	 * @see  $this->payables_filter_by_day()
	 *
	 * @return array
	 */
	public function filter_vendor_suborder( $suborder = false, $vendor_id = false, $parent_order = false ) {
		if ( $vendor_id && $parent_order ) {
			$suborder = $this->get_dokan_vendor_suborder( $vendor_id, $parent_order );
		}

		return $suborder;
	}

	/**
	 * Filter vendor suborders URL.
	 *
	 * @param  string  $suborder_url
	 * @param  object  $vendor_suborder
	 *
	 * @see  $this->payables_filter_by_day()
	 *
	 * @return string
	 */
	public function filter_vendor_suborder_url( $suborder_url, $vendor_suborder = false ) {
		if ( $vendor_suborder ) {
			$suborder_url = esc_url( wp_nonce_url( add_query_arg( array( 'order_id' => $vendor_suborder->get_id() ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) );
		}

		return $suborder_url;
	}

	public function filter_invalids_vendor_products( $query ) {

		if ( 'on' !== $this->settings['require_recipient_id'] || ! $query->is_main_query() || ( ! is_post_type_archive( 'product' ) && ! is_shop() && ! is_search() ) ) {
			return;
		}

		$roles = array( 'seller', 'administrator' );
		$vendors = array();

		foreach ( $roles as $role ) {
			$users = get_users( array(
				'role'       => $role,
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'pagarme_recipient_id',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'pagarme_recipient_id',
						'value'   => '',
						'compare' => '=',
					),
				),
				'fields' => 'ID',
			) );

			$vendors = array_merge( $vendors, $users );
		}

		if ( ! empty( $vendors ) ) {
			$query->set( 'author__not_in', $vendors );
		}
	}

	public function filter_invalids_products_add_cart() {
		if ( 'on' != $this->settings['require_recipient_id'] ) {
			return;
		}
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];
			$vendor     = dokan_get_vendor_by_product( $product_id );
			$seller_id  = $vendor ? $vendor->get_id() : 0;

			$recipient  = trim( get_user_meta( $seller_id, 'pagarme_recipient_id', true ) );

			if ( empty( $recipient ) ) {
				wc_add_notice( __( 'Um ou mais produtos no seu carrinho pertencem a vendedores sem uma conta de pagamento válida.', 'wc-pagarme' ), 'error' );
				WC()->cart->empty_cart();
				break;
			}
		}
	}

	/**
	 * Check the requirements.
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( class_exists( 'WeDevs_Dokan' ) ) {
			return true;
		}

		return false;
	}
}
