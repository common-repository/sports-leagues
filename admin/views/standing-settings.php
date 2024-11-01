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
// Get available ranking rules
$rules_available = [
	'all_win',
	'ft_win',
	'ov_win',
	'pen_win',
	'draw',
	'sf',
	'sd',
	'ratio',
	'pts',
];
?>
<script type="text/javascript">
	var _slStandingRulesAvailable   = <?php echo wp_json_encode( $rules_available ); ?>;
	var _slStandingColumnsOrder     = <?php echo wp_json_encode( get_option( 'sl_standing_settings__col_order' ) ); ?>;
	var _slStandingColumnsOrderMini = <?php echo wp_json_encode( get_option( 'sl_standing_settings__col_order_mini' ) ); ?>;
	var _slStandingRules            = <?php echo wp_json_encode( get_option( 'sl_standing_settings__ranking' ) ); ?>;
	var _slStandingConfig           = <?php echo wp_json_encode( sports_leagues()->config->get_standing_config() ); ?>;
</script>
<div class="wrap anwp-b-wrap">
	<div class="mb-4 pb-1">
		<h1 class="mb-0"><?php echo esc_html__( 'Standing Settings', 'sports-leagues' ); ?></h1>
	</div>

	<div id="sl-app-standing-settings"></div>
</div>
