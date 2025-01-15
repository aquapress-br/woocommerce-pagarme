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
abstract class Abstract_Migration {

    /**
     * @var string The target version for the migration.
     */
    public $version = '';

    /**
     * Process the migration to the specified version.
     * 
     * Subclasses should implement this method to perform migration process.
     * 
     * @param string $current_version The current version of the dashboard.
     * @return bool Returns true if the migration was successful, otherwise false.
     */
    abstract public function process( $current_version ): bool;

}