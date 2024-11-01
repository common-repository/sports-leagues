<?php
/**
 * The Template for displaying Player >> Description Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-description.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
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
		'player_id' => '',
	]
);

$post_content = get_post_meta( $data->player_id, '_sl_bio', true );

if ( ! $post_content ) {
	return;
}
?>
<div class="player__description player-description player-section anwp-section">
	<?php echo wp_kses_post( wpautop( do_shortcode( $post_content ) ) ); ?>
</div>
