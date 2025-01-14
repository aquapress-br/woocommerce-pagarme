<?php

namespace Aquapress\Pagarme\Gateways;

/**
 * Process payment with PIX.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Gateways\PIX class.
 *
 * @extends \Aquapress\Pagarme\Abstracts\Gateway.
 */
class PIX extends \Aquapress\Pagarme\Abstracts\Gateway {

	/**
	 * Start payment method.
	 *
	 * @return   void
	 */
	public function __construct() {
		$this->id                 = 'wc_pagarme_pix';
		$this->method_title       = __( 'Pagar.me', 'wc-pagarme' );
		$this->method_description = __(
			'Receba pagamentos com PIX usando a Pagar.me, com confirmação instantânea, tarifas competitivas.',
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
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_enqueue' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'order_summary_preview' ) );
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
				'default'     => 'PIX',
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
					'Finalize sua compra de forma rápida e segura utilizando PIX!',
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
			'expiration'         => array(
				'title'       => __( 'Minutos para Expiração', 'wc-pagarme' ),
				'description' => __( 'É o número de minutos para o Pix expirar. Por padrão um dia, ou seja, 1440 minutos', 'wc-pagarme' ),
				'default'     => '1440',
			),
			'debug'              => array(
				'title'       => __( 'Log de Depuração', 'wc-pagarme' ),
				'type'        => 'checkbox',
				'label'       => __( 'Habilitar Registro de Erros', 'wc-pagarme' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Registre eventos da Pagar.me, como solicitações de API. Você pode verificar o log em %s', 'wc-pagarme' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'Status do sistema &gt; Logs', 'wc-pagarme' ) . '</a>' ),
			),
			'webhook'              => array(
				'title'       => __( 'Configurações de Webhook', 'wc-pagarme' ),
				'type'        => 'title',
				'description' =>  sprintf( __( 'Realize as configurações do webhook. Você só precisa configurá-lo uma vez no dashbboard pagar.me. <br> O webhook permite que a loja receba notificações para atualizar pedidos automaticamente. <a href="%s">Saiba mais aqui</a>. <br>Crie um novo webhook e coloque <code>%s</code> no campo URL. Por fim, selecione todos os eventos. ', 'wc-pagarme'), 'https://pagarme.helpjuice.com/pt_BR/p2-funcionalidades/configura%C3%A7%C3%B5es-como-configurar-webhooks', esc_url( WC()->api_request_url( 'wc_pagarme_webhook' ) ) ),

			),
		);

		$this->form_fields = apply_filters( 'wc_pagarme_gateway_form_fields', $fields, $this->id );
	}

	/**
	 * Merge payload method data with transaction data.
	 *
	 * @param mixed  $the_order    Woocommerce Order ID or Object WC_Order.
	 *
	 * @return array
	 */
	public function build_payload_data( $the_order ) {
		// Get order data.
		$order = wc_get_order( $the_order );
		// Merge pix settings.
		$payload = array(
			'payments' => array(
				0 => array(
					'payment_method' => 'pix',
					'Pix'            => array(
						'expires_in' => (int) $this->expires,
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
	 * Add a pix code view in order summary.
	 *
	 * @param int $order_id Order ID.
	 */
	public function order_summary_preview( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_meta( 'PAGARME_PIX_QRCODE_URL' ) && $order->get_meta( 'PAGARME_PIX_QRCODE' ) && in_array( $order->get_status(), array( 'pending', 'on-hold' ) ) ) {
			?>
			<div class="pagarme-pix-instructions-container">
				<h4 class="pagarme-pix-instructions-title"><?php esc_html_e( 'Escanei e pague o QR code a seguir para efeturar a compra do seu pedido', 'wc-pagarme' ); ?></h4>
				<br>
				<ul>
					<li><?php esc_html_e( '1 - Abra o aplicativo do seu banco no seu telefone', 'wc-pagarme' ); ?></li>
					<li><?php esc_html_e( '2 - Selecione a opção Pagar com PIX', 'wc-pagarme' ); ?></li>
					<li><?php esc_html_e( '3 - Após o pagamento você receberá um e-mail de confirmação', 'wc-pagarme' ); ?></li>
				</ul>
				<img width="150" height="150" src="<?php echo esc_url( $order->get_meta( 'PAGARME_PIX_QRCODE_URL' ) ); ?>"/>
				<br>
				<span><a class="button" data-qrcode="<?php esc_html_e( $order->get_meta( 'PAGARME_PIX_QRCODE' ) ); ?>" id="pagarme-copy-button" href="javascript:void(0);"><span class="copy-clipboard"><?php esc_html_e( 'Copiar chave PIX', 'wc-pagarme' ); ?></span><span class="copied-successful"><?php esc_html_e( 'Chave PIX copiada!', 'wc-pagarme' ); ?></span></a></span>				
				<br>
			</div>
			<?php
		}
	}

	/**
	 * The payment confirmation screen is displayed seconds after payment automatically.
	 *
	 * @since    1.0.0
	 * @return   array    void
	 */
	public function check_payment_complete() {
		if ( isset( $_REQUEST['order_id'] ) ) {
			// Get the order object.
			$order = wc_get_order( $_REQUEST['order_id'] );
			if ( ! $order ) {
				wp_send_json( false ); // Order not found.
			}
			if ( $order->get_customer_id() != get_current_user_id() ) {
				wp_send_json( false ); // Access fail.
			}
			// Check if the order payment status is completed or processing.
			if ( in_array( $order->get_status(), array( 'completed', 'processing' ) ) ) {
				wp_send_json( true );
			}
		}
		wp_die();
	}

	/**
	 * Register admin_enqueue styles and scripts for payment method
	 *
	 * @since    1.0.0
	 * @return   array    void
	 */
	public function admin_enqueue() {
		wp_enqueue_script(
			'wc-pagarme-pix-settings',
			WC_PAGARME_URI . 'assets/js/admin/pix-settings.js',
			array( 'jquery' ),
			WC_PAGARME_VERSION,
			true
		);
	}

	/**
	 * Register public styles and scripts for payment method
	 *
	 * @since    1.0.0
	 * @return   array    void
	 */
	public function checkout_enqueue() {
		if ( is_checkout() ) {
			wp_enqueue_script(
				'wc-pagarme-pix-instructions',
				WC_PAGARME_URI . 'assets/js/checkout/pix-instructions.js',
				array( 'jquery' ),
				WC_PAGARME_VERSION,
				true
			);
			wp_enqueue_style(
				'wc-pagarme-pix-instructions',
				WC_PAGARME_URI . 'assets/css/checkout/pix-instructions.css'
			);
		}
	}
}
