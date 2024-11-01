<?php
/**
 * The Template for displaying Player >> Gallery Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-gallery.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.7.0
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
		'player_id' => '',
		'header'    => true,
	]
);

if ( empty( $data->player_id ) ) {
	return;
}

// Get Gallery Data
$gallery = get_post_meta( $data->player_id, '_sl_gallery', true );

if ( empty( $gallery ) || ! is_array( $gallery ) ) {
	return;
}

$gallery_notes = get_post_meta( $data->player_id, '_sl_gallery_notes', true );

wp_enqueue_script( 'anwp-sl-justified-gallery' );
wp_enqueue_script( 'anwp-sl-justified-gallery-modal' );
?>
<div class="anwp-section">
	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'player__player_gallery', __( 'Gallery', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="sl-not-ready player-gallery anwp-sl-justified-gallery" id="player__gallery">
		<?php foreach ( $gallery as $image ) : ?>
			<a class="anwp-sl-justified-gallery-item" href="<?php echo esc_attr( $image ); ?>"><img width="200" height="200" src="<?php echo esc_url( $image ); ?>" alt=""></a>
		<?php endforeach; ?>
	</div>

	<?php if ( $gallery_notes ) : ?>
		<p class="mt-1 anwp-opacity-70 player-gallery__notes"><?php echo wp_kses_post( $gallery_notes ); ?></p>
	<?php endif; ?>
</div>
