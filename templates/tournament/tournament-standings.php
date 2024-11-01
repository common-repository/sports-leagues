<?php
/**
 * The Template for displaying Tournament >> Standings.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/tournament/tournament-standings.php.
 *
 * @var object    $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.12.0
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
		'tournament_id' => '',
		'stage_id'      => '',
	]
);

if ( empty( $data->tournament_id ) ) {
	return;
}

$stage_standings_only = 'yes' === Sports_Leagues_Customizer::get_value( 'tournament', 'stage_standings_only' );
$tournament_obj       = sports_leagues()->tournament->get_tournament( $data->tournament_id );
$standings_all        = sports_leagues()->standing->get_standings();

if ( empty( $tournament_obj ) || empty( $standings_all ) ) {
	return;
}

$standings = [];

if ( ! empty( $tournament_obj->stages ) && is_array( $tournament_obj->stages ) ) {
	foreach ( $tournament_obj->stages as $stage ) {
		if ( empty( $stage->id ) ) {
			continue;
		}

		if ( 'group' === $stage->type && ! empty( $stage->groups ) && is_array( $stage->groups ) ) {
			foreach ( $stage->groups as $group ) {
				foreach ( $standings_all as $standing_data ) {
					if ( absint( $standing_data['stage_id'] ) === absint( $stage->id ) && absint( $group->id ) === absint( $standing_data['group_id'] ) ) {
						if ( ( $stage_standings_only && ( ! $data->stage_id || absint( $data->stage_id ) === absint( $stage->id ) ) ) || ! $stage_standings_only ) {
							$standings[] = [
								'group_title' => $group->title,
								'stage_title' => $stage->title,
								'id'          => $standing_data['id'],
							];
						}
					}
				}
			}
		}
	}
}

if ( empty( $standings ) ) {
	return;
}
?>
<div class="tournament__standings anwp-section">
	<?php
	sports_leagues()->load_partial(
		[
			'text'  => Sports_Leagues_Text::get_value( 'tournament__structure__standing_tables', __( 'Standing Tables', 'sports-leagues' ) ),
			'class' => 'mt-4',
		],
		'general/header'
	);

	foreach ( $standings as $standing ) {
		$shortcode_args = [
			'id'              => $standing['id'],
			'title'           => '',
			'context'         => 'stage',
			'subheader_text'  => $standing['group_title'] . ' - ' . $standing['stage_title'],
			'subheader_class' => 'my-2',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo sports_leagues()->template->shortcode_loader( 'standing', $shortcode_args );
	}
	?>
</div>
