<?php
/**
 * The Template for displaying Player >> Played Games Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-games.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.9.3
 *
 * @version       0.12.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'season_id' => '',
		'player_id' => '',
		'header'    => true,
	]
);

if ( empty( $data->player_id ) && empty( $data->season_id ) ) {
	return;
}

$game_ids = sports_leagues()->player->get_player_games( $data->player_id, $data->season_id );

if ( empty( $game_ids ) ) {
	return;
}

?>
<div class="player__played anwp-section">

	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'player__player_games__played_games', __( 'Played Games', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	$loader_params = [
		'include_ids'  => implode( ',', $game_ids ),
		'sort_by_date' => 'asc',
	];

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->template->shortcode_loader( 'games', $loader_params );
	?>
</div>
