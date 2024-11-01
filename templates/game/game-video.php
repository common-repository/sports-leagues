<?php
/**
 * The Template for displaying Game >> Video Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-video.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'video_source'    => '',
		'game_id'         => '',
		'video_media_url' => '',
		'video_id'        => '',
		'header'          => true,
	]
);

if ( ! in_array( $data->video_source, [ 'site', 'youtube', 'vimeo' ], true ) ) {
	return;
}

/**
 * Hook: sports-leagues/tmpl-game/video_before
 *
 * @param object $data Game data
 *
 * @since 0.1.0
 */
do_action( 'sports-leagues/tmpl-game/video_before', $data );

$video_info        = get_post_meta( $data->game_id, '_sl_video_info', true );
$additional_videos = get_post_meta( $data->game_id, '_sl_additional_videos', true );

wp_enqueue_script( 'plyr' );
wp_enqueue_style( 'plyr' );
?>
<div class="anwp-section">

	<?php if ( Sports_Leagues::string_to_bool( $data->header ) ) : ?>
		<div class="anwp-block-header">
			<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__video__video', __( 'Video', 'sports-leagues' ) ) ); ?>
		</div>
	<?php endif; ?>

	<div class="game__video mt-2">

		<?php if ( 'youtube' === Sports_Leagues_Options::get_value( 'preferred_video_player' ) || ( 'mixed' === Sports_Leagues_Options::get_value( 'preferred_video_player' ) && 'youtube' === $data->video_source && $data->video_id ) ) : ?>
			<div class="embed-responsive embed-responsive-16by9">
				<div id="anwp-sl-iframe-yt-match-video"
					data-video="<?php echo esc_attr( sports_leagues()->helper->get_youtube_id( $data->video_id ) ); ?>"
					data-origin="<?php echo esc_url( get_site_url() ); ?>"></div>
			</div>
		<?php else : ?>

			<?php if ( 'site' === $data->video_source && $data->video_media_url ) : ?>
				<video class="anwp-sl-video-player" playsinline controls>
					<source src="<?php echo esc_url( $data->video_media_url ); ?>" type="video/mp4">
				</video>
			<?php elseif ( 'youtube' === $data->video_source && $data->video_id ) : ?>
				<div class="anwp-sl-video-player" data-plyr-provider="youtube" data-plyr-embed-id="<?php echo esc_attr( $data->video_id ); ?>"></div>
			<?php elseif ( 'vimeo' === $data->video_source && $data->video_id ) : ?>
				<div class="anwp-sl-video-player" data-plyr-provider="vimeo" data-plyr-embed-id="<?php echo esc_attr( $data->video_id ); ?>"></div>
			<?php endif; ?>

		<?php endif; ?>
	</div>

	<?php if ( $video_info ) : ?>
		<div class="game__video-info mt-1"><?php echo esc_html( $video_info ); ?></div>
	<?php endif; ?>

	<?php
	/*
	|--------------------------------------------------------------------
	| Additional Videos
	|--------------------------------------------------------------------
	*/
	if ( ! empty( $additional_videos ) && is_array( $additional_videos ) ) :
		?>
		<div class="anwp-video-grid mt-2 d-flex flex-wrap no-gutters mx-n1">
			<?php
			foreach ( $additional_videos as $additional_video ) :

				if ( empty( $additional_video['video_source'] ) ) {
					continue;
				}

				$video_info = isset( $additional_video['video_info'] ) ? $additional_video['video_info'] : '';
				?>
				<div class="anwp-video-grid__item mt-3 anwp-col-sm-6 anwp-col-xl-4">
					<div class="anwp-video-grid__item-inner mx-1 mt-1">

						<?php
						if ( 'youtube' === Sports_Leagues_Options::get_value( 'preferred_video_player' ) || ( 'mixed' === Sports_Leagues_Options::get_value( 'preferred_video_player' ) && 'youtube' === $data->video_source && $data->video_id ) ) :
							$video_id = sports_leagues()->helper->get_youtube_id( $additional_video['video_id'] );
							?>
							<div class="embed-responsive embed-responsive-16by9 anwp-sl-yt-video"
								style="background-image: url('https://img.youtube.com/vi/<?php echo esc_attr( $video_id ); ?>/0.jpg')"
								data-video="<?php echo esc_attr( $video_id ); ?>">
							</div>
						<?php else : ?>

							<?php if ( 'site' === $additional_video['video_source'] && $additional_video['video_media_url'] ) : ?>
								<video class="anwp-sl-video-player" playsinline controls>
									<source src="<?php echo esc_url( $additional_video['video_media_url'] ); ?>" type="video/mp4">
								</video>
							<?php elseif ( 'youtube' === $additional_video['video_source'] && $additional_video['video_id'] ) : ?>
								<div class="anwp-sl-video-player" data-plyr-provider="youtube" data-plyr-embed-id="<?php echo esc_attr( $additional_video['video_id'] ); ?>"></div>
							<?php elseif ( 'vimeo' === $additional_video['video_source'] && $additional_video['video_id'] ) : ?>
								<div class="anwp-sl-video-player" data-plyr-provider="vimeo" data-plyr-embed-id="<?php echo esc_attr( $additional_video['video_id'] ); ?>"></div>
							<?php endif; ?>

							<?php if ( $video_info ) : ?>
								<div class="anwp-video-grid__item-info mt-1"><?php echo esc_html( $video_info ); ?></div>
							<?php endif; ?>

						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>


