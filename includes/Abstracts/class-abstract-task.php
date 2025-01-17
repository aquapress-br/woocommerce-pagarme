<?php

namespace Aquapress\Pagarme\Abstracts;

/**
 * Abstract class that will be inherited by all tasks.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Abstracts\Task class.
 *
 * @since 1.0.0
 */
abstract class Task {

	/**
	 * Task identifier.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Task recurrence.
	 *
	 * @var string
	 */
	public $recurrence;

	/**
	 * Task interval.
	 *
	 * @var string
	 */
	public $interval = 0; // Ex: "5 * 24 * 60 * 60" 5 days in seconds.

	/**
	 * Logger instance.
	 *
	 * This attribute is used to record events and log messages.
	 * The instance may be an object of a specific logging class or
	 * a similar resource.
	 *
	 * @var Aquapress\Pagarme\Logger
	 */
	public ?\Aquapress\Pagarme\Logger $logger;

	/**
	 * Execute the task.
	 *
	 * Subclasses should implement this method to perform tasks or any required operations.
	 *
	 * @return void
	 */
	abstract public function process();

	/**
	 * Check the requirements for running the task process.
	 *
	 * @return boolean
	 */
	public function is_available() {
		return true;
	}

	/**
	 * Debug logger.
	 *
	 * @param string $message      Log message.
	 * @param int    $start_time   Start time (optional).
	 * @param int    $end_time     End time (optional).
	 *
	 * @return void
	 */
	public function debug( $message, $start_time = null, $end_time = null ) {
		if ( ! $this->logger ) {
			$this->logger = new \Aquapress\Pagarme\Logger( 'wc_pagarme_tasks' );
		}

		$this->logger->add( $message, $start_time, $end_time );
	}
}
