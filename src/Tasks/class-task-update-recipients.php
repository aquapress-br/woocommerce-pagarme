<?php

namespace Aquapress\Pagarme\Tasks;

/**
 * Abstract class that will be inherited by all tasks.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Tasks\Update_Recipients class.
 *
 * @extends Aquapress\Pagarme\Abstracts\Task.
 */
class Update_Recipients extends \Aquapress\Pagarme\Abstracts\Task {
	
	use \Aquapress\Pagarme\Traits\User_Meta;

	/**
	 * Task identifier.
	 *
	 * @var string
	 */
	public $id = 'update_recipients';

	/**
	 * Task recurrence.
	 *
	 * @var string
	 */
	public $recurrence = 'hourly';

	/**
	 * Execute the task. Register action hook to perform recipient update on 'pagarme_recipient_update'.
	 *
	 * Subclasses should implement this method to perform tasks or any required operations.
	 *
	 * @return void
	 */
	public function process() {
		$api = \Aquapress\Pagarme\Helpers\Factory::Load_API( 'wc_pagarme_marketplace' );
		try {
			$request = $api->get_recipients(
				array(
					'size' => 30, // TODO: The API does not support time filters to retrieve recipients. Therefore, a random number is set.
				)
			);
			if ( is_array( $request ) && ! empty( $request ) ) {
				foreach ( $request['data'] as $recipient ) {
					$recipient_id = $recipient['id'];
					$user = static::get_user_by_recipient_id( $recipient_id );
					if ( $user !== false ) {
						if ( $recipient['status'] != 'active' ) {
							update_user_meta( $user->ID, 'pagarme_recipient_status', $recipient['status'] );
							update_user_meta( $user->ID, 'pagarme_recipient_status_reason', $recipient['status_reason'] ?? '' );
							update_user_meta( $user->ID, 'pagarme_recipient_kyc_status', $recipient['kyc_details']['status'] ?? '' );
							update_user_meta( $user->ID, 'pagarme_recipient_bank_account_id', $request['default_bank_account']['id'] );
						}
					}
				}
			}
		} catch ( Exception $e ) {
			// Output error message.
			$this->debug( 'Failed to process scheduled task' );
		}
	}
}
