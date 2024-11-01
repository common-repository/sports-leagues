<?php
/**
 * The Template for displaying Game >> Summary Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-summary.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.5.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'summary' => '',
	]
);

if ( empty( $data->summary ) ) {
	return;
}

/**
 * Hook: sports-leagues/tmpl-game/summary_before
 *
 * @param object $data Game data
 *
 * @since 0.1.0
 */
do_action( 'sports-leagues/tmpl-game/summary_before', $data );
?>
<div class="anwp-section">
	<div class="anwp-block-header">
		<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__summary__game_summary', __( 'Game Summary', 'sports-leagues' ) ) ); ?>
	</div>

	<div class="game__summary">
		<?php echo wp_kses_post( wpautop( do_shortcode( $data->summary ) ) ); ?>
	</div>
</div>


