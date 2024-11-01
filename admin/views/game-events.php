<?php
/**
 * Game Events
 *
 * @link       https://anwp.pro
 * @since      0.7.1
 *
 * @package    Sports_Leagues
 * @subpackage Sports_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Must check that the user has the required capability
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}

$app_id = apply_filters( 'sports-leagues/event-config/vue_app_id', 'sl-admin-events' );
?>
<div class="wrap anwp-b-wrap">
	<div class="mb-4 pb-1">
		<h1 class="mb-0"><?php echo esc_html__( 'Sports Leagues', 'sports-leagues' ) . ' :: ' . esc_html__( 'Game Events Configurator', 'sports-leagues' ); ?></h1>
	</div>
	<div class="my-2 p-3 anwp-border anwp-border-blue-600 anwp-bg-blue-100 d-flex">
		<svg class="anwp-icon d-inline-block anwp-icon--s24 mr-2"><use xlink:href="#icon-info"></use></svg>
		<div class="anwp-text-sm">
			Setting up a sports game event requires a bit of planning. The most common are <b>substitution</b>, <b>penalty card</b>, and <b>goal</b>.
			<br>
			Substitution allows players to be exchanged during the game, while penalty cards are used to penalize players for misconduct or rule violations. Goals provide a way for players to score points to win the game.		</div>
	</div>
	<div id="<?php echo esc_attr( $app_id ); ?>"></div>

	<?php do_action( 'sports-leagues/event-config/after_config' ); ?>
</div>
