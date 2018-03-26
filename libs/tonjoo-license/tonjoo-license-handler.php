<?php

if ( ! defined( 'TONJOO_LICENSE_OPTION_NAME' ) ) {
	define( 'TONJOO_LICENSE_OPTION_NAME', 'tonjoo_plugin_license' );
}

if ( ! class_exists( 'Tonjoo_License_Handler' ) ) {

	require_once 'tonjoo-license-api.php';

	/**
	 * Tonjoo Plugin License Handler
	 */
	class Tonjoo_License_Handler {

		/**
		 * Plugin update name
		 *
		 * @var string
		 */
		public $plugin;

		/**
		 * Plugin main file path
		 *
		 * @var string
		 */
		public $plugin_path;

		/**
		 * Default args for license data
		 *
		 * @var string
		 */
		public $default_args;

		/**
		 * Tonjoo license API object
		 *
		 * @var object
		 */
		public $license;

		/**
		 * Max attempt number for license check
		 *
		 * @var int
		 */
		public $max_attempt;

		/**
		 * License check interval
		 *
		 * @var int
		 */
		public $check_interval;

		/**
		 * License form url
		 *
		 * @var string
		 */
		public $license_form;

		/**
		 * Constructor
		 *
		 * @param string $plugin_name Plugin update name.
		 * @param string $plugin_path __FILE__.
		 */
		public function __construct( $plugin_name = '', $plugin_path = '' ) {
			add_action( 'init', array( $this, 'init_option' ), 20 );
			add_action( 'init', array( $this, 'check_status' ), 25 );
			add_action( 'init', array( $this, 'hide_notice' ), 30 );
			add_action( 'init', array( $this, 'init_updater' ), 35 );
			$this->plugin       = $plugin_name;
			$this->plugin_path  = $plugin_path;
			$this->default_args = array(
				'active'    => false,
				'key'       => '',
				'type'      => '-',
				'expiry'    => '-',
				'last_check' => time(),
				'check_attempt' => 0,
				'notice_activate'  => true,
				'notice_expired'   => true,
				'notice_week_to_expired' => true,
			);
			$this->license      = new Tonjoo_License_API( $this->plugin );
			$this->max_attempt   = 3;
			$this->check_interval = DAY_IN_SECONDS; // a day after last check.
			add_action( 'wp_ajax_tj_activate_plugin_' . $this->plugin, array( $this, 'action_activation' ) );
			add_action( 'wp_ajax_tj_deactivate_plugin_' . $this->plugin, array( $this, 'action_deactivation' ) );
		}

		/**
		 * Init option
		 */
		public function init_option() {
			if ( false === get_option( TONJOO_LICENSE_OPTION_NAME ) ) {
				update_option( TONJOO_LICENSE_OPTION_NAME, array() );
			}
			$licenses = get_option( TONJOO_LICENSE_OPTION_NAME, array() );
			if ( ! isset( $licenses[ $this->plugin ] ) ) {
				$licenses[ $this->plugin ] = $this->default_args;
				update_option( TONJOO_LICENSE_OPTION_NAME, $licenses );
			}
		}

		/**
		 * Hide admin notice forever.
		 */
		public function hide_notice() {
			if ( isset( $_GET['tj_license'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['tj_license'] ) ), 'hide_notice_' . $this->plugin ) ) { // Input var okay.
				$license = $this->get_license_status();
				if ( isset( $license[ 'notice_' . sanitize_text_field( wp_unslash( $_GET['hide'] ) ) ] ) ) { // Input var okay.
					$license[ 'notice_' . sanitize_text_field( wp_unslash( $_GET['hide'] ) ) ] = false; // Input var okay.
					$this->set_license_status( $license );
				}
				if ( isset( $_GET['ref'] ) ) { // Input var okay.
					wp_safe_redirect( esc_url_raw( wp_unslash( $_GET['ref'] ) ) ); // Input var okay.
				} else {
					wp_safe_redirect( admin_url() );
				}
			}
		}

		/**
		 * Init plugin updater
		 */
		public function init_updater() {
			if ( $this->is_license_active() ) {
				$license = $this->get_license_status();
				$tes = $this->license->load_updater( $license['key'], $this->plugin_path, $this->check_interval );
			}
		}

		/**
		 * Set plugin license form path
		 *
		 * @param string $url The url.
		 */
		public function set_license_form_url( $url = '' ) {
			$this->license_form = $url;
		}

		/**
		 * Get current URL
		 *
		 * @return string Current URL.
		 */
		private function get_current_url() {
			return ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		/**
		 * Show admin notices
		 */
		public function show_notice() {
			$license = $this->get_license_status();
			$plugin = get_plugin_data( $this->plugin_path );
			if ( ! $this->is_license_active() ) {
				if ( $this->is_license_expired() && isset( $plugin['notice_expired'] ) && true === $plugin['notice_expired'] ) {
					?>
						<div class="notice notice-warning is-dismissible">
							<?php if ( 'trial' === $license['type'] ) : ?>
								<p><?php printf( __( 'Your trial for <strong>%s</strong> has ended.', 'tpc' ), esc_html( $plugin['Name'] ) ); ?></p>
							<?php else : ?>
								<p><?php printf( __( 'Your license for <strong>%s</strong> is expired.', 'tpc' ), esc_html( $plugin['Name'] ) ); ?></p>
							<?php endif; ?>
							<p>
								<?php if ( ! empty( $this->license_form ) ) : ?>
									<a href="<?php echo esc_url( $this->license_form ); ?>" class="button-primary"><?php esc_html_e( 'Renew License', 'tpc' ); ?></a>
								<?php endif; ?>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( '?hide=expired&ref=' . $this->get_current_url() ), 'hide_notice_' . $this->plugin, 'tj_license' ) ); ?>" class="button"><?php esc_html_e( "Don't show again", 'tpc' ); ?></a>
							</p>
						</div>
					<?php
				} elseif ( isset( $license['notice_activate'] ) && true === $license['notice_activate'] ) {
					?>
						<div class="notice notice-warning is-dismissible">
							<p><?php printf( __( 'Please activate your <strong>%s</strong> license.', 'tpc' ), esc_html( $plugin['Name'] ) ); ?></p>
							<p>
								<?php if ( ! empty( $this->license_form ) ) : ?>
									<a href="<?php echo esc_url( $this->license_form ); ?>" class="button-primary"><?php esc_html_e( 'Activate License', 'tpc' ); ?></a>
								<?php endif; ?>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( '?hide=activate&ref=' . $this->get_current_url() ), 'hide_notice_' . $this->plugin, 'tj_license' ) ); ?>" class="button"><?php esc_html_e( "Don't show again", 'tpc' ); ?></a>
							</p>
						</div>
					<?php
				}
			} else {
				if ( 'trial' !== $license['type'] && ( $license['expiry'] - time() ) < WEEK_IN_SECONDS && isset( $license['notice_week_to_expired'] ) && true === $license['notice_week_to_expired'] ) {
					?>
						<div class="notice notice-warning is-dismissible">
							<p><?php printf( __( 'Your license for <strong>%s</strong> will ended soon.', 'tpc' ), esc_html( $plugin['Name'] ) ); ?></p>
							<p>
								<?php if ( ! empty( $this->license_form ) ) : ?>
									<a href="<?php echo esc_url( $this->license_form ); ?>" class="button-primary"><?php esc_html_e( 'Activate License', 'tpc' ); ?></a>
								<?php endif; ?>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( '?hide=week_to_expired&ref=' . $this->get_current_url() ), 'hide_notice_' . $this->plugin, 'tj_license' ) ); ?>" class="button"><?php esc_html_e( "Don't show again", 'tpc' ); ?></a>
							</p>
						</div>
					<?php
				}
			}
		}

		/**
		 * Scheduled check license status on server
		 */
		public function check_status() {
			$license = $this->get_license_status();

			// forget it if license key is empty.
			if ( empty( $license['key'] ) ) {
				return;
			}

			// current status on website is must active.
			if ( true === $license['active'] ) {

				// check if current attempt is below max.
				if ( $this->max_attempt > $license['check_attempt'] ) {
					if ( ( '-' !== $license['expiry'] && time() > (int) $license['expiry'] ) || ( abs( $license['last_check'] - time() ) > $this->check_interval ) ) {
						$status = $this->license->status( $license['key'] );
						if ( true === $status['status'] ) {
							if ( strtotime( $status['data']->validUntil ) < time() ) { // expired.
								$args = array(
									'active'    => false,
									'key'       => $license['key'],
									'type'      => $status['data']->licenseType,
									'expiry'    => strtotime( $status['data']->validUntil ),
									'last_check' => time(),
									'check_attempt'   => 0,
									'notices'   => $license['notices'],
								);
							} else {
								$args = array(
									'active'    => true,
									'key'       => $license['key'],
									'type'      => $status['data']->licenseType,
									'expiry'    => strtotime( $status['data']->validUntil ),
									'last_check' => time(),
									'check_attempt'   => 0,
									'notices'   => $license['notices'],
								);
							}
						} else {
							if ( "Can't connect to server" !== $status['data'] ) {
								$args = array(
									'key'           => $license['key'],
									'active'        => false,
									'last_check'    => time(),
									'check_attempt' => 0,
									'notices'   => $license['notices'],
								);
							} else { // can't connect to server.
								$args = array(
									'key'           => $license['key'],
									'active'        => $license['active'],
									'type'          => $license['type'],
									'expiry'        => $license['expiry'],
									'last_check'    => time(),
									'check_attempt' => $license['check_attempt'] + 1,
									'notices'   => $license['notices'],
								);
							}
						}
					} else {
						return;
					}

					// if the attempt is reached max, then set plugin to deactive.
				} else {
					$args = array(
						'active'    => false,
						'type'      => $license['type'],
						'expiry'    => $license['expiry'],
						'last_check' => time(),
						'check_attempt'   => 0,
						'notices'   => $license['notices'],
					);
				}
				$this->set_license_status( $args );
			}
		}

		/**
		 * Get current plugin license status on database
		 *
		 * @return array License status.
		 */
		public function get_license_status() {
			$licenses = get_option( TONJOO_LICENSE_OPTION_NAME );
			return $licenses[ $this->plugin ];
		}

		/**
		 * Set plugin license status to database
		 *
		 * @param array $args License array.
		 */
		public function set_license_status( $args = array() ) {
			$licenses = get_option( TONJOO_LICENSE_OPTION_NAME );
			$args = wp_parse_args( $args, $this->default_args );
			$licenses[ $this->plugin ] = $args;
			update_option( TONJOO_LICENSE_OPTION_NAME, $licenses );
		}

		/**
		 * Render license activation form
		 */
		public function render_form() {
			$plugin_info    = get_plugin_data( $this->plugin_path );
			$license_info   = $this->get_license_status();
			$is_active      = $this->is_license_active();
			$is_expired     = $this->is_license_expired();
			include 'view/license-form.php';
		}

		/**
		 * Check if plugin is active
		 *
		 * @return boolean License is active or not.
		 */
		public function is_license_active() {
			$license = $this->get_license_status();
			return $license['active'];
		}

		/**
		 * Check if license is expired
		 *
		 * @return boolean Check license expired or not.
		 */
		public function is_license_expired() {
			$license = $this->get_license_status();
			return ( '-' !== $license['expiry'] && time() > $license['expiry'] && ! $license['active'] );
		}

		/**
		 * Get local time from timestamps
		 *
		 * @param  integer $time Timestamps.
		 * @return string        Current time in local format.
		 */
		public function get_local_time( $time = 0 ) {
			if ( 0 === $time ) {
				$time = time();
			}
			$offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
			return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time + $offset );
		}

		/**
		 * License activation handler
		 */
		public function action_activation() {
			$response = array(
				'status'        => false,
				'message'       => __( 'Error: Authentication Failed. Please try again', 'tpc' ),
				'last_check'    => $this->get_local_time(),
			);
			$current = $this->get_license_status();
			if ( isset( $_POST['tj_license'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tj_license'] ) ), 'tonjoo-activate-license' ) ) { // Input var okay.
				if ( isset( $_POST['key'] ) ) { // Input var okay.
					$activation = $this->license->activate( sanitize_text_field( wp_unslash( $_POST['key'] ) ) ); // Input var okay.
					if ( true === $activation['status'] ) {
						$status = $this->license->status( sanitize_text_field( wp_unslash( $_POST['key'] ) ) ); // Input var okay.
						if ( true === $status['status'] ) {
							if ( strtotime( $status['data']->validUntil ) < time() ) { // expired.
								$args = array(
									'active'    => false,
									'last_check' => time(),
									'notices'   => $license['notices'],
								);
								$response = array(
									'status'        => false,
									'message'       => sprintf( __( 'Your license has expired at %s', 'tpc' ), $this->get_local_time( strtotime( $status['data']->validUntil ) ) ),
									'last_check'    => $this->get_local_time( $args['last_check'] ),
								);
							} else {
								$args = array(
									'active'    => true,
									'key'       => sanitize_text_field( wp_unslash( $_POST['key'] ) ), // Input var okay.
									'type'      => $status['data']->licenseType,
									'expiry'    => strtotime( $status['data']->validUntil ),
									'last_check' => time(),
									'check_attempt' => 0,
								);
								$response = array(
									'status'        => true,
									'message'       => __( 'Activation Success', 'tpc' ),
									'last_check'    => $this->get_local_time( $args['last_check'] ),
								);
							}
						} else {
							$this->license->deactivate( sanitize_text_field( wp_unslash( $_POST['key'] ) ) ); // Input var okay.
							$args = array(
								'last_check' => time(),
								'notices'   => $license['notices'],
							);
							$response = array(
								'status'        => false,
								'message'       => $status['data'],
								'last_check'    => $this->get_local_time( $args['last_check'] ),
							);
						}
					} else {
						$args = array(
							'last_check' => time(),
							'notices'   => $license['notices'],
						);
						$response = array(
							'status'        => false,
							'message'       => $activation['data'],
							'last_check'    => $this->get_local_time( $args['last_check'] ),
						);
					}
				} else {
					$args = array(
						'last_check' => time(),
						'notices'   => $license['notices'],
					);
					$response = array(
						'status'        => false,
						'message'       => __( 'License key is empty', 'tpc' ),
						'last_check'    => $this->get_local_time(),
					);
				}
			}
			$this->set_license_status( $args );
			wp_send_json( $response );
		}

		/**
		 * License deactivation handler
		 */
		public function action_deactivation() {
			$response = array(
				'status'        => false,
				'message'       => __( 'Error: Authentication Failed. Please try again', 'tpc' ),
				'last_check'    => $this->get_local_time(),
			);
			$current = $this->get_license_status();
			if ( isset( $_POST['tj_license'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tj_license'] ) ), 'tonjoo-deactivate-license' ) ) { // Input var okay.
				if ( isset( $_POST['key'] ) ) { // Input var okay.
					$deactivation = $this->license->deactivate( sanitize_text_field( wp_unslash( $_POST['key'] ) ) ); // Input var okay.
					if ( true === $deactivation['status'] ) {
						$args = array(
							'last_check' => time(),
						);
						$response = array(
							'status'        => true,
							'message'       => __( 'Deactivation Success', 'tpc' ),
							'last_check'    => $this->get_local_time( $args['last_check'] ),
						);
					} else {
						$args = $current;
						$args['last_check'] = time();
						$response = array(
							'status'        => false,
							'message'       => $deactivation['data'],
							'last_check'    => $this->get_local_time( $args['last_check'] ),
						);
					}
				} else {
					$args = array(
						'last_check' => time(),
					);
					$response = array(
						'status'        => false,
						'message'       => __( 'License key is empty', 'tpc' ),
						'last_check'    => $this->get_local_time(),
					);
				}
			}
			$this->set_license_status( $args );
			wp_send_json( $response );
		}

	}

}
