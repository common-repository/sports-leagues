<?php
/**
 * The Template for displaying Data Error text.
 * Used in shortcodes.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/general/data-error.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.11.0
 *
 * @version       0.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'error_text' => '',
		'class'      => '',
	]
);

if ( empty( $data->error_text ) ) {
	return;
}
?>
<div class="anwp-b-wrap anwp-text-base anwp-text-center anwp-bg-light py-3 anwp-sl-nodata <?php echo esc_attr( $data->class ); ?>">
	<span class="anwp-opacity-70"><?php echo esc_html( $data->error_text ); ?></span>
</div>
