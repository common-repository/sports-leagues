<?php
/**
 * The Template for displaying Game >> Player Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-player-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.6.0
 *
 * @version       0.12.7
 */
// phpcs:disable WordPress.NamingConventions.ValidVariableName

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_team'   => '',
		'away_team'   => '',
		'home_logo'   => '',
		'away_logo'   => '',
		'home_title'  => '',
		'away_title'  => '',
		'game_id'     => '',
		'header'      => true,
		'prev_layout' => '',
	]
);

/*
|--------------------------------------------------------------------
| Try to load custom layout
|--------------------------------------------------------------------
*/
$custom_layout = Sports_Leagues_Customizer::get_value( 'layout', 'game_players_stats' );

if ( ! empty( $custom_layout ) && $data->prev_layout !== $custom_layout ) {
	$data->prev_layout = $custom_layout;
	return sports_leagues()->load_partial( $data, 'game/game-player-stats', $custom_layout );
}

/*
|--------------------------------------------------------------------
| Prepare Stats Data
|--------------------------------------------------------------------
*/
$stats_players_columns = json_decode( get_option( 'sl_columns_game' ) );

if ( empty( $stats_players_columns ) ) {
	return;
}

$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : '';

// Load previous version (before stat groups)
if ( empty( $stat_groups ) ) {
	return sports_leagues()->load_partial( $data, 'game/game-player-stats', 'v1' );
}

if ( ! in_array( 'custom', wp_list_pluck( $stat_groups, 'type' ), true ) && empty( $custom_layout ) ) {
	return sports_leagues()->load_partial( $data, 'game/game-player-stats', 'v2' );
}

return sports_leagues()->load_partial( $data, 'game/game-player-stats', 'v0' );
