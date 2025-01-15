<?php

namespace Aquapress\Pagarme;

/**
 * Process log for gateway events.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class for managing logging events in Pagar.me.
 *
 * This class is responsible for handling the creation and writing of logs
 * related to events in the Pagar.me system. It includes attributes for
 * storing the logger instance and the log file name.
 *
 */
class Logger {

	/**
	 * Logger instance.
	 *
	 * This attribute is used to record events and log messages.
	 * The instance may be an object of a specific logging class or
	 * a similar resource.
	 *
	 * @var mixed
	 */
	private $logger;

	/**
	 * Log file name.
	 *
	 * This attribute stores the name of the file where logs will be
	 * written. The default value is an empty string, and it should be set
	 * with the appropriate name before starting log recording.
	 *
	 * @var string
	 */
	public $filename = '';

	/**
	 * Constructor.
	 *
	 * @param string $filename The logger file name.
	 */
	public function __construct( $filename = 'wc_pagarme' ) {
		$this->filename = $filename;
	}

	/**
	 * Utilize WC logger class
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function add( $message, $start_time = null, $end_time = null ) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}

		if ( apply_filters( 'wc_pagarme_logging', true, $message ) ) {
			if ( empty( $this->logger ) ) {
				$this->logger = wc_get_logger();
			}

			if ( ! is_null( $start_time ) ) {
				$formatted_start_time = date_i18n(
					get_option( 'date_format' ) . ' g:ia',
					$start_time
				);
				$end_time             = is_null( $end_time )
					? current_time( 'timestamp' )
					: $end_time;
				$formatted_end_time   = date_i18n(
					get_option( 'date_format' ) . ' g:ia',
					$end_time
				);
				$elapsed_time         = round( abs( $end_time - $start_time ) / 60, 2 );
				$log_entry            =
					"\n" .
					'====' .
					WC_PAGARME_NAME .
					' Version: ' .
					WC_PAGARME_VERSION .
					'====' .
					"\n";
				$log_entry           .=
					'====Start Log ' .
					$formatted_start_time .
					'====' .
					"\n" .
					$message .
					"\n";
				$log_entry           .=
					'====End Log ' .
					$formatted_end_time .
					' (' .
					$elapsed_time .
					')====' .
					"\n\n";
			} else {
				$log_entry  =
					"\n" .
					'====' .
					WC_PAGARME_NAME .
					' Version: ' .
					WC_PAGARME_VERSION .
					'====' .
					"\n";
				$log_entry .=
					'====Start Log====' .
					"\n" .
					$message .
					"\n" .
					'====End Log====' .
					"\n\n";
			}

			$this->logger->debug( $log_entry, array( 'source' => $this->filename ) );
		}
	}
}
