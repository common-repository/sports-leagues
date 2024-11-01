<?php
/**
 * The Template for displaying Game >> Latest Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-latest.php.
 *
 * @var object $data - Object with args.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         Sports-Leagues/Templates
 *
 * @version         0.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_team'  => '',
		'away_team'  => '',
		'home_logo'  => '',
		'away_logo'  => '',
		'home_title' => '',
		'away_title' => '',
		'kickoff'    => '',
		'header'     => true,
	]
);
?>
<div class="anwp-section">
	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'game__latest__latest_games', __( 'Latest Games', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->helper->render_team_header( $data->home_logo, $data->home_title, $data->home_team, true );

	$shortcode_attr = [
		'filter_by_team' => $data->home_team,
		'finished'       => 1,
		'limit'          => 5,
		'sort_by_date'   => 'desc',
		'class'          => '',
		'kickoff_before' => $data->kickoff,
	];

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->template->shortcode_loader( 'games', $shortcode_attr );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->helper->render_team_header( $data->away_logo, $data->away_title, $data->away_team, false );

	$shortcode_attr = [
		'filter_by_team' => $data->away_team,
		'finished'       => 1,
		'limit'          => 5,
		'sort_by_date'   => 'desc',
		'class'          => '',
		'kickoff_before' => $data->kickoff,
	];

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->template->shortcode_loader( 'games', $shortcode_attr );
	?>
</div>
