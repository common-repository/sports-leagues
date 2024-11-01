<?php
/**
 * The Template for displaying venue content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/content-venue.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.2
 *
 * @version       0.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$venue_data = [
	'venue_id'  => get_the_ID(),
	'season_id' => sports_leagues()->season->get_season_id_maybe( $_GET, sports_leagues()->get_active_instance_season( get_the_ID(), 'venue' ) ), // phpcs:ignore WordPress.Security.NonceVerification
];
?>
<div class="anwp-b-wrap venue venue-page">

	<?php
	$venue_sections = [
		'header',
		'description',
		'upcoming',
		'finished',
		'gallery',
		'map',
	];

	/**
	 * Filter: sports-leagues/tmpl-venue/sections
	 *
	 * @since 0.1.0
	 *
	 * @param array   $team_sections
	 * @param array   $data
	 */
	$venue_sections = apply_filters( 'sports-leagues/tmpl-venue/sections', $venue_sections, $venue_data );

	foreach ( $venue_sections as $section ) {
		sports_leagues()->load_partial( $venue_data, 'venue/venue-' . sanitize_key( $section ) );
	}
	?>
</div>
