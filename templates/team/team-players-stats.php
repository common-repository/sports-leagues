<?php
/**
 * The Template for displaying Team >> Player Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-players-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.6.1
 *
 * @version       0.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'season_dropdown' => '',
		'season_id'       => '',
		'team_id'         => '',
	]
);

/**
 * Hook: sports-leagues/tmpl-team/team_players_stats_before
 *
 * @param object $data Team data
 *
 * @since 0.6.1
 */
do_action( 'sports-leagues/tmpl-team/team_players_stats_before', $data );
?>
<div class="anwp-section team__team-players-stats">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->template->shortcode_loader( 'team-players-stats', $data );
	?>
</div>
