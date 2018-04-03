<?php
/**
 * Admin Page Favo
 *
 * @package favo
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Favo_Admin_Page' ) ) {

	/**
	 *
	 * Class for add admin page on wp-admin
	 */
	class Favo_Admin_Page {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'favo_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'favo_save_settings' ) );
		}

		/**
		 * Create Menu Favo Page
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 */
		public function favo_admin_menu() {
			$page_hook = add_menu_page(
				'Favo',
				'Favo',
				'manage_options',
				'favo',
				array( $this, 'favo_handler' ),
				'dashicons-heart',
				58
			);

			add_submenu_page(
				'favo',
				'Settings',
				'Settings',
				'manage_options',
				'favo-settings',
				array( $this, 'favo_settings_handler' )
			);
		}

		/**
		 * Callback favo admin page untuk view
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 */
		public function favo_handler() {
			$license_handler = new Tonjoo_License_Handler( 'favo', __FILE__ );
			$license_handler->set_license_form_url( admin_url( 'admin.php?page=favo#license' ) );
			$tabs   = apply_filters(
				'favo_about_tabs', array(
					'overview' => array(
						'title'    => esc_html__( 'Overview', 'favo' ),
						'template' => FAVO_PATH . 'views/admin/admin-about-overview.php',
					),
					'license'  => array(
						'title'    => esc_html__( 'License', 'favo' ),
						'template' => FAVO_PATH . 'views/admin/admin-about-license.php',
					),
					'upsell'   => array(
						'title'    => esc_html__( 'Other Cool Stuff for Your Website', 'favo' ),
						'template' => FAVO_PATH . 'views/admin/admin-about-upsell.php',
					),
				)
			);
			$upsell = new Tonjoo_Plugins_Upsell( 'favo' );
			include_once FAVO_PATH . 'views/admin/page-dashboard.php';
		}

		/**
		 * Callback favo admin page settings
		 *
		 * @version 1.0.0
		 * @since 1.0.0
		 */
		public function favo_settings_handler() {
			$favo_image_val_off = favo_setting( 'image_val_off' );
			$favo_image_val_on  = favo_setting( 'image_val_on' );

			wp_enqueue_media();
			include_once FAVO_PATH . 'views/admin/page-setting.php';
		}

		/**
		 * Handle save setting favo action
		 *
		 * @return [type] [description]
		 */
		public function favo_save_settings() {
			if ( isset( $_POST['favo_field_setting'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['favo_field_setting'] ) ), 'favo_action_setting' ) ) {
				$favo_settings = array(
					'enabled'                 => sanitize_text_field( $_POST['favo_enabled'] ),
					'favo_count'              => sanitize_text_field( $_POST['favo_count'] ),
					'required_login'          => sanitize_text_field( $_POST['favo_required_login'] ),
					'display_position_button' => sanitize_text_field( $_POST['display_position_button'] ),
					'type_active'             => sanitize_text_field( $_POST['favo_type'] ),
					'display_on'              => $_POST['display_on'],
					'enable_add_success_message' => sanitize_text_field( $_POST['enable_add_success_message'] ),
					'enable_remove_success_message' => sanitize_text_field( $_POST['enable_remove_success_message'] ),
					'button'                  => array(
						'text'  => array(
							'val_on'  => sanitize_text_field( $_POST['favo_text_val_on'] ),
							'val_off' => sanitize_text_field( $_POST['favo_text_val_off'] ),
						),
						'image' => array(
							'val_on'  => sanitize_text_field( $_POST['favo_image_val_on'] ),
							'val_off' => sanitize_text_field( $_POST['favo_image_val_off'] ),
						),
					),
					'messages'                => array(
						'add_success_message'    => sanitize_text_field( $_POST['add_success_message'] ),
						'remove_success_message' => sanitize_text_field( $_POST['remove_success_message'] ),
						'required_login_message' => sanitize_text_field( $_POST['required_login_message'] ),
					),
				);

				update_option( 'favo_settings', $favo_settings );
			}
		}

	}

	new Favo_Admin_Page();

}
