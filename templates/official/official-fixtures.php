<?php
/**
 * The Template for displaying Official >> Upcoming Games Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/official/official-fixtures.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.10.2
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
		'official_id' => '',
		'season_id'   => '',
		'header'      => true,
	]
);

if ( empty( $data->official_id ) ) {
	return;
}

$official_games = sports_leagues()->official->get_official_games(
	[
		'official_id'  => $data->official_id,
		'finished'     => 0,
		'sort_by_date' => 'asc',
		'season_id'    => $data->season_id,
	]
);

if ( empty( $official_games ) ) {
	return;
}
?>
<div class="official-fixtures anwp-section anwp-b-wrap">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'official__fixtures__upcoming', __( 'Upcoming', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	$games_grouped = sports_leagues()->official->get_official_games_grouped( $official_games, $data->official_id );

	foreach ( $games_grouped as $official_group => $group_games ) :

		if ( '_' === mb_substr( $official_group, 0, 1 ) ) {
			$official_group = mb_substr( $official_group, 1 );
		}

		sports_leagues()->load_partial(
			[
				'text'  => $official_group,
				'class' => 'mb-2 mt-2 official__group-header',
			],
			'general/subheader'
		);

		/*
		|--------------------------------------------------------------------
		| Assistant Referee Games
		|--------------------------------------------------------------------
		*/
		foreach ( $group_games as $group_game ) :
			$data = sports_leagues()->game->prepare_tmpl_game_data( $group_game );

			$data->tournament_logo = 1;
			sports_leagues()->load_partial( $data, 'game/game', 'slim' );

		endforeach;
	endforeach;
	?>
</div>
