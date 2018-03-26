<?php
/**
 *  Update Favorite Status
 */
add_action( 'wp_ajax_update_favo', 'ajax_update_favo_callback' );
add_action( 'wp_ajax_nopriv_update_favo', 'ajax_update_favo_callback' );
function ajax_update_favo_callback() {
	global $wpdb;

		$product_id = sanitize_text_field( $_POST['product_id'] );
	$fav_action     = sanitize_text_field( $_POST['fav_action'] );
	$fav_date       = date( 'Y-m-d H:i:s' );
	$fav_user       = ( is_user_logged_in() ) ? wp_get_current_user()->ID : null;
	$fav_ip         = favo_client_ip();

	if ( $fav_action == 'insert' ) {
		$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . FAVO_DB_NAME . '(date_add,product_id,user_id,ipaddress) VALUES(%s,%d,%s,%s)', array( $fav_date, $product_id, $fav_user, $fav_ip ) ) );
	} elseif ( $fav_action == 'delete' ) {
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . FAVO_DB_NAME . ' WHERE product_id = %d AND ipaddress = %s AND user_id = %s', array( $product_id, $fav_ip, $fav_user ) ) );
	}

	$favo = get_favo( $product_id );

	echo $favo;

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
	global $wpdb;
	$favorite = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . FAVO_DB_NAME . ' WHERE product_id = %d', array( $product_id ) ) );
	return $favorite;
}

/**
 * function to check favo db is exist
 */
function is_favo_db_exist() {
	global $wpdb;

	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . FAVO_DB_NAME . "'" ) != $wpdb->prefix . 'favo' ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Method for handle what first plugin doing after active favo plugin
 *
 * @return [type] [description]
 */
function favo_install() {
	global $wpdb;
	global $favo_db_version;

		// Create Table
	$charset_collate = $wpdb->get_charset_collate();
	$sql             = 'CREATE TABLE ' . FAVO_DB_NAME . " (
        `ID` bigint(20) NOT NULL AUTO_INCREMENT,
        `date_add` datetime NOT NULL,
        `product_id` bigint(20) NOT NULL,
        `user_id` bigint(20) DEFAULT NULL,
        `ipaddress` varchar(30) NOT NULL,
        PRIMARY KEY  (`ID`)
    ) $charset_collate;";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'favo_db_version', $favo_db_version );
}

/**
 * Create default page to display list favorite product
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
	wp_insert_post($favo_list_page);
}

/**
 * Setup favo default setting
 */
function favo_default_setting() {
	$default_settings = array(
		'enabled'     => 'yes',
		'favo_count'  => 'no',
		'type_active' => 'text',
		'display_on'  => array(
			'single_product',
			'loop_product',
		),
		'button'      => array(
			'text' => array(
				'val_on'  => 'Remove From Favorite',
				'val_off' => 'Add to Favorite',
			),
		),
		'messages'    => array(
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
	} else {
		$val = $favo_opt[ $values ];
	}

	return $val;
}
