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
	 * @param string $recipient_id              Pagarme recipient identifier to receive the transfer.
	 * @param int    $amount                    Fixed amount to be transferred to the account when the charge is received.
	 * @param bool   $liable                    Indicates whether the recipient is responsible for the transaction in the event of a chargeback.
	 * @param bool   $charge_processing_fee     Indicates whether the recipient will be charged transaction fees.
	 * @param bool   $charge_remainder_fee      Indicates whether the recipient will receive the remainder of the receivables after a split.
	 *
	 * @return void
	 */
	public function add_to_split( $recipient_id, int $amount, $liable = true, $charge_processing_fee = false, $charge_remainder_fee = false ) {
		$this->data[] = array(
			'type'         => 'flat',
			'amount'       => $amount,
			'recipient_id' => $recipient_id,
			'options'      => array(
				'liable'                => $liable,
				'charge_processing_fee' => $charge_processing_fee,
				'charge_remainder_fee'  => $charge_remainder_fee,
			),
		);
	}

	/**
	 * Get the split data.
	 *
	 * @return array|false Containing the split rules.
	 */
	public function get_data() {
		if ( $this->data ) {
			return $this->data;
		}

		return false;
	}
}
