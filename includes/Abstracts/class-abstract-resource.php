<?php

namespace Aquapress\Pagarme\Abstracts;

/**
 * Abstract class that will be inherited by all resources.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Abstracts\Resource class.
 *
 * @since 1.0.0
 */
abstract class Resource {

	/**
	 * Resource identifier.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Run child class connetor actions hooks.
	 *
	 * @return void
	 */
	abstract public function init_hooks();
	
	/**
	 * Check the requirements for running the split actions.
	 *
	 * @return boolean
	 */
	public function is_available() {
		return true;
	}
}
