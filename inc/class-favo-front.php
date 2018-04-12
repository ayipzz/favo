<?php
/**
 * Front Page Favo
 *
 * @package favo
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Favo_Front' ) ) {

	/**
	 *
	 * Class for add admin page on wp-admin
	 */
	class Favo_Front {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			if ( ! is_admin() ) :
				if ( favo_setting( 'enabled' ) == 'yes' ) {
					add_shortcode( 'favo_button', array( $this, 'shortcode' ) );
					add_shortcode( 'favo_list', array( $this, 'favorite_list' ) );
					add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
					$this->set_default_favo_button();
				}
			endif;
		}

		/**
		 * Set default favo button set
		 */
		public function set_default_favo_button() {
			if ( ! empty( favo_setting( 'display_on' ) ) && in_array( 'single_product', favo_setting( 'display_on' ) ) ) {
				add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'favo_add_button' ) );
			}
			if ( ! empty( favo_setting( 'display_on' ) ) && in_array( 'loop_product', favo_setting( 'display_on' ) ) ) {
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'favo_add_button' ), 15 );
			}
		}

		/**
		 * Enqueue frontend scripts and styles.
		 */
		public function wp_enqueue_scripts() {
			global $post;
			if ( is_woocommerce() || is_cart() || has_shortcode( $post->post_content, 'favo_list' ) ) {
				// load enqueue if favo enabled
				if ( favo_setting( 'enabled' ) == 'yes' ) {
					if ( favo_setting( 'type_active' ) == 'text' ) {
						$on_val  = favo_setting( 'val_on' );
						$off_val = favo_setting( 'val_off' );
					} else {
						$on_img_src = wp_get_attachment_image_src( favo_setting( 'image_val_on' ), 'thumbnail', false );
						$off_img_src = wp_get_attachment_image_src( favo_setting( 'image_val_off' ), 'thumbnail', false );
						$on_val  = ( !empty( $on_img_src[0] ) ) ? $on_img_src[0] : false;
						$off_val = ( !empty( $off_img_src[0] ) ) ? $off_img_src[0] : false;
					}
					$favo_object = array(
						'ajax_url'                      => admin_url( 'admin-ajax.php' ),
						'button_type'                   => favo_setting( 'type_active' ),
						'required_login'                => favo_setting( 'required_login' ),
						'is_login'                      => is_user_logged_in(),
						'on_val'                        => $on_val,
						'off_val'                       => $off_val,
						'favo_count'                    => favo_setting( 'favo_count' ),
						'enable_add_success_message'    => favo_setting( 'enable_add_success_message' ),
						'enable_remove_success_message' => favo_setting( 'enable_remove_success_message' ),
						'add_success_message'           => favo_setting( 'add_success' ),
						'remove_success_message'        => favo_setting( 'remove_success' ),
						'required_login_message'        => favo_setting( 'required_login_message' ),
					);

					wp_enqueue_style( 'favo-style', FAVO_URL . '/assets/css/favo-style.css' );
					wp_enqueue_script( 'favo-script', FAVO_URL . '/assets/js/favo-script.js', array( 'jquery' ) );
					wp_localize_script( 'favo-script', 'favo_object', $favo_object );
				}
			}
		}

		/**
		 * Create shortcode to dispaly favorit button
		 *
		 * @param array $attr shortcode attribute
		 *
		 * @version 1.0.0
		 */
		public function shortcode( $attr = array() ) {
			global $product;

			if ( $product ) :

				$class         = isset( $attr['class'] ) ? $attr['class'] : '';
				$icon_favorite = 'off';

				// set icon favorite
				if ( is_user_logged_in() && $this->get_favorite_by_user( $product->get_id(), get_current_user_id() ) ) {
					$icon_favorite = 'on';
				} else if ( isset( $_COOKIE['favo_product'] ) && in_array( $product->get_id(), json_decode( $_COOKIE['favo_product'] ) ) ) {
					$icon_favorite = 'on';
				}

				// set favo number
				$favo_count = '';
				if ( favo_setting( 'favo_count' ) == 'yes' ) {
					$count_favo = get_favo( $product->get_id() );
					if ( favo_setting( 'type_active' ) == 'text' ) {
						$favo_count = '(' . $count_favo . ')';
					} elseif ( favo_setting( 'type_active' ) == 'image' ) {
						$favo_count = '<span class="count">' . $count_favo . '</span>';
					}
					
				}

				$component = '<span class="favo ' . $class . '" data-product-id="' . $product->get_id() . '">';

				$component .= '<img src="' . FAVO_URL . '/assets/images/loading.gif" class="favo-loading" data-product-id="' . $product->get_id() . '" />';

				if ( favo_setting( 'type_active' ) == 'text' ) : // type text
					$component .= '<button type="button" class="favo-button ' . $icon_favorite . '" data-product-id="' . $product->get_id() . '">' . favo_setting( 'val_' . $icon_favorite ) . ' ' . $favo_count . '</button>';
				elseif ( favo_setting( 'type_active' ) == 'image' ) : // type image
					$on_img_src = wp_get_attachment_image_src( favo_setting( 'image_val_on' ), 'thumbnail', false );
					$off_img_src = wp_get_attachment_image_src( favo_setting( 'image_val_off' ), 'thumbnail', false );
					$on_val  = ( !empty( $on_img_src[0] ) ) ? $on_img_src[0] : false;
					$off_val = ( !empty( $off_img_src[0] ) ) ? $off_img_src[0] : false;

					$favo_class = $icon_favorite == 'on' ? $on_val : $off_val;
					$component .= '<span class="favo_button_icon">';
					$component .= '<img src="' . $favo_class . '" class="favo-button ' . $icon_favorite . '" data-product-id="' . $product->get_id() . '" />';
					$component .= $favo_count;
					$component .= '</span>';
				endif;

				$component .= '</span>';

				echo $component;

			endif;
		}

		/**
		 * get favorit by user_id
		 *
		 * @param int $product_id ID Product
		 * @param int $user_id ID User Current Login
		 *
		 * @version 1.0.0
		 */
		public function get_favorite_by_user( $product_id, $user_id ) {
			if ( is_user_logged_in() ) {
				$product_favo = get_post_meta( $product_id, 'favo', true );
				if ( $product_favo ) {
					$ex_product_favo = explode( ",", trim( $product_favo, "," ) );
					if ( $ex_product_favo ) {
						if ( array_search( $user_id, $ex_product_favo ) != -1 ) {
							return 1;
						} else {
							return 0;
						}
					} else {
						return 0;
					}
				} else return 0;
			}
		}

		/**
		 * Get all product favorite
		 *
		 * @return [type] [description]
		 */
		public function get_favorite_product_by_user( $user_id ) {
			global $wpdb;
			$favo_prepare = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s", "favo", '%,'.$wpdb->esc_like($user_id).',%' );
			$favo = $wpdb->get_results( $favo_prepare, ARRAY_A );
			return $favo;
		}

		public function favo_add_button() {
			echo do_shortcode( '[favo_button]' );
		}

		/**
		 * Display favorite list with shortcode
		 *
		 * @return [type] [description]
		 */
		public function favorite_list() {
			global $wpdb;

			$list_favorite = array();
			
			if( favo_setting( 'required_login' ) == 'yes' ) {
				if ( ! is_user_logged_in() ) {
					echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in.', 'woocommerce' ) );
					return;
				}
			}
			if ( is_user_logged_in() ) {
				if ( $get_favo = $this->get_favorite_product_by_user( get_current_user_id() ) ) {
					foreach ( $get_favo as $favo ) {
						array_push( $list_favorite, $favo['post_id'] );
					}
				}
			} elseif ( isset( $_COOKIE['favo_product'] ) ) {
				$list_favorite = json_decode( $_COOKIE['favo_product'] );
			}

			if ( count( $list_favorite ) > 0 ) {
				$args     = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'post__in'       => $list_favorite,
				);
				$products = new WP_Query( $args );
				if ( $products->have_posts() ) {
					woocommerce_product_loop_start();
					while ( $products->have_posts() ) :
						$products->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					woocommerce_product_loop_end();
					wp_reset_query();
				} else {
					do_action( 'woocommerce_no_products_found' );
				}
			} else {
				do_action( 'woocommerce_no_products_found' );
			}
		}
	}

	new Favo_Front();

}
