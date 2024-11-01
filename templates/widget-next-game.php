<?php
/**
 * The Template for displaying Next Game Widget.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/widget-next-game.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.6
 *
 * @version       0.12.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prevent errors with new params
$args = (object) wp_parse_args(
	$data,
	[
		'team_a'         => '',
		'team_b'         => '',
		'team_id'        => '',
		'tournament_id'  => '',
		'stage_id'       => '',
		'season_id'      => '',
		'show_team_name' => '',
		'game_link_text' => '',
		'exclude_ids'    => '',
		'include_ids'    => '',
		'limit'          => 1,
		'offset'         => '',
		'max_size'       => '',
		'transparent_bg' => '',
	]
);

$date_from = '';

if ( function_exists( 'current_datetime' ) && empty( $attr->include_ids ) ) {
	$date_from = current_datetime()->format( 'Y-m-d' );
}

// Get tournament games
$games = sports_leagues()->game->get_games_extended(
	[
		'tournament_id'  => $args->tournament_id,
		'stage_id'       => $args->stage_id,
		'season_id'      => $args->season_id,
		'finished'       => 0,
		'filter_by_team' => $args->team_id,
		'limit'          => $args->limit,
		'sort_by_date'   => 'asc',
		'exclude_ids'    => $args->exclude_ids,
		'include_ids'    => $args->include_ids,
		'offset'         => $args->offset,
		'date_from'      => $date_from,
		'team_a'         => $args->team_a,
		'team_b'         => $args->team_b,
	]
);

if ( empty( $games ) || empty( $games[0]->game_id ) ) {
	return;
}

$transparent_bg = Sports_Leagues::string_to_bool( $args->transparent_bg );

// Max Size
$image_size_style = '';

if ( absint( $args->max_size ) ) {
	$image_size_style = 'width: ' . absint( $args->max_size ) . 'px; height: ' . absint( $args->max_size ) . 'px;';
}
?>
<div class="anwp-b-wrap">
	<?php
	foreach ( $games as $widget_index => $widget_game ) :
		$data = sports_leagues()->game->prepare_tmpl_game_data( $widget_game );
		?>
		<div class="game-widget <?php echo $transparent_bg ? '' : 'anwp-bg-light'; ?> <?php echo esc_attr( $widget_index ? 'mt-2' : '' ); ?>"
			data-sl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>" data-sl-date-format="v3">

			<?php if ( intval( $data->venue_id ) ) : ?>
				<div class="game-widget__venue anwp-text-center anwp-opacity-70">
					<svg class="anwp-icon">
						<use xlink:href="#icon-location"></use>
					</svg>
					<?php
					echo esc_html( get_the_title( $data->venue_id ) );

					$venue_city = get_post_meta( $data->venue_id, '_sl_city', true );
					echo $venue_city ? esc_html( ', ' . $venue_city ) : '';
					?>
				</div>
			<?php endif; ?>

			<div class="game-widget__tournament anwp-text-center">
				<?php echo esc_html( sports_leagues()->tournament->get_title( $data->tournament_id ) ); ?><br>
				<?php echo esc_html( sports_leagues()->tournament->get_title( $data->stage_id ) ); ?>
			</div>

			<div class="game-widget__teams d-flex mt-3 mb-1">
				<div class="game-widget__team anwp-flex-1 d-flex flex-column align-items-center anwp-text-center anwp-min-width-0 px-1">
					<?php if ( Sports_Leagues::string_to_bool( $args->show_team_name ) ) : ?>
						<img loading="lazy" class="game-widget__team-logo anwp-object-contain mt-2 <?php echo $image_size_style ? '' : 'anwp-w-60 anwp-h-60'; ?>"
							src="<?php echo esc_url( $data->home_logo ); ?>"
							style="<?php echo esc_html( $image_size_style ); ?>"
							alt="<?php echo esc_attr( $data->home_title ); ?>">

						<div class="game-widget__team-title mt-1 d-inline-block text-truncate"><?php echo esc_html( $data->home_title ); ?></div>
					<?php else : ?>
						<img loading="lazy" class="game-widget__team-logo anwp-object-contain my-2 <?php echo $image_size_style ? '' : 'anwp-w-60 anwp-h-60'; ?>"
							data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( $data->home_title ); ?>"
							src="<?php echo esc_url( $data->home_logo ); ?>"
							style="<?php echo esc_html( $image_size_style ); ?>"
							alt="<?php echo esc_attr( $data->home_title ); ?>">
					<?php endif; ?>
				</div>
				<div class="anwp-flex-none align-self-center d-flex game-widget__scores">vs</div>
				<div class="game-widget__team anwp-flex-1 d-flex flex-column align-items-center anwp-text-center anwp-min-width-0 px-1">
					<?php if ( Sports_Leagues::string_to_bool( $args->show_team_name ) ) : ?>
						<img loading="lazy" class="game-widget__team-logo anwp-object-contain mt-2 <?php echo $image_size_style ? '' : 'anwp-w-60 anwp-h-60'; ?>"
							src="<?php echo esc_url( $data->away_logo ); ?>"
							style="<?php echo esc_html( $image_size_style ); ?>"
							alt="<?php echo esc_attr( $data->away_title ); ?>">

						<div class="game-widget__team-title mt-1 d-inline-block text-truncate"><?php echo esc_html( $data->away_title ); ?></div>
					<?php else : ?>
						<img loading="lazy" class="game-widget__team-logo anwp-object-contain my-2 <?php echo $image_size_style ? '' : 'anwp-w-60 anwp-h-60'; ?>"
							data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( $data->away_title ); ?>"
							src="<?php echo esc_url( $data->away_logo ); ?>"
							style="<?php echo esc_html( $image_size_style ); ?>"
							alt="<?php echo esc_attr( $data->away_title ); ?>">
					<?php endif; ?>
				</div>
			</div>

			<div class="game-widget__timer game-card__timer-static anwp-text-center mt-3">
				<div class="d-inline-block py-1 px-2 anwp-bg-white text-dark text-uppercase h5">
					<?php
					if ( $data->kickoff && '0000-00-00 00:00:00' !== $data->kickoff ) {
						$date_format = Sports_Leagues_Options::get_value( 'custom_game_date_format' ) ?: 'j M ';
						$time_format = Sports_Leagues_Options::get_value( 'custom_game_time_format' ) ?: get_option( 'time_format' );

						echo '<span class="game__date-formatted">' . esc_html( date_i18n( $date_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span><span class="mx-1"></span>';
						echo '<span class="game__time-formatted">' . esc_html( date_i18n( $time_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span>';
					}
					?>
				</div>
			</div>

			<?php if ( $args->game_link_text ) : ?>
				<div class="anwp-text-center anwp-game-preview-link <?php echo $transparent_bg ? '' : 'anwp-bg-light'; ?> mt-1">
					<a href="<?php echo esc_url( get_permalink( $data->game_id ) ); ?>" class="anwp-link-without-effects">
						<span class="d-inline-block"><?php echo esc_html( $args->game_link_text ); ?></span>
					</a>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
