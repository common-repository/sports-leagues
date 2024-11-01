<?php
/**
 * The Template for displaying Game content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/content-game.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.9.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// phpcs:disable WordPress.NamingConventions

$game_post = get_post();

// Check fixed state
if ( 'yes' !== $game_post->_sl_fixed ) {
	return '';
}

// Prepare Game data
$game_data = sports_leagues()->game->get_game_data( $game_post->ID );

/**
 * Hook: sports-leagues/tmpl-game/before_wrapper
 *
 * @since 0.1.0
 *
 * @param WP_Post $game_post
 */
do_action( 'sports-leagues/tmpl-game/before_wrapper', $game_post );
?>
<div class="anwp-b-wrap game game__page game-<?php echo (int) $game_post->ID; ?>" data-sl-game-id="<?php echo (int) $game_post->ID; ?>">
	<?php

	// Get game data to render
	$data = sports_leagues()->game->prepare_tmpl_game_data( $game_data, [] );

	$data->permalink = empty( $data->permalink ) ? get_permalink( $data->game_id ) : $data->permalink;
	$data->context   = 'game';

	/*
	|--------------------------------------------------------------------
	| Attach game meta
	|--------------------------------------------------------------------
	*/
	$data->summary         = get_post_meta( $game_post->ID, '_sl_summary', true );
	$data->aggtext         = get_post_meta( $game_post->ID, '_sl_aggtext', true );
	$data->attendance      = get_post_meta( $game_post->ID, '_sl_attendance', true );
	$data->video_source    = get_post_meta( $game_post->ID, '_sl_video_source', true );
	$data->video_media_url = get_post_meta( $game_post->ID, '_sl_video_media_url', true );
	$data->video_id        = get_post_meta( $game_post->ID, '_sl_video_id', true );
	$data->players_home    = get_post_meta( $game_post->ID, '_sl_players_home', true );
	$data->players_away    = get_post_meta( $game_post->ID, '_sl_players_away', true );
	$data->staff_home      = get_post_meta( $game_post->ID, '_sl_staff_home', true );
	$data->staff_away      = get_post_meta( $game_post->ID, '_sl_staff_away', true );

	// Prepare custom numbers
	$data->custom_numbers = json_decode( get_post_meta( $game_post->ID, '_sl_custom_numbers', true ) );

	if ( null === $data->custom_numbers ) {
		$data->custom_numbers = [];
	}

	/*
	|--------------------------------------------------------------------
	| Attach team stats
	|--------------------------------------------------------------------
	*/
	$data->team_stats_home = json_decode( get_post_meta( $game_post->ID, '_sl_team_stats_home', true ) );
	$data->team_stats_away = json_decode( get_post_meta( $game_post->ID, '_sl_team_stats_away', true ) );

	if ( null === $data->team_stats_home ) {
		$data->team_stats_home = (object) [];
	}

	if ( null === $data->team_stats_away ) {
		$data->team_stats_away = (object) [];
	}

	/**
	 * Hook: sports-leagues/tmpl-game/before_header
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Post $game_post
	 */
	do_action( 'sports-leagues/tmpl-game/before_header', $game_post, $data );

	/**
	 * Filter: sports-leagues/tmpl-game/render_header
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Post $game_post
	 */
	if ( apply_filters( 'sports-leagues/tmpl-game/render_header', true, $game_post ) ) {
		sports_leagues()->load_partial( $data, 'game/game' );
	}

	/**
	 * Hook: sports-leagues/tmpl-game/after_header
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Post $game_post
	 */
	do_action( 'sports-leagues/tmpl-game/after_header', $game_post, $data );

	$sections = [
		'summary',
		'players',
		'staff',
		'team-stats',
		'player-stats',
		'missing',
		'video',
		'gallery',
		'latest',
	];

	/**
	 * Filter: sports-leagues/tmpl-game/sections
	 *
	 * @since 0.1.0
	 *
	 * @param array   $sections
	 * @param array   $data
	 * @param WP_Post $game_post
	 */
	$sections = apply_filters( 'sports-leagues/tmpl-game/sections', $sections, $data, $game_post );

	foreach ( $sections as $section ) {
		sports_leagues()->load_partial( $data, 'game/game-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: sports-leagues/tmpl-game/after_wrapper
 *
 * @since 0.1.0
 *
 * @param WP_Post $game_post
 */
do_action( 'sports-leagues/tmpl-game/after_wrapper', $game_post );
