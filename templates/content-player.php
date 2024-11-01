<?php
/**
 * The Template for displaying player content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/content-player.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.10.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$player_id = get_the_ID();

/*
|--------------------------------------------------------------------------
| Prepare player data
|--------------------------------------------------------------------------
*/
$player_data = [
	'player_id'        => $player_id,
	'season_id'        => sports_leagues()->season->get_season_id_maybe( $_GET, sports_leagues()->get_active_instance_season( $player_id, 'player' ) ), // phpcs:ignore WordPress.Security.NonceVerification
	'team_id'          => (int) get_post_meta( $player_id, '_sl_current_team', true ),
	'national_team_id' => (int) get_post_meta( $player_id, '_sl_national_team', true ),
];

$player_data['team_title'] = $player_data['team_id'] ? sports_leagues()->team->get_team_title_by_id( $player_data['team_id'] ) : '';
$player_data['team_link']  = $player_data['team_id'] ? get_permalink( $player_data['team_id'] ) : '';

$player_data['national_team_title'] = $player_data['national_team_id'] ? sports_leagues()->team->get_team_title_by_id( $player_data['national_team_id'] ) : '';
$player_data['national_team_link']  = $player_data['national_team_id'] ? get_permalink( $player_data['national_team_id'] ) : '';

/**
 * Hook: sports-leagues/tmpl-player/before_wrapper
 *
 * @since 0.1.0
 *
 * @param int $player_id
 */
do_action( 'sports-leagues/tmpl-player/before_wrapper', $player_id );
?>
<div class="anwp-b-wrap player player-page player-id-<?php echo (int) $player_id; ?>">
	<?php

	$player_sections = [
		'header',
		'description',
		'total-stats',
		'stats',
		'games',
		'missed',
		'gallery',
	];

	/**
	 * Filter: sports-leagues/tmpl-player/sections
	 *
	 * @since 0.1.0
	 *
	 * @param array $player_sections
	 * @param array $data
	 * @param int   $player_id
	 */
	$player_sections = apply_filters( 'sports-leagues/tmpl-player/sections', $player_sections, $player_data, $player_id );

	foreach ( $player_sections as $section ) {
		sports_leagues()->load_partial( $player_data, 'player/player-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: sports-leagues/tmpl-player/after_wrapper
 *
 * @since 0.1.0
 *
 * @param int $player_id
 */
do_action( 'sports-leagues/tmpl-player/after_wrapper', $player_id );
