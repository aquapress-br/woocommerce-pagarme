<?php defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_pagarme_gateways_register' ) ) {

	/**
	 * Registers Pagar.me payment methods for WooCommerce.
	 *
	 * This function adds custom Pagar.me payment gateways (like Credit Card) to the list of available
	 * WooCommerce payment methods. It uses the `apply_filters` function to extend the
	 * 'woocommerce_payment_gateways' filter with additional payment methods.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	function wc_pagarme_gateways_register() {
		// Adds Pagar.me's Credit Card gateway to the WooCommerce payment methods list.
		add_filter(
			'woocommerce_payment_gateways',
			function ( $payment_methods ) {
				$payment_methods[] = '\Aquapress\Pagarme\Gateways\CreditCard';
				$payment_methods[] = '\Aquapress\Pagarme\Gateways\PIX';
				$payment_methods[] = '\Aquapress\Pagarme\Gateways\Boleto';
				return $payment_methods;
			}
		);
	}

}


if ( ! function_exists( 'wc_pagarme_marketplaces_register' ) ) {
	/**
	 * Registers and initializes marketplace connectors for WooCommerce Pagar.me integration.
	 *
	 * This function checks if the wc_pagarme_marketplaces_register function is not already defined.
	 * If it's not, it defines the function to register marketplace connectors.
	 *
	 * The function performs the following steps:
	 *
	 * @return void
	 */
	function wc_pagarme_marketplaces_register() {
		$embedded = array(
			'\Aquapress\Pagarme\Marketplaces\Dokan',
		);

		$load_connectors = array_unique(
			apply_filters(
				'wc_pagarme_marketplaces',
				$embedded
			)
		);

		foreach ( $load_connectors as $class_name ) {
			if ( ! apply_filters( 'wc_pagarme_marketplace_load', true, $class_name ) ) {
				continue;
			}

			if ( is_string( $class_name ) && class_exists( $class_name ) ) {
				$obj = new $class_name();

				if ( $obj->is_available() ) {
					$obj->init_connector();
				}
			}
		}
	}
}

if ( ! function_exists( 'wc_pagarme_tasks_register' ) ) {

	/**
	 * Task register
	 *
	 * @since    1.0.0
	 * @return   string
	 */
	function wc_pagarme_tasks_register() {
		$embedded = array(
			'Dashlifter\Blocks\Dashboard',
		);

		$load_tasks = array_unique(
			apply_filters(
				'wc_pagarme_tasks',
				$embedded
			)
		);

		foreach ( $load_tasks as $class_name ) {
			$is_load = apply_filters( 'dashlifter_load_task', true, $class_name );

			if ( ! $is_load ) {
				continue;
			}

			if ( is_string( $class_name ) && class_exists( $class_name ) ) {
				$task = new $class_name();
			} else {
				throw new \Exception(
					sprintf( 'The %s is not a valid class name or the class was not found.', $class_name )
				);
			}

			if ( ! is_a( $task, 'Aquapress\Pagarme\Abstracts\Task' ) ) {
				throw new \Exception(
					'The Dashlifter\Abstract_Block class has not been extended to one or more elements.'
				);
			}

			if ( $task->is_available() ) {
				if ( ! wp_next_scheduled( "pagarme_{$task->id}" ) ) {
					wp_schedule_event( time() + $task->interval, $task->recurrence, "pagarme_{$task->id}" );
				}
			}
		}
	}

}

if ( ! function_exists( 'wc_pagarme_tasks_unregister' ) ) {

	/**
	 * Task unregister
	 *
	 * @since    1.0.0
	 * @return   string
	 */
	function wc_pagarme_tasks_unregister() {
		$embedded = array(
			'Dashlifter\Blocks\Dashboard',
		);

		$load_tasks = array_unique(
			apply_filters(
				'wc_pagarme_tasks',
				$embedded
			)
		);

		foreach ( $load_tasks as $class_name ) {
			$is_load = apply_filters( 'dashlifter_load_task', true, $class_name );

			if ( ! $is_load ) {
				continue;
			}

			if ( is_string( $class_name ) && class_exists( $class_name ) ) {
				$task = new $class_name();
			} else {
				throw new \Exception(
					sprintf( 'The %s is not a valid class name or the class was not found.', $class_name )
				);
			}

			if ( ! is_a( $task, 'Aquapress\Pagarme\Abstracts\Task' ) ) {
				throw new \Exception(
					'The Dashlifter\Abstract_Block class has not been extended to one or more elements.'
				);
			}

			wp_clear_scheduled_hook( "pagarme_{$task->id}" );
		}
	}

}
