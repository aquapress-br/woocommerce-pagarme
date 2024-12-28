<?php 

namespace Aquapress\Pagarme\Helpers;

/**
 * Helper class payload.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Helper\Payload class.
 *
 * @since 1.0.0
 */
class Payload {

	/**
	 * Get customer data for an order.
	 *
	 * @param int $order_id The WooCommerce order ID.
	 * @return array The customer data structure.
	 */
	public static function Build_Transaction_Payload( $order_id ) {
		// Check WP user has customer ID pagarme.
		$customer_id = get_user_meta( $user_id, '_wc_pagarme_customer_id', true );
		if ( ! empty( $customer_id ) ) {
			return array(
				'customer_id' => $customer_id,
			);
		} else {
			// Load the WooCommerce order object.
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return array(); // Return an empty array if the order is invalid.
			}
			
			// Get additional information about the customer.
			$data = array(
				'customer' => array(
					'code'  => '#' . $order->get_customer_id(),
					'name'  => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
					'email' => $order->get_billing_email(),
				),
			);
			// Process home phone information.
			if ( ! empty( $order->get_billing_phone() ) ) {
				$data['customer']['phones']['home_phone'] = array(
					'number'       => wc_pagarme_get_phone_information( $order->get_billing_phone(), 'number' ),
					'area_code'    => wc_pagarme_get_phone_information( $order->get_billing_phone(), 'area_code' ),
					'country_code' => wc_pagarme_get_phone_information( $order->get_billing_phone(), 'country_code' ),
				);
			}
			// Process mobile phone information.
			if ( ! empty( $order->billing_cellphone ) ) {
				$data['customer']['phones']['mobile_phone'] = array(
					'number'       => wc_pagarme_get_phone_information( $order->billing_cellphone, 'number' ),
					'area_code'    => wc_pagarme_get_phone_information( $order->billing_cellphone, 'area_code' ),
					'country_code' => wc_pagarme_get_phone_information( $order->billing_cellphone, 'country_code' ),
				);
			}
			// Process customer type information.
			if ( $order->get_meta( '_billing_persontype' ) == '2' ) {
				$data['customer']['type']          = 'company';
				$data['customer']['document_type'] = 'CNPJ';
				$data['customer']['document']      = wc_pagarme_only_numbers( $order->get_meta( '_billing_cnpj' ) );
			} else {
				$data['customer']['type']          = 'individual';
				$data['customer']['document_type'] = 'CPF';
				$data['customer']['document']      = wc_pagarme_only_numbers( $order->get_meta( '_billing_cpf' ) );
			}
			
			// Get billing information.
			$data = array_merge( 
				$data,
				array(
					'billing_address' => array(
						'country'       => strtolower( $order->get_billing_country() ),
						'state'         => strtolower( $order->get_billing_state() ),
						'city'          => $order->get_billing_city(),
						'neighborhood'  => $order->get_meta( '_billing_neighborhood' ), // Custom meta field for neighborhood.
						'street'        => $order->get_billing_address_1(),
						'street_number' => $order->get_meta( '_billing_number' ), // Custom meta field for street number.
						'zipcode'       => $order->get_billing_postcode(),
						// Fix API request
						'line_1'        => $order->get_billing_address_1() . ' N ' . $order->get_meta( '_billing_number' ) . ' - ' . $order->get_meta( '_billing_neighborhood' ),
						'line_2'        => $order->get_billing_address_2(),
					)
				)
			);
			// Get order amount for credit card payment
			if ( $order->get_payment_method() == 'wc_pagarme_creditcard' ) {
				$amount = $order->get_total() * 100;
			} else {
				$amount = $order->get_total() * 100;
			}
			// Get shipping information.
			$data = array_merge( 
				$data,
				array(
					'items' => array(
						array(
							'code'        => $order_id,
							'amount'      => $amount,
							'description' => sprintf( __( 'WooCommerce order #%1$s', 'wc-pagarme' ), $order_id ),
							'quantity'    => 1,
						),
					),
				)
			);
			
			return $data;
		}
		
		return array();
	}
	
	public static function Build_Recipient_Payload( $request, $user_id = false ) {
		// Check recipient exists.
		$recipient_id = get_user_meta( $user_id ?: get_current_user_id(), 'pagarme_recipient_id', true ) ?: false;
		if ( ! empty( $recipient_id ) ) {
			$data = array(
				'register_information' => array(
					'email'           => $request['email'],
					'document' => $request['document'],
					'phone_numbers' => array(
						array(
							'ddd'    => wc_pagarme_get_phone_information( $request['phone'], 'area_code' ),
							'number' => wc_pagarme_get_phone_information( $request['phone'], 'number' ),
							'type'   => 'mobile',
						)
					),
					'address' => array(
						'street' => $request['address_street'],
						'complementary' => 'N/D',
						'street_number' => $request['address_street_number'],
						'neighborhood' => $request['address_neighborhood'],
						'city' => $request['address_city'],
						'state' => $request['address_state'],
						'zip_code' => $request['address_zipcode'],
						'reference_point' => 'N/D',
					)
				)
			);
			if ( $request['account_type'] == 'corporation' ) {
					$data = array_merge( $data, array(
						'register_information' => array_merge(
							$data['register_information'],
							array(
								'type'           => 'corporation',
								'company_name'   => $request['company_legal_name'],
								'trading_name'   => $request['company_name'],
								'annual_revenue' => $request['annual_revenue'],
							)
						)
					)
				);
			} else {
				$data = array_merge( $data, array(
					'register_information' => array_merge( $data['register_information'], array(
						'type'      => 'individual',
						'name'      => $request['full_name'],
						'birthdate' => $request['birthdate'],
						'monthly_income'        => $request['monthly_income'],
						'professional_occupation' => $request['occupation'],
							)
						),
					)
				);
			}
			return $data;
		} else {
			$data = array(
				'transfer_settings' => array(
					'transfer_interval' => 'monthly',
					'transfer_day'      => '5',
					'transfer_enabled'  => true,
				),
				'register_information' => array(
					'email'           => $request['email'],
					'document' => $request['document'],
					'phone_numbers' => array(
						array(
							'ddd'    => wc_pagarme_get_phone_information( $request['phone'], 'area_code' ),
							'number' => wc_pagarme_get_phone_information( $request['phone'], 'number' ),
							'type'   => 'mobile',
						)
					),
					'address' => array(
						'street' => $request['address_street'],
						'complementary' => 'N/D',
						'street_number' => $request['address_street_number'],
						'neighborhood' => $request['address_neighborhood'],
						'city' => $request['address_city'],
						'state' => $request['address_state'],
						'zip_code' => $request['address_zipcode'],
						'reference_point' => 'N/D',
					)
				),
				'default_bank_account' => array(
					'type'                => $request['operation_type'],
					'bank'                => $request['bank_number'],
					'branch_number'       => $request['branch_number']['number'],
					'branch_check_digit'  => $request['branch_number']['digit'] ?: null,
					'account_number'      => $request['account_number']['number'],
					'account_check_digit' => $request['account_number']['digit'] ?: 0,
				),
			);

			if ( $request['account_type'] == 'corporation' ) {
					$data = array_merge(
						$data,
						array(
							'register_information' => array_merge(
								$data['register_information'],
								array(
									'type'           => 'corporation',
									'company_name'   => $request['company_legal_name'],
									'trading_name'   => $request['company_name'],
									'annual_revenue' => $request['annual_revenue'],
								)
							),
							'default_bank_account'         => array_merge(
								$data['default_bank_account'],
								array(
									'holder_name' => substr( $request['company_legal_name'], 0, 30 ),
									'holder_type' => 'company',
									'holder_document' => $request['document'],
								)
							),
						)
				);
			} else {
					$data = array_merge(
						$data,
						array(
							'register_information' => array_merge(
								$data['register_information'],
								array(
									'type'      => 'individual',
									'name'      => $request['full_name'],
									'birthdate' => $request['birthdate'],
									'monthly_income' => $request['monthly_income'],
									'professional_occupation' => $request['occupation'],
								)
							),
							'default_bank_account'         => array_merge(
								$data['default_bank_account'],
								array(
									'holder_name' => substr( $request['full_name'], 0, 30 ),
									'holder_type' => 'individual',
									'holder_document' => $request['document'],
								)
							),
						)
					);
			}

			return $data;
		}
	}

}
