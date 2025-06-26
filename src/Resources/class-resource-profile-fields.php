<?php

namespace Aquapress\Pagarme\Resources;

/**
 * Resources for adding fields in the profile editor in the admin panel.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Resources\Profile_Fields class.
 *
 * @extends Aquapress\Pagarme\Abstracts\Resource.
 */
class Profile_Fields extends \Aquapress\Pagarme\Abstracts\Resource {

	/**
	 * Running the connector actions.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'show_user_profile', array( $this, 'display_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'display_user_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_fields' ) );
	}
	
	/**
	 * Displays additional fields on the user profile page.
	 *
	 * This method adds a section to the user profile page for managing the Pagar.me 
	 * recipient ID. The fields are rendered within a custom table.
	 *
	 * @param WP_User $user The user object of the currently edited profile.
	 */
	public function display_user_profile_fields( $user ) {
		?>
			<h3><?php _e( 'Pagar.me', 'wc-pagarme' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="pagarme_recipient_id"><?php _e( 'ID Recebedor', 'wc-pagarme' ); ?></label></th>
					<td>
						<input type="text" name="pagarme_recipient_id" id="pagarme_recipient_id" value="<?php echo esc_attr( get_user_meta( $user->ID, 'pagarme_recipient_id', true ) ); ?>" class="regular-text" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="pagarme_recipient_id_sandbox"><?php _e( 'ID Recebedor (Testmode)', 'wc-pagarme' ); ?></label></th>
					<td>
						<input type="text" name="pagarme_recipient_id_sandbox" id="pagarme_recipient_id_sandbox" value="<?php echo esc_attr( get_user_meta( $user->ID, 'pagarme_recipient_id_sandbox', true ) ); ?>" class="regular-text" /><br />
					</td>
				</tr>
			</table>
		<?php
	}

	/**
	 * Saves additional fields from the user profile page.
	 *
	 * This method handles saving the Pagar.me recipient ID from the custom fields 
	 * added to the user profile page. It performs nonce verification and permission checks 
	 * to ensure security.
	 *
	 * @param int $user_id The ID of the user whose profile is being updated.
	 * @return void|false Returns false if the user lacks permissions.
	 */
	public function save_user_profile_fields( $user_id ) {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
			return;
		}
		
		if ( !current_user_can( 'edit_user', $user_id ) ) { 
			return false; 
		}

		if ( isset( $_POST['pagarme_recipient_id'] ) ) {
			update_user_meta( $user_id, 'pagarme_recipient_id', $_POST['pagarme_recipient_id'] );
		}
		if ( isset( $_POST['pagarme_recipient_id_sandbox'] ) ) {
			update_user_meta( $user_id, 'pagarme_recipient_id_sandbox', $_POST['pagarme_recipient_id_sandbox'] );
		}
	}

}