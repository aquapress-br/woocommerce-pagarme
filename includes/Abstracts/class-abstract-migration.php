<?php

namespace Aquapress\Pagarme\Abstracts;

/**
 * Abstract class that will be inherited by all payments methods.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abstract base class for version migration.
 *
 * This class defines the basic structure for gateway version migration.
 * Subclasses should implement the abstract method `process` to perform
 * specific migration process.
 */
abstract class Migration {

	/**
	 * @var string The target version for the migration.
	 */
	public $version = '';

	/**
	 * Logger instance.
	 *
	 * This attribute is used to record events and log messages.
	 * The instance may be an object of a specific logging class or
	 * a similar resource.
	 *
	 * @var Aquapress\Pagarme\Logger|null
	 */
	public ?\Aquapress\Pagarme\Logger $logger = null;

	/**
	 * Process the migration to the specified version.
	 *
	 * Subclasses should implement this method to perform migration process.
	 *
	 * @param string $current_version The current version of the dashboard.
	 * @return bool Returns true if the migration was successful, otherwise false.
	 */
	abstract public function process( $current_version ): bool;

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
			$this->logger = new \Aquapress\Pagarme\Logger( 'wc_pagarme_migrations' );
		}

		$this->logger->add( $message, $start_time, $end_time );
	}
}
