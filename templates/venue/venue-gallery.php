<?php
/**
 * The Template for displaying Venue >> Gallery.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/venue/venue-gallery.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.11.0
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
		'venue_id' => '',
		'header'   => true,
	]
);

$venue = get_post( $data->venue_id );

if ( ! apply_filters( 'sports-leagues/tmpl-venue/render_gallery', true, $venue ) ) {
	return;
}


if ( empty( $venue->_sl_gallery ) || ! is_array( $venue->_sl_gallery ) ) {
	return;
}

wp_enqueue_script( 'anwp-sl-justified-gallery' );
wp_enqueue_script( 'anwp-sl-justified-gallery-modal' );
?>
<div class="venue__gallery-wrapper venue-gallery anwp-section">
	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'venue__venue_gallery', __( 'Venue Gallery', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}
	?>
	<div class="sl-not-ready anwp-sl-justified-gallery" id="venue__gallery">
		<?php foreach ( $venue->_sl_gallery as $image ) : ?>
			<a class="anwp-sl-justified-gallery-item" href="<?php echo esc_attr( $image ); ?>"><img width="200" height="200" alt="" src="<?php echo esc_url( $image ); ?>"></a>
		<?php endforeach; ?>
	</div>

	<?php if ( $venue->_sl_gallery_notes ) : ?>
		<p class="mt-1 small text-muted"><?php echo wp_kses_post( $venue->_sl_gallery_notes ); ?></p>
	<?php endif; ?>
</div>
