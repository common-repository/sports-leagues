<?php
/**
 * The Template for displaying Venue >> Upcoming Games.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/venue/venue-upcoming.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.11.0
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
		'venue_id'  => '',
		'header'    => true,
		'season_id' => '',
	]
);

if ( ! apply_filters( 'sports-leagues/tmpl-venue/render_upcoming_games', true, $data->venue_id ) ) {
	return;
}

$games_args = [
	'venue_id'     => $data->venue_id,
	'season_id'    => $data->season_id,
	'finished'     => 0,
	'limit'        => 5,
	'sort_by_date' => 'asc',
];

$games_html = sports_leagues()->template->shortcode_loader( 'games', $games_args );

if ( empty( $games_html ) ) {
	return;
}
?>
<div class="anwp-section">
	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'venue__upcoming_games', __( 'Upcoming Games', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	echo $games_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</div>
