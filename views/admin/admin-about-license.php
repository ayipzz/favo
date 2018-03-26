<?php
/**
 * Admin Template: License Form
 *
 * @package favo
 * @since 1.0.0
 * @version 1.0.0
 */

do_action( 'favo_about_license_before' );
$license_handler->render_form();
do_action( 'favo_about_license_after' );
