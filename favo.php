<?php
/**
 * Plugin Name: Favo
 * Plugin URI: http://tonjoostudio.com/plugin/favo/
 * Description: Product Favorit
 * Version: 1.0.0
 * Author: Tonjoo
 * Author URI: http://tonjoostudio.com/
 * Text Domain: favo
 * Domain Path: /lang
 *
 * WC requires at least: 3.0
 * WC tested up to: 3.0
 *
 * Copyright: © 2018 favo.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $favo_db_version;
$favo_db_version = '1.0';

if ( ! class_exists( 'Favo' ) ) {

	/**
	 * Main Class Favo
	 */
	class Favo {

		public $helpers;

		/**
		 * Class constructor.
		 *
		 * @return void
		 */
		public function __construct() {

			if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_action(
					'admin_notices', function() {
						$class   = 'notice notice-error';
						$message = __( 'WooCommerce needs to be installed and activated to use "favo"', 'favo' );

						printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
					}
				);
				return;
			}

			register_activation_hook( __FILE__, array( $this, 'favo_install' ) );

			$this->define_constants();
			$this->includes();

			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

		}

		/**
		 * Setup plugin constants.
		 *
		 * @return void
		 */
		public function define_constants() {
			global $wpdb;
			define( 'FAVO_VERSION', '1.0.0' );
			define( 'FAVO_URL', plugins_url( '', __FILE__ ) );
			define( 'FAVO_LINK', plugin_dir_url( '', __FILE__ ) );
			define( 'FAVO_PATH', plugin_dir_path( __FILE__ ) );
			define( 'FAVO_REL_PATH', dirname( plugin_basename( __FILE__ ) ) . '/' );
			define( 'FAVO_DB_NAME', $wpdb->prefix . 'favo' );
		}

		/**
		 * Include required files.
		 *
		 * @return void
		 */
		private function includes() {
			include_once FAVO_PATH . 'inc/helpers.php';
			include_once FAVO_PATH . 'inc/class-favo-admin-page.php';
			include_once FAVO_PATH . 'inc/class-favo-front.php';
			include_once FAVO_PATH . 'libs/class-tonjoo-plugins-upsell.php';
			include_once FAVO_PATH . 'libs/tonjoo-license/tonjoo-license-handler.php';
		}

		/**
		 * Method for handle what first plugin doing after active favo plugin
		 *
		 * @return [type] [description]
		 */
		public function favo_install() {
			if ( is_favo_db_exist() == false ) {
				favo_install();
			}
			if ( get_page_by_title( 'Favorite List', ARRAY_A, 'page' ) == null ) {
				favo_create_page();
			}
			if ( ! get_option( 'favo_settings' ) ) {
				favo_default_setting();
			}
		}

		/**
		 * Add setting quick link to the plugins list
		 *
		 * @param  array $links Links.
		 * @return array $links Links
		 */
		public function plugin_add_settings_link( $links ) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page=favo-settings' ) . '">' . __( 'settings', 'favo' ) . '</a>';
			array_push( $links, $settings_link );
			return $links;
		}

		/**
		 * Enqueue admin scripts and styles.
		 *
		 * @param string $page
		 */
		public function admin_enqueue_scripts( $page ) {
			wp_enqueue_style( 'favo-admin-style', FAVO_URL . '/assets/css/favo-admin-style.css' );

			wp_enqueue_script( 'favo-admin-script', FAVO_URL . '/assets/js/favo-admin-script.js', array( 'jquery' ) );
		}

		/**
		 * Enqueue frontend scripts and styles.
		 */
		public function wp_enqueue_scripts() {
			if ( favo_setting( 'type_active' ) == 'text' ) {
				$on_val  = favo_setting( 'val_on' );
				$off_val = favo_setting( 'val_off' );
			} else {
				$on_val  = wp_get_attachment_url( favo_setting( 'image_val_on' ) );
				$off_val = wp_get_attachment_url( favo_setting( 'image_val_off' ) );
			}
			$favo_object = array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'button_type'            => favo_setting( 'type_active' ),
				'required_login'         => favo_setting( 'required_login' ),
				'is_login'               => is_user_logged_in(),
				'on_val'                 => $on_val,
				'off_val'                => $off_val,
				'add_success_message'    => favo_setting( 'add_success' ),
				'remove_success_message' => favo_setting( 'remove_success' ),
				'required_login_message' => favo_setting( 'required_login_message' ),
			);

			wp_enqueue_style( 'favo-style', FAVO_URL . '/assets/css/favo-style.css', array( 'storefront-icons', 'storefront-woocommerce-style' ) );

			wp_enqueue_script( 'favo-script', FAVO_URL . '/assets/js/favo-script.js', array( 'jquery' ) );
			wp_localize_script( 'favo-script', 'favo_object', $favo_object );
		}
	}

	new Favo();
}
