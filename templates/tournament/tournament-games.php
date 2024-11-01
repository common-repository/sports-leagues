<?php
/**
 * The Template for displaying Tournament >> Games.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/tournament/tournament-games.php.
 *
 * @var object    $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.12.0
 *
 * @version       0.12.0
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

$tournament_obj = sports_leagues()->tournament->get_tournament( $data->tournament_id );

if ( empty( $tournament_obj ) ) {
	return;
}

$games = sports_leagues()->tournament->get_tournament_games( $data->tournament_id );
?>
<div class="tournament__games anwp-section">

	<?php if ( count( $tournament_obj->stages ) > 1 ) : ?>
		<div class="anwp-sl-stages d-flex flex-wrap mx-n1">
			<div class="anwp-sl-stages__item anwp-border anwp-border-light px-3 m-1 anwp-rounded <?php echo empty( $data->stage_id ) ? 'anwp-sl-stages__item--active anwp-bg-gray-light' : 'anwp-cursor-pointer'; ?>"
				data-href="<?php echo esc_url( get_permalink( $data->tournament_id ) ); ?>">
				<?php echo esc_html( Sports_Leagues_Text::get_value( 'tournament__tabs__all', __( 'All', 'sports-leagues' ) ) ); ?>
			</div>

			<?php foreach ( $tournament_obj->stages as $tournament_stage ) : ?>
				<?php if ( ! empty( $tournament_stage->id ) && ! empty( $tournament_stage->title ) ) : ?>
					<div class="anwp-sl-stages__item anwp-border anwp-border-light px-3 m-1 anwp-rounded <?php echo $tournament_stage->id === $data->stage_id ? 'anwp-sl-stages__item--active anwp-bg-gray-light' : 'anwp-cursor-pointer'; ?>"
						data-href="<?php echo esc_url( get_permalink( $tournament_stage->id ) ); ?>">
						<?php echo esc_html( $tournament_stage->title ); ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	endif;

	/*
	|--------------------------------------------------------------------
	| Latest Scores
	|--------------------------------------------------------------------
	*/
	$shortcode_attr = [
		'finished'       => 1,
		'limit'          => 10,
		'sort_by_date'   => 'desc',
		'group_by'       => 'round_stage',
		'header_style'   => 'subheader',
		'header_class'   => 'my-1',
		'show_load_more' => true,
		'tournament_id'  => $data->tournament_id,
		'stage_id'       => $data->stage_id,
	];

	$output_latest_scores = sports_leagues()->template->shortcode_loader( 'games', $shortcode_attr );

	if ( ! empty( $output_latest_scores ) ) {
		sports_leagues()->load_partial(
			[
				'text'  => Sports_Leagues_Text::get_value( 'tournament__structure__latest_scores', __( 'Latest Scores', 'sports-leagues' ) ),
				'class' => 'mt-4',
			],
			'general/header'
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output_latest_scores;
	}

	/*
	|--------------------------------------------------------------------
	| Upcoming Games
	|--------------------------------------------------------------------
	*/
	$shortcode_attr = [
		'finished'       => 0,
		'limit'          => 10,
		'group_by'       => 'round_stage',
		'header_style'   => 'subheader',
		'header_class'   => 'my-1',
		'sort_by_date'   => 'asc',
		'show_load_more' => true,
		'tournament_id'  => $data->tournament_id,
		'stage_id'       => $data->stage_id,
	];

	$output_html_up = sports_leagues()->template->shortcode_loader( 'games', $shortcode_attr );

	if ( ! empty( $output_html_up ) ) {
		sports_leagues()->load_partial(
			[
				'text'  => Sports_Leagues_Text::get_value( 'tournament__structure__upcoming_games', __( 'Upcoming Games', 'sports-leagues' ) ),
				'class' => 'mt-4',
			],
			'general/header'
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output_html_up;
	}
	?>

</div>
