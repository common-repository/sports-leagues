<?php
/**
 * The Template for displaying Staff >> Upcoming Games Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/staff/staff-fixtures.php.
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
		'staff_id'  => '',
		'season_id' => '',
		'header'    => true,
	]
);

if ( empty( $data->staff_id ) ) {
	return;
}

$staff_games = sports_leagues()->staff->get_staff_games(
	[
		'staff_id'     => $data->staff_id,
		'season_id'    => $data->season_id,
		'finished'     => 0,
		'sort_by_date' => 'asc',
	]
);

if ( empty( $staff_games ) ) {
	return;
}
?>
<div class="staff-fixtures anwp-section anwp-b-wrap">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'staff__fixtures__upcoming', __( 'Upcoming', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	$games_grouped = sports_leagues()->staff->get_staff_games_grouped( $staff_games, $data->staff_id );

	foreach ( $games_grouped as $official_group => $group_games_by_team ) :
		foreach ( $group_games_by_team as $team_id => $group_games ) {

			if ( '_' === mb_substr( $official_group, 0, 1 ) ) {
				$official_group = mb_substr( $official_group, 1 );
			}

			$team_obj = sports_leagues()->team->get_team_by_id( $team_id );

			ob_start();
			?>
			<div class="d-flex flex-wrap w-100 align-items-center">
				<span class="mr-auto"><?php echo esc_html( $official_group ); ?></span>
				<img src="<?php echo esc_url( $team_obj->logo ); ?>" alt="team logo" class="anwp-flex-none mx-2 anwp-object-contain anwp-w-25 anwp-h-25">
				<span class="anwp-text-sm"><?php echo esc_html( $team_obj->title ); ?></span>
			</div>
			<?php
			$subheader_text = ob_get_clean();

			sports_leagues()->load_partial(
				[
					'text'       => $subheader_text,
					'allow_html' => true,
					'class'      => 'mb-2 mt-2 staff__group-header',
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
		}

	endforeach;
	?>
</div>
