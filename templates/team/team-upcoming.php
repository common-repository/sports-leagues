<?php
/**
 * The Template for displaying Team >> Upcoming Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-upcoming.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.5
 *
 * @version       0.12.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'team_id'   => '',
		'season_id' => '',
		'header'    => true,
	]
);

$team_post = get_post( $data->team_id );

/**
 * Hook: sports-leagues/tmpl-team/before_upcoming
 *
 * @since 0.5.5
 *
 * @param WP_Post $team_post
 * @param integer $data->season_id
 */
do_action( 'sports-leagues/tmpl-team/before_upcoming', $team_post, $data->season_id );
?>
<div class="team__upcoming anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'team__upcoming__upcoming', __( 'Upcoming', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	/**
	 * Filter: sports-leagues/tmpl-team/upcoming_limit
	 *
	 * @param int     $limit
	 * @param WP_Post $team_post
	 * @param integer $season_id
	 *
	 * @since 0.5.5
	 */
	$games_limit = apply_filters( 'sports-leagues/tmpl-team/upcoming_limit', 10, $team_post, $data->season_id );

	$loader_params = [
		'filter_by_team' => sports_leagues()->team->get_subteam_ids( $data->team_id ),
		'finished'       => 0,
		'season_id'      => $data->season_id,
		'limit'          => $games_limit,
		'sort_by_date'   => 'asc',
		'class'          => 'mt-2',
		'show_load_more' => true,
	];

	$games_output = sports_leagues()->template->shortcode_loader( 'games', $loader_params );

	if ( $games_output ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $games_output;
	} else {
		sports_leagues()->load_partial(
			[
				'no_data_text' => Sports_Leagues_Text::get_value( 'team__upcoming__no_games', __( 'No games', 'sports-leagues' ) ),
			],
			'general/no-data'
		);
	}
	?>
</div>
