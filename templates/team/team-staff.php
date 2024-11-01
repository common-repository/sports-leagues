<?php
/**
 * The Template for displaying Team >> Roster Staff Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-staff.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.14
 *
 * @version       0.11.0
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
	]
);

$team_post = get_post( $data->team_id );

/**
 * Hook: sports-leagues/tmpl-team/before_staff_roster
 *
 * @since 0.5.14
 *
 * @param WP_Post $team_post
 * @param integer $season_id
 */
do_action( 'sports-leagues/tmpl-team/before_staff_roster', $team_post, $data->season_id );

/**
 * Filter: sports-leagues/tmpl-team/render_staff_roster
 *
 * @since 0.5.14
 *
 * @param bool
 * @param WP_Post $team_post
 * @param integer $season_id
 */
if ( ! apply_filters( 'sports-leagues/tmpl-team/render_staff_roster', true, $team_post, $data->season_id ) ) {
	return;
}
?>
	<div class="team__roster anwp-section">

		<?php
		/**
		 * Filter: sports-leagues/tmpl-team/roster_layout
		 *
		 * @since 0.1.0
		 *
		 * @param WP_Post $team_post
		 * @param integer $season_id
		 */
		$roster_layout = apply_filters( 'sports-leagues/tmpl-team/roster_layout', Sports_Leagues_Customizer::get_value( 'roster', 'team_roster_layout' ), $team_post, $data->season_id );

		$loader_params = [
			'team_id'         => $data->team_id,
			'season_id'       => $data->season_id,
			'season_dropdown' => 'hide',
			'layout'          => $roster_layout,
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo sports_leagues()->template->shortcode_loader( 'staff-roster', $loader_params );
		?>
	</div>
<?php
/**
 * Hook: sports-leagues/tmpl-team/after_staff_roster
 *
 * @since 0.5.14
 *
 * @param WP_Post $team_post
 * @param integer $season_id
 */
do_action( 'sports-leagues/tmpl-team/after_staff_roster', $team_post, $data->season_id );
