<?php
/**
 * The Template for displaying Official content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/content-official.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.13
 *
 * @version       0.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$official_id = get_the_ID();

/*
|--------------------------------------------------------------------------
| Prepare official data
|--------------------------------------------------------------------------
*/
$official_data = [
	'official_id' => $official_id,
	'season_id'   => sports_leagues()->season->get_season_id_maybe( $_GET, sports_leagues()->get_active_instance_season( $official_id, 'official' ) ), // phpcs:ignore WordPress.Security.NonceVerification
];

/**
 * Hook: sports-leagues/tmpl-official/before_wrapper
 *
 * @since 0.5.13
 *
 * @param int $official_id
 */
do_action( 'sports-leagues/tmpl-official/before_wrapper', $official_id );
?>
<div class="anwp-b-wrap official official-page official-id-<?php echo (int) $official_id; ?>">
	<?php

	$official_sections = [
		'header',
		'description',
		'fixtures',
		'games',
	];

	/**
	 * Filter: sports-leagues/tmpl-official/sections
	 *
	 * @since @since 0.5.13
	 *
	 * @param array $official_sections
	 * @param array $data
	 * @param int   $official_id
	 */
	$official_sections = apply_filters( 'sports-leagues/tmpl-official/sections', $official_sections, $official_data, $official_id );

	foreach ( $official_sections as $section ) {
		sports_leagues()->load_partial( $official_data, 'official/official-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: sports-leagues/tmpl-official/after_wrapper
 *
 * @since @since 0.5.13
 *
 * @param int $official_id
 */
do_action( 'sports-leagues/tmpl-official/after_wrapper', $official_id );
