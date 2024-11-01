<?php
/**
 * Shortcodes page for Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.5.5
 *
 * @package    Sports_Leagues
 * @subpackage Sports_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$available_tabs = [ 'basic', 'howto', 'builder' ];

// phpcs:ignore WordPress.Security.NonceVerification
$shortcode_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'basic';

if ( ! in_array( $shortcode_tab, $available_tabs, true ) ) {
	$shortcode_tab = 'basic';
}
?>
	<div class="anwp-b-wrap">
		<div class="inside px-3 pt-3">
			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sl-shortcodes' ) ); ?>"
					class="nav-tab <?php echo esc_attr( 'basic' === $shortcode_tab ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'Basic Shortcodes', 'sports-leagues' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sl-shortcodes&tab=builder' ) ); ?>"
					class="nav-tab <?php echo esc_attr( 'builder' === $shortcode_tab ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'Shortcode Builder', 'sports-leagues' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sl-shortcodes&tab=howto' ) ); ?>"
					class="nav-tab <?php echo esc_attr( 'howto' === $shortcode_tab ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'How To\'s', 'sports-leagues' ); ?></a>
				<?php
				do_action( 'sports-leagues/config/shortcode_extra_tabs' );
				?>
			</nav>
		</div>
	</div>
<?php
switch ( $shortcode_tab ) {

	case 'howto':
		Sports_Leagues::include_file( 'admin/views/shortcodes-howto' );
		break;

	case 'builder':
		Sports_Leagues::include_file( 'admin/views/shortcodes-builder' );
		break;

	default:
		Sports_Leagues::include_file( 'admin/views/shortcodes-basic' );
}
