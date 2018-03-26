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

				add_shortcode( 'favo_button', array( $this, 'shortcode' ) );
				add_shortcode( 'favo_list', array( $this, 'favorite_list' ) );

				if ( is_favo_db_exist() == true && favo_setting( 'enabled' ) == 'yes' ) {
					if ( ! empty( favo_setting( 'display_on' ) ) && in_array( 'single_product', favo_setting( 'display_on' ) ) ) {
						add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'favo_single_product' ) );
					}
					if ( ! empty( favo_setting( 'display_on' ) ) && in_array( 'loop_product', favo_setting( 'display_on' ) ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'favo_single_product' ), 15 );
					}
				} else {

				}

			endif;
		}

		/**
		 * Create shortcode to dispaly favorit button
		 *
		 * @param array $attr shortcode attribute
		 *
		 * @version 1.0.0
		 */
		public function shortcode( $attr ) {
			global $product;

			if ( $product ) :

				$attr['class'] = isset( $attr['class'] ) ? $attr['class'] : '';
				$icon_favorite = 'off';

				// set icon favorite
				if ( isset( $_COOKIE['favo_product'] ) && in_array( $product->get_id(), json_decode( $_COOKIE['favo_product'] ) ) ) {
					$icon_favorite = 'on';
				} elseif ( $this->get_favorite_by_user( $product->get_id(), wp_get_current_user()->ID ) ) {
					$icon_favorite = 'on';
				}

				// set favo number
				$favo_count = '';
				if ( favo_setting( 'favo_count' ) == 'yes' ) {
					if ( favo_setting( 'type_active' ) == 'text' ) {
						$favo_count = '('. get_favo( $product->get_id() ) . ')';
					} else if ( favo_setting( 'type_active' ) == 'image' ) {
						$favo_count = '<span class="count">'. get_favo( $product->get_id() ) . '</span>';
					}
				}

				$component = '<span class="favo ' . $attr['class'] . '">';

				$component .= '<img src="' . FAVO_URL . '/assets/images/loading.gif" class="favo-loading" data-product-id="' . $product->get_id() . '" />';

				if ( favo_setting( 'type_active' ) == 'text' ) : // type text
					$component .= '<button type="button" class="favo-button ' . $icon_favorite . '" data-product-id="' . $product->get_id() . '">' . favo_setting( 'val_' . $icon_favorite ) . ' '. $favo_count .'</button>';
				elseif ( favo_setting( 'type_active' ) == 'image' ) : // type image
					$favo_class = $icon_favorite == 'on' ? favo_setting( 'image_val_on' ) : favo_setting( 'image_val_off' );
					$component .= '<span class="favo_button_icon">';
					$component .= '<img src="' . wp_get_attachment_url( $favo_class ) . '" class="favo-button ' . $icon_favorite . '" data-product-id="' . $product->get_id() . '" />';
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
			global $wpdb;
			if ( is_user_logged_in() ) {
				$prepare_favo = $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . FAVO_DB_NAME . ' WHERE product_id = %d AND user_id = %d', array( $product_id, $user_id ) );
				$favorite     = $wpdb->get_var( $prepare_favo );
				return $favorite;
			}
		}

		/**
		 * Get all product favorite
		 *
		 * @return [type] [description]
		 */
		public function get_favorite_product_by_user( $user_id, $result = '' ) {
			global $wpdb;
			if ( is_user_logged_in() ) {
				if ( $result == 'list' ) {
					$favorite = $wpdb->get_results( $wpdb->prepare( 'SELECT product_id FROM ' . FAVO_DB_NAME . ' WHERE user_id = %s', $user_id ), ARRAY_A );
				} else {
					$favorite = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . FAVO_DB_NAME . ' WHERE user_id = %s', $user_id ) );
				}
				return $favorite;
			}
		}

		public function favo_single_product() {
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

			if ( $this->get_favorite_product_by_user( wp_get_current_user()->ID ) ) {
				// get from DB
				if ( $get_favorite = $this->get_favorite_product_by_user( wp_get_current_user()->ID, 'list' ) ) {
					$list_favorite = array();
					foreach ( $get_favorite as $favorite ) {
						array_push( $list_favorite, $favorite['product_id'] );
					}
				}
			} elseif ( isset( $_COOKIE['favo_product'] ) ) {
				// get from cookie
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
