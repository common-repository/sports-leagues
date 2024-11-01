<?php
/**
 * The Template for displaying team content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/content-team.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prepare tmpl data
$team   = get_post();
$prefix = '_sl_';
$data   = [];

$fields = [
	'abbr',
	'description',
	'city',
	'nationality',
	'address',
	'website',
	'founded',
	'venue',
	'twitter',
	'youtube',
	'facebook',
	'instagram',
	'vk',
	'tiktok',
	'linkedin',
	'discord',
	'twitch',
	'conference',
	'division',
];

foreach ( $fields as $field ) {
	$data[ $field ] = $team->{$prefix . $field};
}

/**
 * Filter: sports-leagues/tmpl-team/data_fields
 *
 * @since 0.1.0
 *
 * @param array   $data
 * @param WP_Post $team
 */
$data = apply_filters( 'sports-leagues/tmpl-team/data_fields', $data, $team );

$data['team_id']   = $team->ID;
$data['season_id'] = sports_leagues()->season->get_season_id_maybe( $_GET, sports_leagues()->get_active_instance_season( $team->ID, 'team' ) ); // phpcs:ignore WordPress.Security.NonceVerification

/**
 * Hook: sports-leagues/tmpl-team/before_wrapper
 *
 * @since 0.1.0
 *
 * @param WP_Post $team
 */
do_action( 'sports-leagues/tmpl-team/before_wrapper', $team );
?>
<div class="anwp-b-wrap team team-page team-<?php echo (int) $team->ID; ?>">
	<?php
	$team_sections = [
		'header',
		'description',
		'upcoming',
		'finished',
		'roster',
		'players-stats',
		'staff',
		'gallery',
	];

	/**
	 * Filter: sports-leagues/tmpl-team/sections
	 *
	 * @since 0.1.0
	 *
	 * @param array   $team_sections
	 * @param array   $data
	 */
	$team_sections = apply_filters( 'sports-leagues/tmpl-team/sections', $team_sections, $data );

	foreach ( $team_sections as $section ) {
		sports_leagues()->load_partial( $data, 'team/team-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: sports-leagues/tmpl-team/after_wrapper
 *
 * @since 0.1.0
 *
 * @param WP_Post $team
 */
do_action( 'sports-leagues/tmpl-team/after_wrapper', $team );
