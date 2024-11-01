<?php
/**
 * The Template for displaying Staff content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/content-staff.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.14
 *
 * @version       0.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$staff_id = get_the_ID();

/*
|--------------------------------------------------------------------------
| Prepare Staff data
|--------------------------------------------------------------------------
*/
$staff_data = [
	'staff_id'  => $staff_id,
	'season_id' => sports_leagues()->season->get_season_id_maybe( $_GET, sports_leagues()->get_active_instance_season( $staff_id, 'staff' ) ), // phpcs:ignore WordPress.Security.NonceVerification
	'team_id'   => (int) get_post_meta( $staff_id, '_sl_current_team', true ),
];

$staff_data['team_title'] = $staff_data['team_id'] ? sports_leagues()->team->get_team_title_by_id( $staff_data['team_id'] ) : '';
$staff_data['team_link']  = $staff_data['team_id'] ? get_permalink( $staff_data['team_id'] ) : '';

/**
 * Hook: sports-leagues/tmpl-staff/before_wrapper
 *
 * @since 0.5.14
 *
 * @param int $staff_id
 */
do_action( 'sports-leagues/tmpl-staff/before_wrapper', $staff_id );
?>
<div class="anwp-b-wrap staff staff-page staff-id-<?php echo (int) $staff_id; ?>">
	<?php

	$staff_sections = [
		'header',
		'history',
		'description',
		'fixtures',
		'games',
	];

	/**
	 * Filter: sports-leagues/tmpl-staff/sections
	 *
	 * @since @since 0.5.14
	 *
	 * @param array $staff_sections
	 * @param array $staff_data
	 * @param int   $staff_id
	 */
	$staff_sections = apply_filters( 'sports-leagues/tmpl-staff/sections', $staff_sections, $staff_data, $staff_id );

	foreach ( $staff_sections as $section ) {
		sports_leagues()->load_partial( $staff_data, 'staff/staff-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: sports-leagues/tmpl-staff/after_wrapper
 *
 * @param int $staff_id
 *
 * @since @since 0.5.14
 */
do_action( 'sports-leagues/tmpl-staff/after_wrapper', $staff_id );
