<?php
/**
 * Admin Template: Upsell Page
 *
 * @package tpc
 * @since 1.0.0
 * @version 1.0.0
 */

do_action( 'tpc_about_upsell_before' );
$upsell->render();
do_action( 'tpc_about_upsell_after' );
