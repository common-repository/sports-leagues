<?php
/**
 * The Template for displaying No Data text.
 * Used in shortcodes.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/general/no-data.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.10.2
 *
 * @version       0.10.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'no_data_text' => '',
		'class'        => '',
	]
);

if ( empty( $data->no_data_text ) ) {
	return;
}
?>
<div class="anwp-b-wrap anwp-text-center anwp-bg-light py-3 anwp-sl-nodata <?php echo esc_attr( $data->class ); ?>">
	<span class="anwp-opacity-70"><?php echo esc_html( $data->no_data_text ); ?></span>
</div>
