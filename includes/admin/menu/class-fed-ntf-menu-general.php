<?php
/**
 * Main Menu.
 *
 * @package Frontend Dashboard Notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FED_NTF_Menu_General' ) ) {
	/**
	 * Class FED_NTF_Menu_General
	 */
	class FED_NTF_Menu_General {
		/**
		 * Layout.
		 */
		public function layout() {
			$settings = $this->settings_data();
			fed_common_simple_layout( $settings );
		}

		/**
		 * Setting Data
		 *
		 * @return mixed|void.
		 *
		 * Options => fed_payment_settings
		 */
		public function settings_data() {
			$settings = get_option( 'fed_payment_settings' );
			$array    = array(
				'form'  => array(
					'method' => '',
					'class'  => 'fed_admin_menu fed_ajax',
					'attr'   => '',
					'action' => array(
						'url'        => '',
						'action'     => 'fed_ajax_request',
						'parameters' => array(
							'fed_action_hook' => 'FEDPayment',
						),
					),
					'nonce'  => array(
						'action' => '',
						'name'   => '',
					),
					'loader' => '',
				),
				'input' => array(
					'Enable Payment' => array(
						'col'          => 'col-md-7',
						'name'         => __( 'Gateway', 'frontend-dashboard' ),
						'input'        =>
							fed_get_input_details(
								array(
									'input_meta'  => 'settings[gateway]',
									'user_value'  => isset( $settings['settings']['gateway'] ) ? esc_attr(
										$settings['settings']['gateway']
									) : 'disable',
									'input_type'  => 'radio',
									'class_name'  => 'm-r-10',
									'input_value' => fed_get_payment_gateways(),
								)
							),
						'help_message' => fed_show_help_message(
							array(
								'content' => __(
									'By Checking this, you are enabling the Payment',
									'frontend-dashboard'
								),
							)
						),
					),
				),
			);

			return apply_filters( 'fed_payment_settings', $array, $settings );
		}
	}

}