<?php

namespace Aquapress\Pagarme\Gateways;

/**
 * Process payment with Boleto.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Gateways\Boleto class.
 *
 * @extends \Aquapress\Pagarme\Abstracts\Gateway.
 */
class Boleto extends \Aquapress\Pagarme\Abstracts\Gateway {

	/**
	 * Start payment method.
	 *
	 * @return   void
	 */
	public function __construct() {
		$this->id                 = 'wc_pagarme_boleto';
		$this->method_title       = __( 'Pagar.me', 'wc-pagarme' );
		$this->method_description = __(
			'Receba pagamentos com praticidade e segurança via Boleto Bancário!',
			'wc-pagarme'
		);
		$this->supports           = array(
			'products',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
			'multiple_subscriptions',
		);
		$this->testmode           = $this->get_option( 'testmode' );
		$this->public_key         = $this->get_option( 'public_key' );
		$this->secret_key         = $this->get_option( 'secret_key' );
		$this->public_key_sandbox = $this->get_option( 'public_key_sandbox' );
		$this->secret_key_sandbox = $this->get_option( 'secret_key_sandbox' );
		$this->instructions       = $this->get_option( 'instructions' );
		$this->expires            = $this->get_option( 'expiration' );
		$this->debug              = $this->get_option( 'debug' ) === 'yes';

		// Enable custom form fields for this gateway.
		$this->has_fields = true;

		// Initialize the form fields and gateway settings.
		$this->init_form_fields();
		$this->init_settings();

		// Initializes the Pagar.me payment gateway.
		parent::init_gateway();
	}

	/**
	 * Initializes hooks for the payment gateway.
	 *
	 * This method registers the necessary WordPress and WooCommerce hooks
	 * required for the payment gateway's functionality. These hooks may include
	 * actions and filters that allow the gateway to integrate with the checkout process,
	 * handle payment transactions, and respond to various events during the payment lifecycle.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'order_summary_preview' ) );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_link_to_boleto' ), 10, 2 );
	}

	/**
	 * Defines and initializes the form fields for the payment gateway's admin settings.
	 *
	 * This method sets up the form fields that appear on the plugin's admin settings page.
	 * These fields allow administrators to configure various options for the payment gateway,
	 * such as API keys, titles, descriptions, and other settings specific to the gateway.
	 * The fields are used to capture and save configuration data in the WordPress admin area.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$fields = array(
			'enabled'            => array(
				'title'       => __( 'Ativar/Desativar', 'wc-pagarme' ),
				'label'       => __(
					'Marque para habilitar esta forma de pagamento.',
					'wc-pagarme'
				),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'              => array(
				'title'       => __( 'Título do Checkout', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Este campo controla o título que o usuário vê durante o checkout.',
					'wc-pagarme'
				),
				'default'     => 'Boleto',
				'desc_tip'    => true,
			),
			'description'        => array(
				'title'       => __( 'Descrição do Checkout', 'wc-pagarme' ),
				'type'        => 'textarea',
				'description' => __(
					'Este campo controla a descrição que o usuário vê durante o checkout.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'default'     => __(
					'Finalize sua compra de forma rápida e segura utilizando Boleto!',
					'wc-pagarme'
				),
			),
			'environment'        => array(
				'title'       => __( 'Configurações de Integração', 'wc-pagarme' ),
				'type'        => 'title',
				'description' => __(
					'Selecione o ambiente ativo para a API',
					'wc-pagarme'
				),
			),
			'testmode'           => array(
				'title'       => __( 'Ambiente de Sandbox', 'wc-pagarme' ),
				'type'        => 'checkbox',
				'label'       => __( 'Habilitar o Teste da Pagar.me', 'wc-pagarme' ),
				'description' => __(
					'O Sandbox da Pagar.me pode ser utilizado para testar os pagamentos',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'default'     => 'no',
			),
			'public_key'         => array(
				'title'       => __( 'Chave Pública', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __( 'Chave Pública da Pagar.me', 'wc-pagarme' ),
				'desc_tip'    => true,
			),
			'secret_key'         => array(
				'title'       => __( 'Chave Secreta', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __( 'Chave Secreta da Pagar.me', 'wc-pagarme' ),
				'desc_tip'    => true,
			),
			'public_key_sandbox' => array(
				'title'       => __( 'Chave Pública do Sandbox', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Chave Pública da Pagar.me para Sandbox',
					'wc-pagarme'
				),
				'desc_tip'    => true,
			),
			'secret_key_sandbox' => array(
				'title'       => __( 'Chave Secreta de Testes', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Chave Secreta da Pagar.me para Testes',
					'wc-pagarme'
				),
				'desc_tip'    => true,
			),
			'payment_settings'   => array(
				'title'       => __( 'Configurações de Pagamento', 'wc-pagarme' ),
				'type'        => 'title',
				'description' => __(
					'Personalize as opções de pagamento',
					'wc-pagarme'
				),
			),
			'instructions'       => array(
				'title'       => __( 'Instruções do boleto', 'wc-pagarme' ),
				'type'        => 'textarea',
				'description' => __(
					'Isso estará impresso no boleto.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'default'     => '',
			),
			'expiration'         => array(
				'title'       => __( 'Dias para Vencimento', 'wc-pagarme' ),
				'description' => __( 'É o número de dias para o vencimento do boleto. Por padrão 2 dias', 'wc-pagarme' ),
				'default'     => '2',
			),
			'debug'              => array(
				'title'       => __( 'Log de Depuração', 'wc-pagarme' ),
				'type'        => 'checkbox',
				'label'       => __( 'Habilitar Registro de Erros', 'wc-pagarme' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Registre eventos da Pagar.me, como solicitações de API. Você pode verificar o log em %s', 'wc-pagarme' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'Status do sistema &gt; Logs', 'wc-pagarme' ) . '</a>' ),
			),
			'webhook'            => array(
				'title'       => __( 'Configurações de Webhook', 'wc-pagarme' ),
				'type'        => 'title',
				'description' => sprintf( __( 'Realize as configurações do webhook. Você só precisa configurá-lo uma vez no dashbboard pagar.me. <br> O webhook permite que a loja receba notificações para atualizar pedidos automaticamente. <a href="%1$s">Saiba mais aqui</a>. <br>Crie um novo webhook e coloque <code>%2$s</code> no campo URL. Por fim, selecione todos os eventos. ', 'wc-pagarme' ), 'https://pagarme.helpjuice.com/pt_BR/p2-funcionalidades/configura%C3%A7%C3%B5es-como-configurar-webhooks', esc_url( WC()->api_request_url( 'wc_pagarme_webhook' ) ) ),

			),
		);

		$this->form_fields = apply_filters( 'wc_pagarme_gateway_form_fields', $fields, $this );
	}

	/**
	 * Merge payload method data with transaction data.
	 *
	 * @param mixed  $the_order  Woocommerce Order ID or Object WC_Order.
	 *
	 * @return array
	 */
	public function build_payload_data( $the_order ) {
		// Get order data.
		$order = wc_get_order( $the_order );
		// Merge boleto settings.
		$payload = array(
			'payments' => array(
				0 => array(
					'payment_method' => 'boleto',
					'boleto'         => array(
						'instructions' => $this->instructions,
						'due_at'       => wc_pagarme_add_days_to_date(
							$this->expires
						),
					),
				),
			),
			'items'    => array(
				array(
					'quantity'    => 1,
					'code'        => $order->get_id(),
					'amount'      => (int) round( $order->get_total() * 100, 0 ),
					'description' => sprintf(
						__( 'WooCommerce ordem #%1$s. Total: %2$s', 'wc-pagarme' ),
						$order->get_id(),
						$order->get_total()
					),
				),
			),
		);

		return $payload;
	}

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
	public function order_summary_preview( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_meta( '_pagarme_boleto_url' ) && in_array( $order->get_status(), array( 'pending', 'on-hold' ), true ) ) {
			?>
			<div class="woocommerce-message">
				<span><a class="button" href="<?php echo esc_url( $order->get_meta( '_pagarme_boleto_url' ) ); ?>" target="_blank"><?php esc_html_e( 'IMPRIMIR BOLETO', 'wc-pagarme' ); ?></a><?php esc_html_e( 'Você pode imprimir e pagar via internet banking ou em uma casa lotérica.', 'wc-pagarme' ); ?><br /><?php esc_html_e( 'Após recebermos a confirmação do pagamento do boleto bancário, seu pedido será processado.', 'wc-pagarme' ); ?></span>
			</div>
			<div>
				<p> <iframe src="<?php echo esc_url( $order->get_meta( '_pagarme_boleto_url' ) ); ?>" style="width:100%; height:1000px;border: solid 1px #eee;"></iframe> </p>
			</div>
			<br>
			<?php
		}
	}

	/**
	 * Add boleto link/button in My Orders section on My Accout page.
	 *
	 * @param array    $actions Actions.
	 * @param WC_Order $order   Order data.
	 *
	 * @return array
	 */
	public function add_link_to_boleto( $actions, $order ) {
		if ( $this->id !== $order->payment_method ) {
			return $actions;
		}

		if ( ! in_array( $order->get_status(), array( 'pending', 'on-hold' ), true ) ) {
			return $actions;
		}

		if ( ! empty( $order->get_meta( '_pagarme_boleto_url' ) ) ) {
			$actions[] = array(
				'url'  => $order->get_meta( '_pagarme_boleto_url' ),
				'name' => __( 'Imprimir Boleto', 'wc-pagarme' ),
			);
		}

		return $actions;
	}

	/**
	 * Register admin_enqueue styles and scripts for payment method
	 *
	 * @since    1.0.0
	 * @return   array    void
	 */
	public function admin_enqueue() {
		wp_enqueue_script(
			'wc-pagarme-boleto-settings',
			WC_PAGARME_URI . 'assets/js/admin/boleto-settings.js',
			array( 'jquery' ),
			WC_PAGARME_VERSION,
			true
		);
	}
}