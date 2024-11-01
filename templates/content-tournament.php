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
 * @version       0.12.0
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
$stage_id       = $post_obj->post_parent ? $post_obj->ID : '';
$tournament_id  = $post_obj->post_parent ?: $post_obj->ID;
$tournament_obj = sports_leagues()->tournament->get_tournament( $tournament_id );

if ( empty( $tournament_obj ) ) {
	return;
}

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
			'tournament_id'   => $tournament_id,
			'season_selector' => 'hide' === Sports_Leagues_Customizer::get_value( 'tournament', 'show_selector' ) ? 0 : 1,
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

	/*
	|--------------------------------------------------------------------
	| Load Tournament Games
	|--------------------------------------------------------------------
	*/
	sports_leagues()->load_partial(
		[
			'tournament_id' => $tournament_id,
			'stage_id'      => $stage_id,
		],
		'tournament/tournament-games'
	);

	/*
	|--------------------------------------------------------------------
	| Load Stages Data
	|--------------------------------------------------------------------
	*/
	sports_leagues()->load_partial(
		[
			'tournament_id' => $tournament_id,
			'stage_id'      => $stage_id,
		],
		'tournament/tournament-standings'
	);
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
