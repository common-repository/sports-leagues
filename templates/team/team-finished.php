<?php
/**
 * The Template for displaying Team >> Finished Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-finished.php.
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
 * Hook: sports-leagues/tmpl-team/before_finished
 *
 * @since 0.5.5
 *
 * @param WP_Post $team_post
 * @param integer $season_id
 */
do_action( 'sports-leagues/tmpl-team/before_finished', $team_post, $data->season_id );
?>
<div class="team__finished anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'team__finished__finished_games', __( 'Finished Games', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	/*
	|--------------------------------------------------------------------
	| Finished Matches
	|--------------------------------------------------------------------
	*/
	$shortcode_loader = [
		'filter_by_team' => sports_leagues()->team->get_subteam_ids( $data->team_id ),
		'season_id'      => $data->season_id,
		'finished'       => 1,
		'limit'          => 10,
		'sort_by_date'   => 'desc',
		'show_load_more' => true,
	];

	$games_output = sports_leagues()->template->shortcode_loader( 'games', $shortcode_loader );

	if ( $games_output ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $games_output;
	} else {
		sports_leagues()->load_partial(
			[
				'no_data_text' => Sports_Leagues_Text::get_value( 'team__finished__no_games', __( 'No games', 'sports-leagues' ) ),
			],
			'general/no-data'
		);
	}
	?>
</div>
