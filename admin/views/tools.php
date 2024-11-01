<?php
/**
 * Tools page for Sports Leagues
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

// phpcs:ignore WordPress.Security.NonceVerification
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
?>
<div class="anwp-b-wrap wrap" id="anwp-sl-import-wrapper">

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab text-dark <?php echo esc_attr( '' === $active_tab ? 'nav-tab-active' : '' ); ?>"
			href="<?php echo esc_url( admin_url( 'admin.php?page=sl-import-tool' ) ); ?>">
			<?php echo esc_html__( 'Batch Import', 'sports-leagues' ); ?>
		</a>
		<a class="nav-tab text-dark <?php echo esc_attr( 'csv-export' === $active_tab ? 'nav-tab-active' : '' ); ?>"
			href="<?php echo esc_url( admin_url( 'admin.php?page=sl-import-tool&tab=csv-export' ) ); ?>">
			<?php echo esc_html__( 'CSV Export', 'sports-leagues' ); ?>
		</a>
	</h2>

	<?php
	if ( '' === $active_tab ) :
		Sports_Leagues::include_file( 'admin/views/tools-import' );
	elseif ( 'csv-export' === $active_tab ) :
		Sports_Leagues::include_file( 'admin/views/tools-export' );
	endif;
	?>
</div>
