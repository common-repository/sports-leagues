<?php
/**
 * The Template for displaying Venue >> Description Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/venue/venue-description.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.11.0
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
		'venue_id' => '',
	]
);

$post_content = get_post_meta( $data->venue_id, '_sl_description', true );

if ( ! $post_content ) {
	return;
}
?>
<div class="venue__description anwp-section">
	<?php echo wp_kses_post( wpautop( do_shortcode( $post_content ) ) ); ?>
</div>
