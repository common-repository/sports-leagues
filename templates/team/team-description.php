<?php
/**
 * The Template for displaying Team >> Description Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/team/team-description.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'description' => '',
	]
);

if ( ! trim( $data->description ) ) {
	return;
}
?>
<div class="team__description team-description anwp-section">
	<?php echo wp_kses_post( wpautop( do_shortcode( $data->description ) ) ); ?>
</div>
