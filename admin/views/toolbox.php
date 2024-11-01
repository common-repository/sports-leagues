<?php
/**
 * Toolbox
 *
 * @link       https://anwp.pro
 * @since      0.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}

// phpcs:ignore WordPress.Security.NonceVerification
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';

/*
|--------------------------------------------------------------------
| Options
|--------------------------------------------------------------------
*/
$app_options = [
	'rest_root'   => esc_url_raw( rest_url() ),
	'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
	'spinner_url' => admin_url( 'images/spinner.gif' ),
	'sl_page_num' => absint( $_GET['sl_page_num'] ?? 10 ) ?: 10,
];
?>
<script type="text/javascript">
	window._slToolbox = <?php echo wp_json_encode( $app_options ); ?>;
</script>
<div class="wrap anwp-b-wrap">

	<?php if ( 'optimizer' === $active_tab ) : ?>
		<?php // Sports_Leagues::include_file( 'admin/views/toolbox--optimizer' ); ?>
	<?php else : ?>
		<?php Sports_Leagues::include_file( 'admin/views/toolbox--updater' ); ?>
	<?php endif; ?>

</div>
