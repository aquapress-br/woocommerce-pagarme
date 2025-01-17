<?php

namespace Aquapress\Pagarme\Abstracts;

/**
 * Abstract class that will be inherited by all marketplace connectors.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abstract class that will be inherited by all connectors.
 */
abstract class Marketplace {

	use \Aquapress\Pagarme\Traits\Order_Meta;

	/**
	 * Connector identifier.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Setting values.
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Split rules stored as an associative array.
	 *
	 * @var array
	 */
	private $split_data = array();

	/**
	 * API handler instance.
	 *
	 * This attribute stores an instance of the `Aquapress\Pagarme\API` class, which is responsible
	 * for managing communication between the Pagar.me plugin and the Pagar.me API.
	 * It handles requests such as processing payments, refunds, and retrieving transaction details
	 * from the Pagar.me platform, ensuring seamless integration with the WooCommerce store.
	 *
	 * @var Aquapress\Pagarme\API
	 */
	public \Aquapress\Pagarme\API $api;

	/**
	 * Check the requirements for running the split actions.
	 *
	 * @return boolean
	 */
	abstract public function is_available();

	/**
	 * Run child class connetor actions hooks.
	 *
	 * @return void
	 */
	abstract public function init_hooks();

	/**
	 * Build split rules for payment data.
	 *
	 * @param mixed                                  $the_order           Woocommerce Order ID or Object WC_Order.
	 * @param \Aquapress\Pagarme\Abstracts\Gateway   $context             The Pagarme gateway object.
	 *
	 * @return \Aquapress\Pagarme\Models\Split_Data Split data object.
	 */
	abstract public function split_data( $the_order, $context );

	/**
	 * Merge split rules with regular payment data.
	 *
	 * @param array                                $payload             Regular payment data.
	 * @param mixed                                $the_order           Woocommerce Order ID or Object WC_Order.
	 * @param Aquapress\Pagarme\Abstracts\Gateway  $context             The pagarme gateway object.
	 *
	 * @return array
	 */
	final public function build_split_data( $payload, $the_order, $context ) {
		if ( $this->settings['recipient_id'] ) {
			// Get split data from child class.
			$split_data = $this->split_data( $the_order, $context );
			// Merge split data in transaction payload.
			if ( is_a( $split_data, '\Aquapress\Pagarme\Models\Split_Data' ) ) {
				if ( $split_data->get_data() ) {
					$payload['payments'][0]['split'] = $split_data->get_data();
				}
			}
		}

		return $payload;
	}

	/**
	 * Initializes the Pagar.me marketplace connector.
	 *
	 * Sets up the required components for the connector, including:
	 * - Logger initialization for debugging and event tracking.
	 * - API setup to handle communication with the Pagar.me platform.
	 * - Hook registration for WordPress integration.
	 *
	 * @return void
	 */
	public function init_connector() {
		// Initialize the options to connector settings API.
		$this->init_settings();
		// Initialize the API and set up logging and other hooks.
		$this->init_api();
		$this->init_actions();
	}

	/**
	 * Initialize and register connector action hooks.
	 *
	 * This method registers the necessary action hooks for the Pagar.me connector
	 * within the WordPress environment. It ensures that critical tasks, such as
	 * scheduling recipient updates and triggering actions for recipient management,
	 * are properly executed at the right points during the request lifecycle.
	 *
	 * - `wp`: Schedules the recipient update process.
	 * - `pagarme_recipient_update`: Triggers the actual recipient update process.
	 *
	 * Additionally, this method calls `init_hooks()` to initialize any other custom hooks
	 * for the connector in the child class.
	 *
	 * @return void
	 */
	public function init_actions() {
		// Initialize any additional hooks needed by the connector in the child class.
		$this->init_hooks();
		// Build split data in process payment.
		add_filter( 'wc_pagarme_transaction_data', array( $this, 'build_split_data' ), 10, 3 );
		// Hiden API key in gateway settings.
		add_filter( 'wc_pagarme_gateway_form_fields', array( $this, 'hide_gateway_settings_fields' ), 90 );
		// Load payment settings from marketplace options.
		add_filter( 'wc_pagarme_load_api_settings', array( $this, 'load_default_api_settings' ), 10, 2 );
	}

	/**
	 * Initialise Settings.
	 *
	 * Store all settings in a single database entry
	 * and make sure the $settings array is either the default
	 * or the settings stored in the database.
	 *
	 * @since 1.0.0
	 * @uses get_option()
	 */
	public function init_settings() {
		// Sets default values ​​for settings.
		$default_settings = array(
			'secret_key'         => '',
			'public_key'         => '',
			'secret_key_sandbox' => '',
			'public_key_sandbox' => '',
			'recipiente_id'      => '',
			'debug'              => 'no',
			'testmode'           => 'no',
		);
		// Get the settings stored in the database.
		$stored_settings = get_option( 'wc_pagarme_marketplace_settings' );
		// Merge stored settings with default values.
		$this->settings = wp_parse_args( $stored_settings, $default_settings );
	}

	/**
	 * Initializes the Pagar.me API instance.
	 *
	 * This method checks if the `$api` attribute is already set. If not, it creates a new instance
	 * of the `Aquapress\Pagarme\API` class using the provided API key and encryption key. This ensures
	 * that the plugin can communicate with the Pagar.me platform for handling transactions,
	 * refunds, and other API interactions.
	 *
	 * @return void
	 */
	public function init_api() {
		$this->api = \Aquapress\Pagarme\Helpers\Factory::Load_API( 'wc_pagarme_marketplace' );
	}

	/*
	 * Get Recipient payables from API.
	 *
	 * @return void
	 */
	function get_recipient_payables_action() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wc_pagarme_verify_action' ) ) {
			wp_send_json_error( 'Você está trapaceando?' );
		}
		$current_user_id = get_current_user_id();
		$recipient_id    = get_user_meta( $current_user_id, 'pagarme_recipient_id', true ) ?: false;

		if ( ! empty( $_POST['data']['date'] ) ) {
			try {
				// Get range based on current calendar month.
				$payment_date_since = date_create( $_POST['data']['date'], timezone_open( 'America/Sao_Paulo' ) )->modify( 'first day of this month' )->format( 'Y-m-d' );
				$payment_date_until = date_create( $_POST['data']['date'], timezone_open( 'America/Sao_Paulo' ) )->modify( 'last day of this month' )->format( 'Y-m-d' );
				// Process recipient payables request.
				$payables = $this->api->get_recipient_payables(
					array(
						'size'               => '1000',
						'recipient_id'       => $recipient_id,
						'payment_date_since' => $payment_date_since,
						'payment_date_until' => $payment_date_until,
					)
				);
			} catch ( \Exception $e ) {
				$payables = array(
					'data'   => array(),
					'paging' => array(),
				);
			}
			if ( ! empty( $payables['data'] ) ) {
				// Filter and organize payables for calendar viewing.
				$filter = static::organize_payables_by_date( $payables, $current_user_id );
				// Data to render the calendar.
				wp_send_json_success( $filter );
			}
		}

		wp_send_json_success( '{}' );
	}

	public static function organize_payables_by_date( $payables, $vendor_id ) {
		$total_amount  = 0;
		$payables_data = array();
		// Loop payables.
		foreach ( $payables['data'] as $payable ) {
			// Filter only balance entry payables.
			if ( $payable['type'] == 'credit' ) {
				// Check parent order exists.
				$parent_order = static::get_order_by_gateway_id( $payable['gateway_id'] );
				if ( ! $parent_order ) {
					continue;
				}
				// Check vendor suborder exists.
				$vendor_suborder = apply_filters( 'wc_pagarme_get_vendor_suborder', false, $vendor_id, $parent_order );
				if ( ! $vendor_suborder ) {
					continue;
				}
				// Starts key grouping results by date. The payment date is used as the key for the output array.
				$payment_date = date( 'Y-m-d', strtotime( $payable['payment_date'] ) );
				// Initialize the key with the current payable if it does not exist.
				if ( ! isset( $payables_data[ $payment_date ] ) ) {
					$payables_data[ $payment_date ] = array(
						'type' => array(
							'status' => '',
							'total'  => 0,
						),
					);
				}
				// Sets the status and increases the amount of receipts for each payable on the date.
				$payables_data[ $payment_date ]['type']['status'] = $payable['status'];
				$payables_data[ $payment_date ]['type']['total'] += ( $payable['amount'] - $payable['fee'] );
				// Returns payable details. Multiple payables on the same day are grouped here.
				$payables_data[ $payment_date ]['type']['transactions'][] = array(
					'payment_date'   => $payment_date,
					'payment_method' => $payable['payment_method'],
					'installments'   => $vendor_suborder->get_meta( '_pagarme_card_installments' ),
					'installment'    => $payable['installment'],
					'amount'         => $payable['amount'] - $payable['fee'],
					'status'         => $payable['status'],
					'gateway_id'     => $payable['gateway_id'],
					'order_id'       => $vendor_suborder->get_id(),
					'order_link'     => apply_filters( 'wc_pagarme_get_vendor_suborder_url', '#', $vendor_suborder ),
				);
				// Consolidates the total of payables not yet paid.
				if ( 'waiting_funds' == $payable['status'] ) {
					$total_amount += ( $payable['amount'] - $payable['fee'] );
				}
			}
		}
		// Returns data to render the calendar.
		return array(
			'total'        => $total_amount,
			'transactions' => $payables_data,
		);
	}

	/*
	 * Update Recipient Data
	 *
	 * @return void
	 */
	public function update_recipient_data_action( $user_id = false ) {
		$data            = array();
		$show_errors     = true;
		$current_user_id = $user_id ?: get_current_user_id();

		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'update_recipient_data' ) {
			parse_str( $_POST['data'], $postdata );
		} else {
			$show_errors = false;
			$postdata    = wp_unslash( $_REQUEST );
			if ( isset( $_REQUEST['user_id'] ) ) {
				$current_user_id = $_REQUEST['user_id'];
			}
		}

		try {
			foreach ( $postdata as $item => $value ) {
				if ( strpos( $item, 'pagarme_recipient_' ) === 0 ) {
					$field  = substr( $item, strlen( 'pagarme_recipient_' ) );
					$schema = static::form_schema();
					if ( isset( $schema[ $field ] ) ) {
						$field_value = static::schema_validation( $field, trim( $value ), $show_errors );
						if ( isset( $schema[ $field ]['sanitize_callback'] ) ) {
							$data[ $field ] = call_user_func( $schema[ $field ]['sanitize_callback'], $field_value );
						} else {
							$data[ $field ] = $field_value;
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'update_recipient_data' ) {
				wp_send_json_error( $e->getMessage() );
			} else {
				add_filter(
					'wp_redirect',
					function ( $location ) use ( $e ) {
						return add_query_arg( 'pagarme_error', base64_encode( $e->getMessage() ), $location );
					}
				);
			}
			return;
		}
		try {
			// Check recipient exists.
			$recipient_id = get_user_meta( $current_user_id, 'pagarme_recipient_id', true ) ?: false;
			// Process recipient request.
			if ( ! $recipient_id ) {
				// Process create recipient request.
				$request = $this->api->do_save_recipient( \Aquapress\Pagarme\Helpers\Payload::Build_Recipient_Payload( $data ) );
			} else {
				// Process update recipient request.
				$request = $this->api->do_update_recipient( $recipient_id, \Aquapress\Pagarme\Helpers\Payload::Build_Recipient_Payload( $data ) );
			}
			if ( isset( $request['id'], $request['status'] ) ) {
				// Update form values in WP user meta.
				foreach ( $postdata as $item => $value ) {
					if ( strpos( $item, 'pagarme_recipient_' ) === 0 ) {
						$field  = substr( $item, strlen( 'pagarme_recipient_' ) );
						$schema = static::form_schema();
						if ( isset( $schema[ (string) $field ] ) ) {
							update_user_meta( $current_user_id, $item, $value );
						}
					}
				}
				// Save base info.
				update_user_meta( $current_user_id, 'pagarme_recipient_id', $request['id'] );
				update_user_meta( $current_user_id, 'pagarme_recipient_status', $request['status'] );

				// Save bank account id for create request.
				if ( isset( $request['default_bank_account']['id'] ) ) {
					update_user_meta( $current_user_id, 'pagarme_recipient_bank_account_id', $request['default_bank_account']['id'] );
				}
				wp_send_json_success( __( 'As informações foram armazenadas com sucesso.', 'wc-pagarme' ) );
			}
		} catch ( \Exception $e ) {
			// Output error message.
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'update_recipient_data' ) {
				wp_send_json_error( __( 'Houve um erro ao processar sua requisição. Por favor, se o problema persistir, entre em contato com o administrador do site.', 'wc-pagarme' ) );
			} else {
				add_filter(
					'wp_redirect',
					function ( $location ) use ( $e ) {
						return add_query_arg( 'pagarme_error', base64_encode( __( 'Houve um erro ao processar sua requisição. Por favor, se o problema persistir, entre em contato com o administrador do site.', 'wc-pagarme' ) ), $location );
					}
				);
			}
			return;
		}
	}

	public static function schema_validation( $field, $value = '', $return_error = true ) {
		$schema = static::form_schema();

		if ( isset( $schema[ $field ] ) && true === $return_error ) {
			$valid = $schema[ $field ];

			if ( $valid['required'] && empty( $value ) ) {
				throw new \Exception(
					sprintf(
						__( '"%s" é um campo obrigatório.', 'wc-pagarme' ),
						$valid['label']
					) .
						' ' .
						$schema[ $field ]['error_msg']
				);
			}

			if ( false !== $valid['max'] && ! ( strlen( $value ) <= $valid['max'] ) ) {
				throw new \Exception(
					sprintf(
						__( 'O campo "%s" excedeu o tamanho máximo.', 'wc-pagarme' ),
						$valid['label']
					) .
						' ' .
						$schema[ $field ]['error_msg']
				);
			}

			if ( false !== $valid['min'] && ! ( strlen( $value ) >= $valid['min'] ) ) {
				throw new \Exception(
					sprintf(
						__( 'O campo "%s" não atendeu ao tamanho mínimo.', 'wc-pagarme' ),
						$valid['label']
					) .
						' ' .
						$schema[ $field ]['error_msg']
				);
			}

			if ( false !== $valid['size'] && ! ( strlen( $value ) == $valid['size'] ) ) {
				throw new \Exception(
					sprintf(
						__(
							'O campo "%s" não possui a quantidade correta de caracteres.',
							'wc-pagarme'
						),
						$valid['label']
					) .
						' ' .
						$schema[ $field ]['error_msg']
				);
			}

			if ( '' !== $value && ( 'int' == $valid['type'] && ! is_numeric( $value ) ) ) {
				throw new \Exception(
					sprintf(
						__( 'Caracteres inválidos para o campo "%s".', 'wc-pagarme' ),
						$valid['label']
					) .
						' ' .
						$schema[ $field ]['error_msg']
				);
			}

			if ( 'regex' !== $valid['type'] && false !== $valid['equal'] && ! in_array( $value, $valid['equal'] ) ) {
				throw new \Exception(
					sprintf(
						__( 'O valor do campo "%s" é inválido.', 'wc-pagarme' ),
						$valid['label']
					) .
						' ' .
						$schema[ $field ]['error_msg']
				);
			}

			if ( 'regex' == $valid['type'] && ! empty( $valid['equal'] ) ) {
				$regex_is_validate = false;
				foreach ( $valid['equal'] as $regex ) {
					if ( filter_var( $value, FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => $regex ) ) ) !== false ) {
						$regex_validate = true;
					}
				}
				if ( ! $regex_validate ) {
					throw new \Exception(
						sprintf(
							__( 'O valor do campo "%s" é inválido.', 'wc-pagarme' ),
							$valid['label']
						) .
							' ' .
							$valid['error_msg']
					);
				}
			}
		}

		return sanitize_text_field( $value );
	}

	public static function form_schema() {
		$schema = array(
			'account_type'          => array(
				'required'          => true,
				'label'             => __( 'Tipo da Conta', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => false,
				'type'              => 'string',
				'equal'             => array( 'individual', 'corporation' ),
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira valores válidos para o campo "Tipo da Conta": Pessoa Física ou Pessoa Jurídica.', 'wc-pagarme' ),
			),
			'document'              => array(
				'required'          => true,
				'label'             => __( 'Documento', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => false,
				'type'              => 'regex',
				'equal'             => array( '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/', '/^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/' ),
				'sanitize_callback' => 'wc_pagarme_only_numbers',
				'error_msg'         => __( 'Insira um valor válido para o campo “Documento”: CPF ou CNPJ.', 'wc-pagarme' ),
			),
			'full_name'             => array(
				'required'          => true,
				'label'             => __( 'Nome Completo', 'wc-pagarme' ),
				'max'               => 36,
				'min'               => 2,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira um valor válido para o campo “Nome da pessoa”. O comprimento do campo é de 2 a 36 caracteres.', 'wc-pagarme' ),
			),
			'birthdate'             => array(
				'required'          => true,
				'label'             => __( 'Data de Aniversário', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => false,
				'type'              => 'regex',
				'sanitize_callback' => 'wc_pagarme_pagarme_date_formatter',
				'equal'             => array( '/^(19|20)\d\d\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/' ),
				'error_msg'         => __( 'Insira uma data de aniversário valida.', 'wc-pagarme' ),
			),
			'occupation'            => array(
				'required'          => true,
				'label'             => __( 'Ocupação Profissional', 'wc-pagarme' ),
				'max'               => 36,
				'min'               => 2,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira uma “Ocupação Profissional” valida.', 'wc-pagarme' ),
			),
			'company_name'          => array(
				'required'          => true,
				'label'             => __( 'Nome Fantasia da Empresa', 'wc-pagarme' ),
				'max'               => 30,
				'min'               => 2,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira um valor válido para o campo “Nome Fantasia da Empresa”.', 'wc-pagarme' ),
			),
			'company_legal_name'    => array(
				'required'          => true,
				'label'             => __( 'Razão Social da Empresa', 'wc-pagarme' ),
				'max'               => 36,
				'min'               => 2,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira um valor válido para o campo “Razão Social da Empresa”.', 'wc-pagarme' ),
			),
			'annual_revenue'        => array(
				'required'          => true,
				'label'             => __( 'Receita Anual da Empresa', 'wc-pagarme' ),
				'max'               => 36,
				'min'               => 1,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_only_numbers',
				'error_msg'         => __( 'Insira um valor válido para o campo “Receita Anual da Empresa”.', 'wc-pagarme' ),
			),
			'monthly_income'        => array(
				'required'          => true,
				'label'             => __( 'Renda Mensal', 'wc-pagarme' ),
				'max'               => 36,
				'min'               => 1,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_only_numbers',
				'error_msg'         => __( 'Insira um valor válido para o campo “Renda Mensal”.', 'wc-pagarme' ),
			),
			'address_zipcode'       => array(
				'required'          => true,
				'label'             => __( 'CEP', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => false,
				'type'              => 'regex',
				'equal'             => array( '/^\d{5}-?\d{3}$/' ),
				'sanitize_callback' => 'wc_pagarme_only_numbers',
				'error_msg'         => __( 'Insira um código postal válido no campo "CEP". Ex: 06550-000.', 'wc-pagarme' ),
			),
			'address_street'        => array(
				'required'          => true,
				'label'             => __( 'Logradouro', 'wc-pagarme' ),
				'max'               => 150,
				'min'               => 1,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira um endereço válido no campo "Logradouro". Ex: Rua General Justo.', 'wc-pagarme' ),
			),
			'address_street_number' => array(
				'required'          => true,
				'label'             => __( 'Número', 'wc-pagarme' ),
				'max'               => 99999999,
				'min'               => 1,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_only_numbers',
				'error_msg'         => __( 'Insira um endereço válido no campo "Logradouro". Ex: Rua General Justo.', 'wc-pagarme' ),
			),
			'address_neighborhood'  => array(
				'required'          => true,
				'label'             => __( 'Bairro', 'wc-pagarme' ),
				'max'               => 100,
				'min'               => 1,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira um nome válido no campo "Bairro". Ex: Vila Olímpia.', 'wc-pagarme' ),
			),
			'address_city'          => array(
				'required'          => true,
				'label'             => __( 'Cidade', 'wc-pagarme' ),
				'max'               => 50,
				'min'               => 1,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira um nome válido no campo "Cidade". Ex: São Paulo.', 'wc-pagarme' ),
			),
			'address_state'         => array(
				'required'          => true,
				'label'             => __( 'Estado', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => 2,
				'type'              => 'regex',
				'equal'             => array( '/^[A-Z]{2}$/' ),
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Selecione um valor válido no campo "Estado". Ex: SP.', 'wc-pagarme' ),
			),
			'email'                 => array(
				'required'          => true,
				'label'             => __( 'E-mail', 'wc-pagarme' ),
				'max'               => 46,
				'min'               => 1,
				'size'              => false,
				'type'              => 'string',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira um endereço válido no campo "E-mail". Ex: seumome@gmail.com.', 'wc-pagarme' ),
			),
			'phone'                 => array(
				'required'          => true,
				'label'             => __( 'Celular', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => false,
				'type'              => 'regex',
				'equal'             => array( '/^\(\d{2}\) 9?\d{3}-\d{5}$/' ),
				'sanitize_callback' => 'wc_pagarme_only_numbers',
				'error_msg'         => __( 'Insira um número válido no campo "Telefone". Ex: (11) 91234-5678.', 'wc-pagarme' ),
			),
			'operation_type'        => array(
				'required'          => true,
				'label'             => __( 'Tipo da Conta', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => false,
				'type'              => 'string',
				'equal'             => array( 'checking', 'savings' ),
				'sanitize_callback' => 'wc_pagarme_sanitize_string',
				'error_msg'         => __( 'Insira valores válidos para o campo "Tipo da Conta": Conta Corrente ou Conta Poupança.', 'wc-pagarme' ),
			),
			'bank_number'           => array(
				'required'          => true,
				'label'             => __( 'Bank number', 'wc-pagarme' ),
				'max'               => 3,
				'min'               => 1,
				'size'              => false,
				'type'              => 'int',
				'equal'             => false,
				'sanitize_callback' => 'wc_pagarme_only_numbers',
				'error_msg'         => __( 'Insira valores válidos para o campo "Nome do Banco".', 'wc-pagarme' ),
			),
			'branch_number'         => array(
				'required'          => true,
				'label'             => __( 'Número da Agência', 'wc-pagarme' ),
				'max'               => 6,
				'min'               => 3,
				'size'              => false,
				'type'              => 'regex',
				'equal'             => array( '/^\d{1,10}(-\d{1})?$/' ),
				'sanitize_callback' => 'wc_pagarme_split_digit',
				'error_msg'         => __( 'Insira valores válidos para o campo "Número da Agência".', 'wc-pagarme' ),
			),
			'account_number'        => array(
				'required'          => true,
				'label'             => __( 'Número da Conta', 'wc-pagarme' ),
				'max'               => false,
				'min'               => false,
				'size'              => false,
				'type'              => 'regex',
				'equal'             => array( '/^\d{1,10}(-\d{1})?$/' ),
				'sanitize_callback' => 'wc_pagarme_split_digit',
				'error_msg'         => __( 'Insira valores válidos para o campo "Número da Conta".', 'wc-pagarme' ),
			),
		);

		return $schema;
	}

	/**
	 * Get recipient form template.
	 *
	 * @return void
	 */
	public static function output_recipient_form_template() {
		$current_user_id   = get_current_user_id();
		$current_user_info = get_userdata( $current_user_id );

		$recipient_id         = get_user_meta( $current_user_id, 'pagarme_recipient_id', true );
		$recipient_status     = get_user_meta( $current_user_id, 'pagarme_recipient_status', true );
		$recipient_kyc_status = get_user_meta( $current_user_id, 'pagarme_recipient_kyc_status', true );
		$bank_account_id      = get_user_meta( $current_user_id, 'pagarme_recipient_bank_account_id', true );

		wc_pagarme_get_template(
			'recipient-form.php',
			array(
				'user_id'              => $current_user_id,
				'user_info'            => $current_user_info,

				'occupations'          => wc_pagarme_get_occupations_list(),
				'banks'                => wc_pagarme_get_banks_list(),
				'states'               => wc_pagarme_get_states_list(),
				//Saved data
				'recipient_id'         => $recipient_id,
				'recipient_status'     => $recipient_status,
				'recipient_kyc_status' => $recipient_kyc_status,
				'bank_account_id'      => $bank_account_id,
			)
		);
	}

	/**
	 * Get recipient transactions template.
	 *
	 * @return void
	 */
	public function output_recipient_transactions_template() {
		$balance         = $operations  = array();
		$current_user_id = get_current_user_id();
		$recipient_id    = get_user_meta( $current_user_id, 'pagarme_recipient_id', true ) ?: false;
		try {
			// Process recipient balance request.
			$balance = $this->api->get_recipient_balance( $recipient_id );
			// Process recipient operations request.
			$operations = $this->api->get_recipient_operations(
				array(
					'recipient_id'  => $recipient_id,
					'page'          => ( $_GET['operations-page'] ?? 1 ),
					'created_since' => isset( $_GET['start_date'] ) && ! empty( $_GET['start_date'] ) ? ( date_create( $_GET['start_date'], timezone_open( 'America/Sao_Paulo' ) )->format( 'Y-m-d' ) ) : null,
					'created_until' => isset( $_GET['end_date'] ) && ! empty( $_GET['end_date'] ) ? ( date_create( sprintf( '%sT23:59:59', $_GET['end_date'] ), timezone_open( 'America/Sao_Paulo' ) )->format( 'Y-m-d' ) ) : null,
					'size'          => '10',
				)
			);
		} catch ( \Exception $e ) {
			$balance    = array(
				'available_amount'     => 0,
				'waiting_funds_amount' => 0,
				'transferred_amount'   => 0,
			);
			$operations = array(
				'data'   => array(),
				'paging' => array(),
			);
		}

		wc_pagarme_get_template(
			'recipient-transactions.php',
			array(
				'balance'    => $balance,
				'operations' => $operations,
			)
		);
	}

	/**
	 * Get recipient calendar template.
	 *
	 * @return void
	 */
	public function output_recipient_calendar_template() {
		$current_user_id = get_current_user_id();
		$recipient_id    = get_user_meta( $current_user_id, 'pagarme_recipient_id', true ) ?: false;
		try {
			// Process recipient balance request.
			$balance = $this->api->get_recipient_balance( $recipient_id );
		} catch ( \Exception $e ) {
			$balance = array(
				'available_amount'     => 0,
				'waiting_funds_amount' => 0,
				'transferred_amount'   => 0,
			);
		}

		wc_pagarme_get_template(
			'recipient-calendar.php',
			array(
				'balance' => $balance,
			)
		);
	}

	/**
	 * Get recipient verification template.
	 *
	 * @return void
	 */
	public function output_recipient_verification_template() {
		$current_user_id   = get_current_user_id();
		$current_user_info = get_userdata( $current_user_id );

		$recipient_id         = get_user_meta( $current_user_id, 'pagarme_recipient_id', true ); // TODO: change to "pagarme_recipient_id" in future
		$recipient_status     = get_user_meta( $current_user_id, 'pagarme_recipient_status', true );
		$recipient_kyc_status = get_user_meta( $current_user_id, 'pagarme_recipient_kyc_status', true );

		wc_pagarme_get_template(
			'recipient-verification.php',
			array(
				'user_id'              => $current_user_id,
				'user_info'            => $current_user_info,

				//Saved data
				'recipient_id'         => $recipient_id,
				'recipient_status'     => $recipient_status,
				'recipient_kyc_status' => $recipient_kyc_status,
				'recipient_kyc_link'   => $this->get_verification_link(),
			)
		);
	}

	/**
	 * Run cron job.
	 *
	 * @return void
	 */
	public function get_verification_link() {
		$request = $this->api->get_kyc_link( '' );

		if ( isset( $request['url'] ) ) {
			return $request['url'];
		}

		return '#';
	}

	/**
	 * Load payment settings from marketplace options.
	 *
	 * @param string $value  The default value from gateway settings.
	 * @param string $key  The settings key.
	 *
	 * @return string
	 */
	public function load_default_api_settings( $settings, $key ) {
		// Get marketplace settings.
		$settings['testmode']           = ''; // It is set to empty because the marketplace settings do not yet manage keys for different environments.
		$settings['debug']              = $this->settings['debug'];
		$settings['public_key']         = $this->settings['public_key'];
		$settings['public_key_sandbox'] = $this->settings['public_key_sandbox'];
		$settings['secret_key']         = $this->settings['secret_key']; // Default.
		$settings['secret_key_sandbox'] = $this->settings['secret_key_sandbox'];

		return $settings;
	}

	/**
	 * Hiden API key in gateway settings.
	 * The API key must be configured once for all methods in the gateway plugin settings.
	 *
	 * @param array $fields  The gateway settings fields.
	 *
	 * @return void
	 */
	public function hide_gateway_settings_fields( $fields ) {
		// Hide sandbox mode.
		unset( $fields['environment'] );
		unset( $fields['testmode'] );
		// Hide API credentials.
		unset( $fields['public_key'] );
		unset( $fields['public_key_sandbox'] );
		unset( $fields['secret_key'] );
		unset( $fields['secret_key_sandbox'] );

		return $fields;
	}
}
