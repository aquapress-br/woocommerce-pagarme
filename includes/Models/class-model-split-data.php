<?php

namespace Aquapress\Pagarme\Models;

/**
 * A utility class for managing split payments, allowing easy
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Models\Split_Data model.
 *
 * @since 1.0.0
 */
class Split_Data {

    /**
     * Split rules stored as an associative array.
     *
     * @var array
     */
    private $data = array();

    /**
     * Adds a split rule to the data.
     *
     * @param string $walletId           Asaas wallet identifier to receive the transfer.
     * @param float  $fixedValue         Fixed amount to be transferred to the account when the charge is received.
     * @param float  $percentualValue    Percentage of the net value of the charge to be transferred when received.
     * @param float  $totalFixedValue    (Only for installments) Value that will be split concerning the total amount to be installed.
     *
     * @return void
     */
    public function add_to_split( $walletId, $fixedValue = null, $percentualValue = null, $totalFixedValue = null ) 
	{
		$settings = get_option( 'woocommerce_asaas-credit-card_settings' );
		$commission_type = $settings['wc-asaas-marketplace-commission-type'] ?? 'percentualValue';
		
		if ( 'fixedValue' == $commission_type ) {
			$split_rule = compact( 'walletId', 'fixedValue', 'totalFixedValue' );
		} else {
			$split_rule = compact( 'walletId', 'percentualValue', 'totalFixedValue' );
		}
		
        $this->data[] = array_filter(
			$split_rule,
			function ( $value ) {
				return !is_null( $value );
			}
		);
    }

    /**
     * Get the split data.
     *
     * @return array|false Containing the split rules.
     */
    public function get_data() 
	{
        if ( $this->data ) {
			return $this->data;
		};
		
		return false;
    }
}
