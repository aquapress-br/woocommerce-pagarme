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
	 * Start dokan connector.
	 *
	 * @return   void
	 */
	public function __construct() {
		// Init actions for the connector.
		parent::init_connector();
	}
	
	/**
	 * Running the connector actions.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
		add_action( 'wp_ajax_update_recipient_data', array( $this, 'update_recipient_data' ) );
		
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
	}

	/**
	 * Set dokan admin section.
	 *
	 * @param array $sections All registered sections.
	 * @return array
	 */
	public function admin_settings( $sections ) {
		$sections[] = array(
			'id'  => 'wc_pagarme_marketplace_settings',
			'title' => __( 'Pagar.me', 'wc-pagarme' ),
			'description' => __( 'Opções do Marketplace', 'wc-pagarme' ),
			'icon_url'  => WC_PAGARME_URI . 'assets/img/pagarme-icon.png',
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
	function admin_settings_fieldsr( $settings_fields ) 
	{		
		$options = array( '' => __( '--- Nenhum Usuário Selecionado ---', 'wc-pagarme' ) );
		$profiles = $blogusers = get_users( array( 'role__in' => array( 'administrator' ) ) );
		
		foreach( $profiles as $profile ) {
			$options[ $profile->ID ] = $profile->display_name;
		}
		
		$settings_fields['wc_pagarme_marketplace_settings'] = array(
			'secret_key'  => array(
				'name'    => 'secret_key',
				'label'   => __( 'Pagar.me API Key', 'wc-pagarme' ),
				'desc'    => sprintf( __( 'Por favor, insira sua chave de API Pagar.me. Ela é necessária para a homologação dos vendedores, relatórios de recebimentos e as notificações de URL. É possível obter sua chave de API em %s.', 'wc-pagarme' ), '<a href="https://dashboard.pagar.me/">' . __( 'Pagar.me Dashboard > Página Minha Conta', 'wc-pagarme' ) . '</a>' ),
            ),
			'debug' => array(
				'name'    => 'debug',
				'label'   => __( 'Habilitar Logs', 'wc-pagarme' ),
				'desc'    => sprintf( __( 'Registre eventos da Pagar.me como solicitações de API. Você pode verificar o log em %s', 'wc-pagarme' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( 'wc_pagarme_dokan' ) . '-' . sanitize_file_name( wp_hash( 'wc_pagarme_dokan' ) ) . '.log' ) ) . '">' . __( 'Status do sistema &gt; Logs', 'wc-pagarme' ) . '</a>' ),
				'type'    => 'switcher',
				'default' => 'yes',
			)
		);

		return $settings_fields;
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
			wp_enqueue_script( 'jquery-mask', plugin_dir_url( __DIR__ ) . WC_PAGARME_URI . 'assets/js/jquery.mask.js', array( 'jquery' ), '1.14.10', true );
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

		$recipient_id         = get_user_meta( $current_user_id, 'pagarme_recipiente_id', true ); // TODO: change to "pagarme_recipient_id" in future
		$recipient_status     = get_user_meta( $current_user_id, 'pagarme_recipient_status', true );
		$recipient_kyc_status = get_user_meta( $current_user_id, 'pagarme_recipient_kyc_status', true );		
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
		
		$recipient_id         = get_user_meta( $current_user_id, 'pagarme_recipiente_id', true ); // TODO: change to "pagarme_recipient_id" in future
		$recipient_status     = get_user_meta( $current_user_id, 'pagarme_recipient_status', true );
		$recipient_kyc_status = get_user_meta( $current_user_id, 'pagarme_recipient_kyc_status', true );

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
		if ( !isset( $nav_links['settings']['submenu']['payment'] ) ) {
			$nav_links['settings']['submenu']['payments'] = array(
				'title' => __( 'Pagamento', 'wc-pagarme' ),
				'icon'  => '<i class="fa fa-usd"></i>',
				'url'   => dokan_get_navigation_url( 'settings/payment' ),
				'pos'   => 2,
			);
		}
		
		//Set dashboard menu for transactions and calendar.
		$nav_links['finances'] = array(
			'title' => sprintf('%s <i class="fa fa-angle-right pull-right"></i>', __( 'Minhas Finanças', 'wc-pagarme' ) ),
			'icon'  => '<i class="fa fa-dollar"></i>',
			'url'   => dokan_get_navigation_url( 'finances/transactions' ),
			'pos'   => 51,
			'sub'   => array(
				'back' => array(
					'title' => __( 'Voltar para o Painel', 'wc-pagarme' ),
					'icon'  => '<i class="fa fa-long-arrow-left"></i>',
					'url'   => dokan_get_navigation_url(),
					'pos'   => 10
				),
				'finances/transactions' => array(
					'title' => __( 'Movimentações', 'wc-pagarme' ),
					'icon'  => '<i class="fa fa-dollar"></i>',
					'url'   => dokan_get_navigation_url( 'finances/transactions' ),
					'pos'   => 10
				),
				'finances/calendar' => array(
					'title'      => __( 'Recebimentos', 'wc-pagarme' ),
					'icon'       => '<i class="fa fa-calendar"></i>',
					'url'        => dokan_get_navigation_url( 'finances/calendar' ),
					'pos'        => 30,
					'permission' => 'dokan_view_store_settings_menu'
				)
			)
		);

		// Active selected custom menus.
		global $wp;
		$request = $wp->request;
		$active = explode('/', $request);
		$active_finances = (in_array('transactions', $active) && in_array('finances', $active)) || (in_array('calendar', $active) && in_array('finances', $active));
		
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
		} else if ( isset( $dokan_menus['finances'] ) && $dokan_menus['finances'] == 'transactions' ) {
			$this->show_recipient_transactions_template();
		}  else if ( isset( $dokan_menus['finances'] ) && $dokan_menus['finances'] == 'calendar' ) {
			//parent::output_recipient_verification_template();
		}
	}

	/**
	 * Show verification page for kyc process.
	 *
	 * @return void
	 */
	public function set_custom_query_vars( $dokan_menus ) {
		$dokan_menus['finances'] = 'finances';
		$dokan_menus['verification_kyc'] = 'verification_kyc';

		return $dokan_menus;
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
		$methods['pagarme']['callback'] = array( __CLASS__, 'withdraw_method_callback' );

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
     * @param Aquapress\Pagarme\Models\Model_Split   $data      Split data object.
     * @param int                                    $order_id  Woocommerce order ID.
     * @param WC_Asaas\Gateway\Gateway               $gateway   The assas gateway object.
	 *
     * @return Aquapress\Pagarme\Models\Model_Split
     */
    public function split_data( $data, $order_id, $gateway ) {
		
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
