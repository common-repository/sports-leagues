<?php
/**
 * The Template for displaying Team >> Gallery Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-gallery.php.
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
		'team_id' => '',
		'header'  => true,
	]
);

if ( empty( $data->team_id ) ) {
	return;
}

// Get Gallery Data
$gallery = get_post_meta( $data->team_id, '_sl_gallery', true );

if ( empty( $gallery ) || ! is_array( $gallery ) ) {
	return;
}

$gallery_notes = get_post_meta( $data->team_id, '_sl_gallery_notes', true );

wp_enqueue_script( 'anwp-sl-justified-gallery' );
wp_enqueue_script( 'anwp-sl-justified-gallery-modal' );
?>
<div class="anwp-section team-gallery">
	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'team__team_gallery', __( 'Gallery', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="sl-not-ready anwp-sl-justified-gallery" id="team__gallery">
		<?php foreach ( $gallery as $image ) : ?>
			<a class="anwp-sl-justified-gallery-item" href="<?php echo esc_attr( $image ); ?>"><img width="200" height="200" src="<?php echo esc_url( $image ); ?>" alt=""></a>
		<?php endforeach; ?>
	</div>

	<?php if ( $gallery_notes ) : ?>
		<p class="team-gallery__notes mt-1 small text-muted"><?php echo wp_kses_post( $gallery_notes ); ?></p>
	<?php endif; ?>
</div>
