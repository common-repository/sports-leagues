<?php
/**
 * The Template for displaying Header - 1st Level.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/general/header.php.
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
		'text'       => '',
		'class'      => '',
		'allow_html' => false,
	]
);

if ( empty( $data->text ) ) {
	return;
}
?>
<div class="anwp-block-header <?php echo esc_attr( $data->class ); ?>">
	<?php echo $data->allow_html ? $data->text : esc_html( $data->text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
