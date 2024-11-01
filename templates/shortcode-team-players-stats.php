<?php
/**
 * The Template for displaying Shortcode >> Team Players Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-team-players-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.6.2
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'season_id'   => '',
		'team_id'     => '',
		'prev_layout' => '',
		'header'      => true,
	]
);

/*
|--------------------------------------------------------------------
| Try to load custom layout
|--------------------------------------------------------------------
*/
$custom_layout = Sports_Leagues_Customizer::get_value( 'layout', 'team_players_stats' );

if ( ! empty( $custom_layout ) && $data->prev_layout !== $custom_layout ) {
	$data->prev_layout = $custom_layout;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->template->shortcode_loader( 'team-players-stats--' . sanitize_key( $custom_layout ), $data );
	return;
}

$stats_columns_game   = json_decode( get_option( 'sl_columns_game' ) );
$stats_columns_season = json_decode( get_option( 'sl_columns_season' ) );

if ( empty( $stats_columns_season ) || empty( $stats_columns_game ) ) {
	return;
}

$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : '';

// Load previous version (before stat groups)
if ( empty( $stat_groups ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->template->shortcode_loader( 'team-players-stats--v1', $data );
	return;
}

if ( ! in_array( 'custom', wp_list_pluck( $stat_groups, 'type' ), true ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->template->shortcode_loader( 'team-players-stats--v2', $data );
	return;
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo sports_leagues()->template->shortcode_loader( 'team-players-stats--v0', $data );
