<?php
/**
 * Dashboard page for Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.11.0
 *
 * @package    Sports_Leagues
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}
?>

<div class="wrap anwp-b-wrap">
	<div id="sl-admin-dashboard"></div>
</div>
