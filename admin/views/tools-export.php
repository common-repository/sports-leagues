<?php
/**
 * Import Data page for Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.10.2
 *
 * @package    Sports_Leagues
 * @subpackage Sports_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}
?>

<h1 class="my-3">Export CSV files</h1>

<div class="d-inline-block">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=sl-import-tool&tab=csv-export&sl_export=players' ) ); ?>" class="button button-secondary anwp-w-300 py-2 my-2 text-center">Export Players</a>
</div>
<br>
