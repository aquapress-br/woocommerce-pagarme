<?php defined( 'ABSPATH' ) ?? exit();

/**
 * WC_Pagarme_Credit_Card_Gateway class.
 *
 * @extends WC_Pagarme_Abstract_Gateway
 */
class WC_Pagarme_Gateway_CreditCard2 extends WC_Pagarme_Abstract_Gateway {

	const CARD_ID                  = 'pagarme_card';
	const CARD_NUMBER              = 'pagarme_card_number';
	const CARD_NAME                = 'pagarme_card_holder_name';
	const CARD_EXPIRY              = 'pagarme_card_expiry';
	const CARD_CVC                 = 'pagarme_card_cvc';
	const CARD_INSTALLMENTS        = 'pagarme_card_installments';
	const CARD_CHECK_SAVE          = 'pagarme_card_check_save';
	const CARD_TOKEN               = 'pagarmetoken';
	const FILTER_CARD_INSTALLMENTS = 'wc_pagarme_card_installments';
	const FILTER_MAX_INSTALLMENTS  = 'wc_pagarme_max_installments';
	const FILTER_CARD_DATA         = 'wc_pagarme_card_data';

	/**
	 * Start payment method.
	 *
	 * @return   void
	 */
	public function __construct() {
		$this->id                   = 'wc_pagarme_creditcard';
		$this->method_title         = __( 'Pagar.me', 'wc-pagarme' );
		$this->method_description   = __(
			'Receba com Cartão de Crédito usando Pagar.me.',
			'wc-pagarme'
		);
		$this->supports             = array(
			'tokenization',
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
		$this->testmode             = $this->get_option( 'testmode' );
		$this->public_key           = $this->get_option( 'public_key' );
		$this->secret_key           = $this->get_option( 'secret_key' );
		$this->public_key_sandbox   = $this->get_option( 'public_key_sandbox' );
		$this->secret_key_sandbox   = $this->get_option( 'secret_key_sandbox' );
		$this->methods              = $this->get_option( 'methods' );
		$this->smallest_installment = $this->get_option( 'smallest_installment' );
		$this->interest_rate        = $this->get_option( 'interest_rate' );
		$this->installments         = $this->get_option( 'installments' );
		$this->interest             = $this->get_option( 'interest' );
		$this->installment_type     = $this->get_option( 'installment_type' );
		$this->statement_descriptor = $this->get_option( 'statement_descriptor' );
		$this->operation_type       = $this->get_option( 'operation_type' );
		$this->collect_card         = $this->get_option( 'collect_card' );
		$this->debug                = $this->get_option( 'debug' );
		$this->icon                 = null;

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
		add_filter(
			static::FILTER_MAX_INSTALLMENTS,
			array( $this, 'remove_subscriptions_installments' ),
			50
		);
		add_action(
			'woocommerce_scheduled_subscription_payment_' . $this->id,
			array( $this, 'process_subscription' ),
			10,
			2
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue' ) );

		add_action(
			'wc_pagarme_saved_customer_data',
			array(
				$this,
				'save_customer_id',
			)
		);
		add_action(
			'wc_pagarme_saved_card_data',
			array(
				$this,
				'save_payment_method',
			)
		);

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);

		add_action( 'woocommerce_api_' . $this->id, array( $this, 'webhook_handler' ) );
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
			'enabled'              => array(
				'title'       => __( 'Ativar/Desativar', 'wc-pagarme' ),
				'label'       => __(
					'Marque para habilitar esta forma de pagamento.',
					'wc-pagarme'
				),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'                => array(
				'title'       => __( 'Título do Checkout', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Este campo controla o título que o usuário vê durante o checkout.',
					'wc-pagarme'
				),
				'default'     => 'Cartão de Crédito',
				'desc_tip'    => true,
			),
			'description'          => array(
				'title'       => __( 'Descrição do Checkout', 'wc-pagarme' ),
				'type'        => 'textarea',
				'description' => __(
					'Este campo controla a descrição que o usuário vê durante o checkout.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'default'     => __(
					'Insira os detalhes do cartão de crédito',
					'wc-pagarme'
				),
			),
			'environment'          => array(
				'title'       => __( 'Configurações de Integração', 'wc-pagarme' ),
				'type'        => 'title',
				'description' => __(
					'Selecione o ambiente ativo para a API',
					'wc-pagarme'
				),
			),
			'testmode'             => array(
				'title'       => __( 'Ambiente de Sandbox', 'wc-pagarme' ),
				'type'        => 'checkbox',
				'label'       => __( 'Habilitar o Teste do Pagar.me', 'wc-pagarme' ),
				'description' => __(
					'O Sandbox do Pagar.me pode ser utilizado para testar os pagamentos',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'default'     => 'no',
			),
			'public_key'           => array(
				'title'       => __( 'Chave Pública', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __( 'Chave Pública do Pagar.me', 'wc-pagarme' ),
				'desc_tip'    => true,
			),
			'secret_key'           => array(
				'title'       => __( 'Chave Secreta', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __( 'Chave Secreta do Pagar.me', 'wc-pagarme' ),
				'desc_tip'    => true,
			),
			'public_key_sandbox'   => array(
				'title'       => __( 'Chave Pública do Sandbox', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Chave Pública do Pagar.me para Sandbox',
					'wc-pagarme'
				),
				'desc_tip'    => true,
			),
			'secret_key_sandbox'   => array(
				'title'       => __( 'Chave Secreta do Sandbox', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Chave Secreta do Pagar.me para Sandbox',
					'wc-pagarme'
				),
				'desc_tip'    => true,
			),
			'payment_settings'     => array(
				'title'       => __( 'Configurações de Pagamento', 'wc-pagarme' ),
				'type'        => 'title',
				'description' => __(
					'Personalize as opções de pagamento',
					'wc-pagarme'
				),
			),
			'statement_descriptor' => array(
				'title'             => __(
					'Texto no Extrato do Cartão de Crédito',
					'wc-pagarme'
				),
				'type'              => 'text',
				'description'       => __(
					'Texto a ser exibido no extrato do cartão de crédito',
					'wc-pagarme'
				),
				'desc_tip'          => true,
				'default'           => __( 'Compra online', 'wc-pagarme' ),
				'custom_attributes' => array( 'maxlength' => '13' ),
			),
			'smallest_installment' => array(
				'title'       => __( 'Menor Parcela', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Valor mínimo de cada parcela, não pode ser inferior a 1.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'default'     => '1',
			),
			'installments'         => array(
				'title'       => __( 'Parcelamento', 'wc-pagarme' ),
				'type'        => 'select',
				'description' => __(
					'Número máximo de parcelas para pedidos na sua loja.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => '1',
				'options'     => array(
					'1'  => '1x',
					'2'  => '2x',
					'3'  => '3x',
					'4'  => '4x',
					'5'  => '5x',
					'6'  => '6x',
					'7'  => '7x',
					'8'  => '8x',
					'9'  => '9x',
					'10' => '10x',
					'11' => '11x',
					'12' => '12x',
				),
			),
			'methods'              => array(
				'title'       => __( 'Bandeiras Aceitas', 'wc-pagarme' ),
				'type'        => 'multiselect',
				'description' => __(
					'Selecione as bandeiras de cartão que serão aceitas como pagamento. Pressione a tecla Ctrl para selecionar mais de uma bandeira.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => array(
					'visa',
					'mastercard',
					'diners',
					'discover',
					'elo',
					'amex',
					'jcb',
					'aura',
				),
				'options'     => array(
					'visa'       => __( 'Visa', 'wc-pagarme' ),
					'mastercard' => __( 'MasterCard', 'wc-pagarme' ),
					'diners'     => __( 'Diners', 'wc-pagarme' ),
					'discover'   => __( 'Discover', 'wc-pagarme' ),
					'elo'        => __( 'Elo', 'wc-pagarme' ),
					'amex'       => __( 'American Express', 'wc-pagarme' ),
					'jcb'        => __( 'JCB', 'wc-pagarme' ),
					'aura'       => __( 'Aura', 'wc-pagarme' ),
				),
			),
			'installment_type'     => array(
				'title'       => __( 'Tipo de Parcelamento', 'wc-pagarme' ),
				'type'        => 'select',
				'description' => __(
					'O cliente adiciona juros às parcelas no valor total do pedido.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => 'store',
				'options'     => array(
					'client' => __( 'Cliente', 'wc-pagarme' ),
					'store'  => __( 'Loja', 'wc-pagarme' ),
				),
			),
			'interest_rate'        => array(
				'title'       => __( 'Taxa de Juros (%)', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __(
					'Percentual de juros que será cobrado do cliente na parcela em que houver aplicação de juros.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'default'     => '2',
			),
			'interest'             => array(
				'title'       => __( 'Cobrar Juros A Partir De', 'wc-pagarme' ),
				'type'        => 'select',
				'description' => __(
					'Indique a partir de qual parcela será cobrado juros.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => '6',
				'options'     => array(
					'1'  => '1x',
					'2'  => '2x',
					'3'  => '3x',
					'4'  => '4x',
					'5'  => '5x',
					'6'  => '6x',
					'7'  => '7x',
					'8'  => '8x',
					'9'  => '9x',
					'10' => '10x',
					'11' => '11x',
					'12' => '12x',
				),
			),
			'operation_type'       => array(
				'title'       => __( 'Tipo de Captura', 'wc-pagarme' ),
				'type'        => 'select',
				'description' => __(
					'Indique o tipo de captura de pagamento.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => 'auth_and_capture',
				'options'     => array(
					'auth_and_capture' => __(
						'Autorização e Captura',
						'wc-pagarme'
					),
					'pre_auth'         => __( 'Pré-autorização', 'wc-pagarme' ),
				),
			),
			'collect_card'         => array(
				'title'       => __( 'Coletar Dados do Cartão', 'wc-pagarme' ),
				'type'        => 'select',
				'description' => __(
					'Controla como os dados do cartão devem ser manuseados.',
					'wc-pagarme'
				),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => 'ask_before_saving',
				'options'     => array(
					'ask_before_saving'   => __(
						'Perguntar Antes de Salvar',
						'wc-pagarme'
					),
					'save_without_asking' => __(
						'Salvar Sem Perguntar',
						'wc-pagarme'
					),
					'never_save'          => __( 'Nunca Salvar', 'wc-pagarme' ),
				),
			),
		);

		$this->form_fields = $fields;
	}

	/**
	 * Processes the payment for the specified order.
	 *
	 * This method handles the payment process after a payment request has been sent
	 * during checkout. It uses the provided `$order_id` to retrieve order details and
	 * complete the payment transaction. The method typically involves interacting with
	 * the payment gateway to process the payment and may return an array containing the
	 * result of the payment operation, such as the payment status and any relevant messages.
	 *
	 * @param string $order_id The ID of the order being processed.
	 *
	 * @return array An array containing the result of the payment processing, which may include
	 *               payment status, redirect URLs, or error messages.
	 */
	public function process_payment( $order_id ) {
		$order         = wc_get_order( $order_id );
		$order_data    = $this->get_order_data( $order_id, $order );
		$card_data     = $this->get_creditcard_data( $_POST, $order );
		$customer_data = $this->get_customer_data( $order );

		$this->debug( 'Payment process log for order ID:' . $order_id );
		$this->debug(
			'Payment process get transaction order data:' .
				var_export( $order_data, true )
		);
		$this->debug(
			'Payment process get transaction card data:' .
				var_export( $card_data, true )
		);
		$this->debug(
			'Payment process get transaction customer data:' .
				var_export( $customer_data, true )
		);

		// Save or update customer in pagar.me
		try {
			$customer = new WC_Pagarme_Model_Customer();
			$customer->set_email( $customer_data['email'] );
			$customer->set_code( $customer_data['id'] );

			if ( $customer['person_type'] == '2' ) {
				$customer->set_type( 'individual' );
				$customer->set_document_type( 'CNPJ' );
				$customer->set_document( $customer_data['cnpj'] );
				$customer->set_name( $customer_data['company_name'] );
			} else {
				$customer->set_type( 'individual' );
				$customer->set_document_type( 'CPF' );
				$customer->set_document( $customer_data['cpf'] );
				$customer->set_name( $customer_data['full_name'] );
			}

			if (
				$customer_id = $this->api->do_save_customer(
					$customer->container
				)
			) {
				do_action(
					'wc_pagarme_saved_customer_data',
					$customer_id,
					$customer
				);
			} else {
				throw new Exception(
					__(
						'There was an error validating your payment details. Please try again. If the problem persists, contact the site administrator. #1010',
						'wc-pagarme'
					)
				);
			}
		} catch ( Exception $e ) {
			Wc_Pagarme_Helper::add_checkout_notice( $e->getMessage(), 'error' );

			return array(
				'result' => 'fail',
			);
		}

		// Process transation
		try {
			$card = new WC_Pagarme_Model_Card();
			if ( $card_data['card_id'] != '' ) {
				$card->set_card_id( $card_data['card_id'] );
			} else {
				$card->set_number( $card_data['card_number'] );
				$card->set_holder_name( $card_data['card_holder'] );
				$card->set_brand( $card_data['card_brand'] );
				$card->set_exp_month(
					substr( $card_data['card_expiration'], 0, 2 )
				);
				$card->set_exp_year( substr( $card_data['card_expiration'], -2 ) );
				$card->set_cvv( $card_data['card_cvv'] );

				$card->set_billing_address_country(
					$customer_data['address_country']
				);
				$card->set_billing_address_state(
					$customer_data['address_state']
				);
				$card->set_billing_address_city( $customer_data['address_city'] );
				$card->set_billing_address_zip_code(
					$customer_data['address_zipcode']
				);
				$card->set_billing_address_line_1(
					$customer_data['address_street'] .
						' N ' .
						$customer_data['address_number'] .
						' - ' .
						$customer_data['address_district']
				);
				$card->set_billing_address_line_2(
					$customer_data['address_complement']
				);

				$card->set_billing_address_street(
					$customer_data['address_street'] .
						' N ' .
						$customer_data['address_number'] .
						' - ' .
						$customer_data['address_district']
				);
				$card->set_billing_address_number(
					$customer_data['address_number']
				);
				$card->set_billing_address_neighborhood(
					$customer_data['address_district']
				);
				$card->set_billing_address_complement(
					$customer_data['address_complement']
				);
			}

			$transaction = new WC_Pagarme_Model_Transaction();
			$transaction->set_customer_id( $customer_id );
			$transaction->add_item(
				$card['card_order_total'] * 100,
				sprintf( __( 'Pedido WooCommerce #%1$s.', 'wc-pagarme' ), 1 )
			);
			$transaction->add_credit_card_payment(
				$card['card_installments'],
				$this->operation_type,
				$this->statement_descriptor,
				$card
			);

			$result = $this->api->do_transaction(
				apply_filters(
					'wc_pagarme_transaction_data',
					$transaction->container,
					$order
				)
			);

			if ( isset( $data['errors'] ) || isset( $data['message'] ) ) {
				return array(
					'result' => 'fail',
				);
			}

			$this->save_order_meta_fields( $order_id, $result );
			$this->process_order_status( $order, $result['status'] );
		} catch ( Exception $e ) {
			Wc_Pagarme_Helper::add_checkout_notice( $e->getMessage(), 'error' );

			return array(
				'result' => 'fail',
			);
		}

		if ( $result['status'] != 'failed' ) {
			// Save card data.
			if (
				$card['card_id'] == '' &&
				( $this->collect_card == 'save_without_asking' ||
					$card_data['card_check_save'] )
			) {
				$this->add_payment_method();
			}

			// Redirect to thanks page.
			return array(
				'result'   => 'success',
				'redirect' => $order_data['return_url'],
			);
		}

		Wc_Pagarme_Helper::add_checkout_notice(
			__(
				'We were unable to process payment with the card provided. Check the information provided and try again. If the problem persists, contact the issuing bank for more information.',
				'wc-pagarme'
			),
			'error'
		);

		return array(
			'result' => 'fail',
		);
	}

	/**
	 * Process payment method.
	 *
	 * @return   void
	 */
	public function add_payment_method() {
		$user        = wp_get_current_user();
		$customer_id = get_user_meta(
			$user->ID,
			'_wc_pagarme_customer_id',
			true
		);
		$card_data   = $this->get_creditcard_data( $_POST );

		$this->debug(
			'Save payment method get card data:' . var_export( $card_data, true )
		);

		try {
			$card_payload = array(
				'billing_address' => array(
					'street'       => get_user_meta(
						$user->ID,
						'billing_address_1',
						true
					),
					'number'       => get_user_meta(
						$user->ID,
						'billing_number',
						true
					),
					'zip_code'     => get_user_meta(
						$user->ID,
						'billing_postcode',
						true
					),
					'neighborhood' => get_user_meta(
						$user->ID,
						'billing_neighborhood',
						true
					),
					'city'         => get_user_meta( $user->ID, 'billing_city', true ),
					'state'        => get_user_meta( $user->ID, 'billing_state', true ),
					'country'      => get_user_meta(
						$user->ID,
						'billing_country',
						true
					),
					'line_1'       =>
						get_user_meta( $user->ID, 'billing_address_1', true ) .
						' N ' .
						get_user_meta( $user->ID, 'billing_number', true ) .
						' - ' .
						get_user_meta( $user->ID, 'billing_neighborhood', true ),
					'line_2'       => get_user_meta(
						$user->ID,
						'billing_address_2',
						true
					),
				),
			);

			if ( ! $customer_id ) {
				$customer_payload = array(
					'code'  => $user->ID,
					'email' => $user->user_email,
					'name'  => $user->first_name . ' ' . $user->last_name,
				);

				if (
					$customer_id = $this->api->do_save_customer(
						$customer_payload
					)
				) {
					$card_payload['customer_id'] = $customer_id;
				} else {
					throw new Exception(
						__(
							'There was an error validating your payment details. Please try again. If the problem persists, contact the site administrator.',
							'wc-pagarme'
						)
					);
				}
			} else {
				$card_payload['customer_id'] = $customer_id;
			}

			if ( $card_data['card_token'] != '' ) {
				$card_payload['token'] = $card_data['card_token'];
			} else {
				$card_payload = array_merge(
					$card_payload,
					array(
						'number'      => $card_data['card_number'],
						'brand'       => $card_data['card_brand'],
						'holder_name' => $card_data['card_holder'],
						'exp_month'   => substr( $card_data['card_expiration'], 0, 2 ),
						'exp_year'    => substr( $card_data['card_expiration'], -2 ),
						'cvv'         => $card_data['card_cvv'],
					)
				);
			}

			if ( ! $this->api->do_save_card( $card_payload ) ) {
				throw new Exception(
					__(
						'There was an error validating your payment details. Please try again. If the problem persists, contact the site administrator.',
						'wc-pagarme'
					)
				);
			}

			Wc_Pagarme_Helper::add_checkout_notice(
				__( 'Card successfully added.', 'wc-pagarme' ),
				'success'
			);
		} catch ( Exception $e ) {
			Wc_Pagarme_Helper::add_checkout_notice( $e->getMessage(), 'error' );
		}
	}

	/**
	 * Save payment method.
	 *
	 * @return   void
	 */
	public function save_payment_method( $data ) {
		$token = new WC_Payment_Token_CC();

		$token->set_gateway_id( $this->id );
		$token->set_token( $data['id'] ); // Pagar.me card ID

		$token->set_last4( $data['last_four_digits'] );
		$token->set_expiry_year( $data['exp_year'] );
		$token->set_expiry_month( $data['exp_month'] );

		$token->set_card_type( $data['brand'] );
		$token->set_user_id( get_current_user_id() );

		// Save the new token to the database
		$token->save();
	}

	/**
	 * Save customer id.
	 *
	 * @return   void
	 */
	public function save_customer_id( $data ) {
		update_user_meta( $data['code'], '_wc_pagarme_customer_id', $data['id'] );
	}

	/**
	 * Retrieves the checkout form fields for the payment gateway.
	 *
	 * This method generates and returns the necessary form fields that will be displayed
	 * during the checkout process. These fields are used to capture additional information
	 * required for processing payments. The `$order_total` parameter is used to customize
	 * the form fields based on the total amount of the current order.
	 *
	 * @param float $order_total The total amount of the order, used to customize the form fields.
	 *
	 * @return string HTML markup for the checkout form fields.
	 */
	protected function get_checkout_form( $order_total ) {
		wc_get_template(
			'payment-form.php',
			array(
				'card_id'         => static::CARD_ID,
				'card_number'     => static::CARD_NUMBER,
				'card_name'       => static::CARD_NAME,
				'card_expiry'     => static::CARD_EXPIRY,
				'card_cvc'        => static::CARD_CVC,
				'card_check_save' => static::CARD_CHECK_SAVE,

				'collect_card'    => $this->collect_card,
				'methods'         => parent::get_available_methods_options(),
				'installments'    => parent::get_installments_html( $order_total ),
				'cards_save'      => WC_Payment_Tokens::get_customer_tokens(
					get_current_user_id(),
					$this->id
				),
				'is_checkout'     => is_checkout(),
			),
			'woocommerce/pagarme/',
			WC_PAGARME_PATH . 'templates/woocommerce'
		);
	}

	/**
	 * Register admin styles and scripts for payment method
	 *
	 * @since    1.0.0
	 * @return   array    void
	 */
	public function admin_enqueue() {
		wp_enqueue_script(
			'wc-pagarme-method-creditcard-admin',
			WC_PAGARME_URI . 'assets/admin/js/creditcard.js'
		);
	}

	/**
	 * Register public styles and scripts for payment method
	 *
	 * @since    1.0.0
	 * @return   array    void
	 */
	public function public_enqueue() {
		wp_enqueue_script( 'wc-credit-card-form' );
		wp_enqueue_script(
			'wc-pagarme-card-fields',
			WC_PAGARME_URI . 'assets/js/checkout/card-fields.js',
			array( 'jquery', 'jquery-blockui' ),
			WC_PAGARME_VERSION,
			true
		);
		wp_enqueue_script(
			'wc-pagarme-card-form',
			WC_PAGARME_URI . 'assets/js/checkout/card-form.js',
			array( 'jquery', 'jquery-blockui' ),
			WC_PAGARME_VERSION,
			true
		);
		wp_enqueue_script(
			'jquery-mask',
			WC_PAGARME_URI . 'assets/js/jquery.mask.js',
			array( 'jquery' ),
			'1.14.10',
			true
		);
		wp_enqueue_style(
			'wc-pagarme-card-form',
			WC_PAGARME_URI . 'assets/public/css/card-form.css'
		);
	}

	/**
	 * Validates the checkout form fields.
	 *
	 * This method checks whether the required billing information, specifically the country
	 * and tax identification number (VAT), is properly provided during checkout. If the
	 * billing country is not Brazil (`BR`), it ensures that the taxpayer identification
	 * (`billing_taxvat`) field is filled. If not, an exception is thrown, prompting the user
	 * to provide the necessary details.
	 *
	 * @throws Exception If the taxpayer identification number is empty for non-Brazilian customers.
	 *
	 * @return bool  True if the validation passes, otherwise throws an exception.
	 */
	public function validate_fields() {
		if ( defined( 'STATIC::CARD_ID' ) ) {
			try {
				$this->validate_card_fields();
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}
		}
	}
}
