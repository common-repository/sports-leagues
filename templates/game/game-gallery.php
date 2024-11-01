<?php
/**
 * The Template for displaying Game >> Gallery Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-gallery.php.
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
		'game_id' => '',
		'header'  => true,
	]
);

if ( empty( $data->game_id ) ) {
	return;
}

// Get Gallery Data
$gallery = get_post_meta( $data->game_id, '_sl_gallery', true );

if ( empty( $gallery ) || ! is_array( $gallery ) ) {
	return;
}

$gallery_notes = get_post_meta( $data->game_id, '_sl_gallery_notes', true );

wp_enqueue_script( 'anwp-sl-justified-gallery' );
wp_enqueue_script( 'anwp-sl-justified-gallery-modal' );
?>
<div class="anwp-section">
	<?php if ( sports_leagues()->string_to_bool( $data->header ) ) : ?>
		<div class="anwp-block-header"><?php echo esc_html( Sports_Leagues_Text::get_value( 'game__game_gallery', __( 'Gallery', 'sports-leagues' ) ) ); ?></div>
	<?php endif; ?>

	<div class="sl-not-ready anwp-sl-justified-gallery" id="game__gallery">
		<?php foreach ( $gallery as $image ) : ?>
			<a class="anwp-sl-justified-gallery-item" href="<?php echo esc_attr( $image ); ?>"><img width="200" height="200" src="<?php echo esc_url( $image ); ?>" alt=""></a>
		<?php endforeach; ?>
	</div>

	<?php if ( $gallery_notes ) : ?>
		<p class="mt-1 small text-muted"><?php echo wp_kses_post( $gallery_notes ); ?></p>
	<?php endif; ?>
</div>
