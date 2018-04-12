<?php
/**
 *  Update Favorite Status
 */
add_action( 'wp_ajax_update_favo', 'ajax_update_favo_callback' );
add_action( 'wp_ajax_nopriv_update_favo', 'ajax_update_favo_callback' );
function ajax_update_favo_callback() {
	global $wpdb;

	$product_id   = sanitize_text_field( $_POST['product_id'] );
	$fav_action   = sanitize_text_field( $_POST['fav_action'] );
	$fav_user     = ( is_user_logged_in() ) ? get_current_user_id() : favo_client_ip();
	
	$product_favo = get_post_meta( $product_id, 'favo', true );
	if ( $product_favo ) {
		if ( $fav_action == 'insert' ) {
			$new_product_favo = $product_favo . $fav_user . ',';
		} elseif ( $fav_action == 'delete' ) {
			$ex_product_favo = explode( ",", trim( $product_favo, "," ) );
			if ( count( $ex_product_favo ) == 1 ) {
				$new_product_favo = str_replace( ','.$fav_user.',', '', $product_favo );
			} else {
				$new_product_favo = str_replace( $fav_user.',', '', $product_favo );
			}
		}	
	} else if ( $fav_action == 'insert' ) {
		$new_product_favo = ',' . $fav_user . ',';
	}
	update_post_meta( $product_id, 'favo', $new_product_favo );
	if ( favo_setting( 'favo_count' ) == 'yes' ) {
		echo get_favo( $product_id );
	} else {
		echo '';
	}

	wp_die();
}

/**
 * Get client IP Address
 */
function favo_client_ip() {
	$ipaddress = '';
	if ( getenv( 'HTTP_CLIENT_IP' ) ) {
		$ipaddress = getenv( 'HTTP_CLIENT_IP' );
	} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
		$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
	} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
		$ipaddress = getenv( 'HTTP_X_FORWARDED' );
	} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
		$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
	} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
		$ipaddress = getenv( 'HTTP_FORWARDED' );
	} elseif ( getenv( 'REMOTE_ADDR' ) ) {
		$ipaddress = getenv( 'REMOTE_ADDR' );
	} else {
		$ipaddress = 'UNKNOWN';
	}
	return $ipaddress;
}

/**
 * get favorit by product_id
 *
 * @param int $product_id ID Product
 *
 * @version 1.0.0
 */
function get_favo( $product_id ) {
	$product_favo = get_post_meta( $product_id, 'favo', true );
	if ( $product_favo ) {
		$ex_product_favo = explode( ",", trim( $product_favo, "," ) );
		return count( $ex_product_favo );
	} else return '';
}

/**
 * Create default page to display list favorite product
 *
 * @return [type] [description]
 */
function favo_create_page() {
	$favo_list_page = array(
		'post_title'   => 'Favorite List',
		'post_type'    => 'page',
		'post_content' => '[favo_list]',
		'post_status'  => 'publish',
		'post_author'  => 1,
	);
	wp_insert_post( $favo_list_page );
}

/**
 * Setup favo default setting
 */
function favo_default_setting() {
	$default_settings = array(
		'enabled'                       => 'yes',
		'favo_count'                    => 'no',
		'type_active'                   => 'text',
		'enable_add_success_message'    => 'no',
		'enable_remove_success_message' => 'no',
		'display_on'                    => array(
			'single_product',
			'loop_product',
		),
		'button'                        => array(
			'text' => array(
				'val_on'  => 'Remove From Favorite',
				'val_off' => 'Add to Favorite',
			),
		),
		'messages'                      => array(
			'add_success_message'    => 'Product Added to favorite list',
			'remove_success_message' => 'Product Removed from favorite list',
		),
	);
	update_option( 'favo_settings', $default_settings );
}

/**
 * Get favorit setting drom db option
 *
 * @param  string $values option key to display
 * @return string
 */
function favo_setting( $values ) {
	$favo_opt = get_option( 'favo_settings' );
	$val_on   = '';
	$val_off  = '';

	if ( $values == 'val_on' ) {
		$val = $favo_opt['button']['text']['val_on'];
	} elseif ( $values == 'val_off' ) {
		$val = $favo_opt['button']['text']['val_off'];
	} elseif ( $values == 'image_val_on' ) {
		$val = $favo_opt['button']['image']['val_on'];
	} elseif ( $values == 'image_val_off' ) {
		$val = $favo_opt['button']['image']['val_off'];
	} elseif ( $values == 'add_success' ) {
		$val = $favo_opt['messages']['add_success_message'];
	} elseif ( $values == 'remove_success' ) {
		$val = $favo_opt['messages']['remove_success_message'];
	} elseif ( $values == 'required_login_message' ) {
		$val = $favo_opt['messages']['required_login_message'];
	} else {
		$val = $favo_opt[ $values ];
	}

	return $val;
}
