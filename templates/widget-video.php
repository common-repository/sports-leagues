<?php
/**
 * The Template for displaying Video Widget.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/widget-video.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.7.2
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prevent errors with new params
$args = (object) wp_parse_args(
	$data,
	[
		'team_id'       => '',
		'tournament_id' => '',
		'stage_id'      => '',
		'include_ids'   => '',
	]
);

// Get tournament games
$games = sports_leagues()->game->get_games_extended(
	[
		'tournament_id'  => $args->tournament_id,
		'stage_id'       => $args->stage_id,
		'finished'       => 1,
		'filter_by_team' => $args->team_id,
		'sort_by_date'   => 'desc',
		'include_ids'    => $args->include_ids,
	],
	'ids'
);

if ( empty( $games ) ) {
	return;
}

$game_id_with_video = '';
$available_game_ids = sports_leagues()->game->get_games_with_video();

foreach ( $games as $game_id ) {

	if ( ! in_array( absint( $game_id ), $available_game_ids, true ) ) {
		continue;
	}

	$video_source = get_post_meta( $game_id, '_sl_video_source', true );

	if ( ( in_array( $video_source, [ 'youtube', 'vimeo' ], true ) && get_post_meta( $game_id, '_sl_video_id', true ) )
			|| ( 'site' === $video_source && get_post_meta( $game_id, '_sl_video_media_url', true ) ) ) {

		$game_id_with_video = $game_id;
		break;
	}
}

if ( empty( $game_id_with_video ) ) {
	return;
}

// Prepare video data
$video_info      = get_post_meta( $game_id_with_video, '_sl_video_info', true );
$video_source    = get_post_meta( $game_id_with_video, '_sl_video_source', true );
$video_id        = get_post_meta( $game_id_with_video, '_sl_video_id', true );
$video_media_url = get_post_meta( $game_id_with_video, '_sl_video_media_url', true );

wp_enqueue_script( 'plyr' );
wp_enqueue_style( 'plyr' );
?>
<div class="anwp-b-wrap">
	<div class="anwp-video-module">
		<div class="anwp-video-grid__item-inner">

			<?php
			if ( 'youtube' === Sports_Leagues_Options::get_value( 'preferred_video_player' ) || ( 'mixed' === Sports_Leagues_Options::get_value( 'preferred_video_player' ) && 'youtube' === $video_source && $video_id ) ) :
				$video_id = sports_leagues()->helper->get_youtube_id( $video_id );
				?>
				<div class="embed-responsive embed-responsive-16by9 anwp-sl-yt-video"
					style="background-image: url('https://img.youtube.com/vi/<?php echo esc_attr( $video_id ); ?>/0.jpg')"
					data-video="<?php echo esc_attr( $video_id ); ?>">
				</div>
			<?php else : ?>

				<?php if ( 'site' === $video_source && $video_media_url ) : ?>
					<video class="anwp-sl-video-player" playsinline controls>
						<source src="<?php echo esc_url( $video_media_url ); ?>" type="video/mp4">
					</video>
				<?php elseif ( 'youtube' === $video_source && $video_id ) : ?>
					<div class="anwp-sl-video-player" data-plyr-provider="youtube" data-plyr-embed-id="<?php echo esc_attr( $video_id ); ?>"></div>
				<?php elseif ( 'vimeo' === $video_source && $video_id ) : ?>
					<div class="anwp-sl-video-player" data-plyr-provider="vimeo" data-plyr-embed-id="<?php echo esc_attr( $video_id ); ?>"></div>
				<?php endif; ?>

			<?php endif; ?>

			<?php if ( $video_info ) : ?>
				<div class="anwp-video-grid__item-info mt-1"><?php echo esc_html( $video_info ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
