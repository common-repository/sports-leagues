<?php
/**
 * The Template for displaying Tournament content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/content-tournament.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$post_obj = get_post();

/*
|--------------------------------------------------------------------
| Prepare initial data
|--------------------------------------------------------------------
*/
$stage_id      = $post_obj->post_parent ? $post_obj->ID : false;
$tournament_id = $post_obj->post_parent ?: $post_obj->ID;

/*
|--------------------------------------------------------------------
| Get list of stages
|--------------------------------------------------------------------
*/
if ( $stage_id ) {
	$stages = [ $stage_id ];
} else {
	$stages = get_posts(
		[
			'post_parent' => $tournament_id,
			'numberposts' => - 1,
			'post_type'   => 'sl_tournament',
			'fields'      => 'ids',
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		]
	);
}

/**
 * Filter: sports-leagues/tmpl-tournament/stages
 *
 * @since 0.1.0
 *
 * @param array   $stages
 * @param integer $tournament_id - Tournament ID
 *
 */
$stages = apply_filters( 'sports-leagues/tmpl-tournament/stages', $stages );

/**
 * Hook: sports-leagues/tmpl-tournament/before_wrapper
 *
 * @since 0.1.0
 *
 * @param int $tournament_id Post ID
 */
do_action( 'sports-leagues/tmpl-tournament/before_wrapper', $tournament_id );
?>
<div class="anwp-b-wrap tournament tournament-page tournament-<?php echo (int) $tournament_id; ?>">

	<?php
	/**
	 * Hook: sports-leagues/tmpl-tournament/before_header
	 *
	 * @since 0.1.0
	 *
	 * @param int $tournament_id Post ID
	 */
	do_action( 'sports-leagues/tmpl-tournament/before_header', $tournament_id );

	/**
	 * Filter: sports-leagues/tmpl-tournament/render_header
	 *
	 * @since 0.1.0
	 *
	 * @param int $tournament_id Post ID
	 */
	if ( apply_filters( 'sports-leagues/tmpl-tournament/render_header', true, $tournament_id ) ) {

		$shortcode_attr = [
			'stage_id'      => $stage_id,
			'tournament_id' => $tournament_id,
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo sports_leagues()->template->shortcode_loader( 'tournament-header', $shortcode_attr );
	}

	/**
	 * Hook: sports-leagues/tmpl-tournament/after_header
	 *
	 * @since 0.1.0
	 *
	 * @param int $tournament_id Post ID
	 */
	do_action( 'sports-leagues/tmpl-tournament/after_header', $tournament_id );

	if ( ! empty( $stages ) && is_array( $stages ) ) :

		foreach ( $stages as $stage ) :

			if ( empty( $stage ) ) {
				continue;
			}

			$tmpl_data = [
				'show_title'    => count( $stages ) > 1,
				'tournament_id' => $tournament_id,
				'stage_id'      => $stage,
			];

			sports_leagues()->load_partial( $tmpl_data, 'tournament/tournament-stage' );
		endforeach;
	endif;
	?>
</div>
<?php
/**
 * Hook: sports-leagues/tmpl-tournament/after_wrapper
 *
 * @since 0.1.0
 *
 * @param int $tournament_id Post ID
 */
do_action( 'sports-leagues/tmpl-tournament/after_wrapper', $tournament_id );
