<?php
/**
 * The Template for displaying Tournament List Shortcode.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-tournament-list.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.17
 *
 * @version       0.5.17
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'status'       => '',
		'sort_by_date' => '',
		'limit'        => 0,
		'exclude_ids'  => '',
		'include_ids'  => '',
		'date_from'    => '',
		'date_to'      => '',
	]
);

$tournaments = sports_leagues()->tournament->get_tournaments_extended( $data );

if ( empty( $tournaments ) ) {
	return;
}
?>
<div class="anwp-b-wrap tournament__list tournament-list--shortcode">
	<?php
	foreach ( $tournaments as $tournament ) {
		$shortcode_args = [
			'title_as_link' => 1,
			'tournament_id' => $tournament->ID,
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo sports_leagues()->template->shortcode_loader( 'tournament-header', $shortcode_args );
	}
	?>
</div>
