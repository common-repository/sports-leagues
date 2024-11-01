<?php
/**
 * The Template for displaying Staff >> Description Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/staff/staff-description.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.14
 *
 * @version       0.5.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'staff_id' => '',
	]
);

$post_content = get_post_meta( $data->staff_id, '_sl_bio', true );

if ( ! $post_content ) {
	return;
}
?>
<div class="staff__description staff-section anwp-section">
	<?php echo wp_kses_post( wpautop( do_shortcode( $post_content ) ) ); ?>
</div>
