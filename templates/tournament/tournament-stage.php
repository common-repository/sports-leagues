<?php
/**
 * The Template for displaying Tournament >> Stage.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/tournament/tournament-stage.php.
 *
 * @var object    $data - Object with args.
 * @deprecated    Not recommended to use since v0.12.0.
 *                But can be activated with a hook.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.1.0
 *
 * @version       0.5.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'show_title' => true,
		'stage_id'   => '',
	]
);

if ( empty( $data->stage_id ) || empty( $data->tournament_id ) ) {
	return;
}

$games = sports_leagues()->tournament->get_tournament_games( $data->stage_id );
?>
<div class="tournament__stage anwp-section">
	<?php
	$stage_system = get_post_meta( $data->stage_id, '_sl_stage_system', true );

	/**
	 * Hook: sports-leagues/tmpl-tournament/before_stage
	 *
	 * @since 0.1.0
	 *
	 * @param object $data
	 */
	do_action( 'sports-leagues/tmpl-tournament/before_stage', $data );

	/**
	 * Filter: sports-leagues/tmpl-tournament/render_stage_title
	 *
	 * @since 0.1.0
	 *
	 * @param bool
	 * @param object $data
	 */
	if ( apply_filters( 'sports-leagues/tmpl-tournament/render_stage_title', true, $data ) ) :
		if ( $data->show_title ) :
			?>
			<div class="anwp-section-header anwp-section anwp-tournament-stage-title mt-5 anwp-bg-light border-left">
				<?php echo esc_html( get_the_title( $data->stage_id ) ); ?>
			</div>
			<?php
		endif;
	endif;

	/**
	 * Hook: sports-leagues/tmpl-tournament/after_stage_title
	 *
	 * @since 0.1.0
	 *
	 * @param object $data
	 */
	do_action( 'sports-leagues/tmpl-tournament/after_stage_title', $data );

	// Prepare groups
	$groups = json_decode( get_post_meta( $data->stage_id, '_sl_groups', true ) );

	if ( empty( $groups ) || ! is_array( $groups ) ) {
		return;
	}

	/*
	|--------------------------------------------------------------------
	| Prepare rounds
	|--------------------------------------------------------------------
	*/
	$rounds = json_decode( get_post_meta( $data->stage_id, '_sl_rounds', true ) );

	if ( empty( $rounds ) || ! is_array( $rounds ) ) {
		return;
	}

	// Check DESC round sorting
	if ( 'desc' === Sports_Leagues_Options::get_value( 'tournament_rounds_order' ) ) {
		$rounds = wp_list_sort( $rounds, 'id', 'DESC' );
	}

	foreach ( $rounds as $round ) :

		/**
		 * Hook: sports-leagues/tmpl-tournament/before_group
		 *
		 * @since 0.1.0
		 *
		 * @param object  $round
		 * @param object  $data
		 */
		do_action( 'sports-leagues/tmpl-tournament/before_round', $round, $data );

		$render_round_title = count( $rounds ) > 1 && ! empty( $round->title );

		/**
		 * Filter: sports-leagues/tmpl-tournament/render_round_title
		 *
		 * @since 0.1.0
		 *
		 * @param bool    $render_round_title
		 * @param object  $round
		 * @param object  $data
		 */
		if ( apply_filters( 'sports-leagues/tmpl-tournament/render_round_title', $render_round_title, $round, $data ) ) :
			$round_games = wp_list_filter( $games, [ 'round_id' => $round->id ] );

			if ( ! empty( $round_games ) ) :
				?>
				<div class="anwp-block-header mt-4"><?php echo esc_html( $round->title ); ?></div>
				<?php
			endif;
		endif;

		foreach ( $groups as $group ) :

			if ( intval( $group->round ) !== intval( $round->id ) ) {
				continue;
			}

			/**
			 * Hook: sports-leagues/tmpl-tournament/before_group
			 *
			 * @since 0.1.0
			 *
			 * @param object  $group
			 * @param object  $data
			 */
			do_action( 'sports-leagues/tmpl-tournament/before_group', $group, $data );
			?>
			<div class="tournament__group-wrapper <?php echo esc_attr( 'group' === $stage_system ? 'mt-4' : 'mt-3' ); ?>">
				<?php
				if ( 'group' === $stage_system ) :

					/**
					 * Hook: sports-leagues/tmpl-tournament/before_group_title
					 *
					 * @since 0.1.0
					 *
					 * @param object  $group
					 * @param object  $data
					 */
					do_action( 'sports-leagues/tmpl-tournament/before_group_title', $group, $data );

					$render_group_title = count( $groups ) > 1 && ! empty( $group->title );

					/**
					 * Filter: sports-leagues/tmpl-tournament/render_group_title
					 *
					 * @since 0.1.0
					 *
					 * @param bool    $render_group_title
					 * @param object  $group
					 * @param object  $data
					 */
					if ( apply_filters( 'sports-leagues/tmpl-tournament/render_group_title', $render_group_title, $group, $data ) ) :
						?>
						<div class="tournament__group-title mt-4 mb-2 anwp-group-header"><?php echo esc_html( $group->title ); ?></div>
						<?php
					endif;

					/**
					 * Hook: sports-leagues/tmpl-tournament/after_group_title
					 *
					 * @since 0.1.0
					 *
					 * @param object  $group
					 * @param object  $data
					 */
					do_action( 'sports-leagues/tmpl-tournament/after_group_title', $group, $data );

					/**
					 * Filter: sports-leagues/tmpl-tournament/render_group_title
					 *
					 * @since 0.1.0
					 *
					 * @param bool
					 * @param object  $group
					 * @param object  $data
					 */
					if ( apply_filters( 'sports-leagues/tmpl-tournament/render_group_standing', true, $group, $data ) ) :

						$standing = sports_leagues()->tournament->tmpl_get_stage_standings( $data->stage_id, $group->id );

						if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) :

							$shortcode_args = [
								'id'      => $standing[0]->ID,
								'title'   => '',
								'context' => 'stage',
							];

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo sports_leagues()->template->shortcode_loader( 'standing', $shortcode_args );
						endif;
					endif;

					/**
					 * Hook: sports-leagues/tmpl-tournament/after_group_standing
					 *
					 * @since 0.1.0
					 *
					 * @param object  $group
					 * @param object  $data
					 */
					do_action( 'sports-leagues/tmpl-tournament/after_group_standing', $group, $data );
				endif;

				/**
				 * Filter: sports-leagues/tmpl-tournament/render_list_of_games
				 *
				 * @since 0.1.0
				 *
				 * @param bool
				 * @param object  $data
				 */
				if ( apply_filters( 'sports-leagues/tmpl-tournament/render_list_of_games', true, $data ) ) :
					?>
					<div class="list-group">
						<?php
						$game_day = 0;
						foreach ( $games as $game ) :

							if ( intval( $game->group_id ) !== intval( $group->id ) ) {
								continue;
							}

							if ( 'group' === $stage_system && intval( $game->game_day ) !== $game_day && intval( $game->game_day ) ) :
								?>
								<div class="anwp-block-header mt-4">
									<?php echo esc_html( Sports_Leagues_Text::get_value( 'tournament__stage__game_day', __( 'Game Day', 'sports-leagues' ) ) ) . ': ' . intval( $game->game_day ); ?>
								</div>
								<?php
								$game_day = (int) $game->game_day;
							endif;

							// Get game data to render
							$game_data = sports_leagues()->game->prepare_tmpl_game_data( $game );

							$game_data->tournament_logo = false;
							sports_leagues()->load_partial( $game_data, 'game/game', 'slim' );

						endforeach;
						?>
					</div>
					<?php
				endif;

				/**
				 * Hook: sports-leagues/tmpl-tournament/after_list_of_games
				 *
				 * @since 0.1.0
				 *
				 * @param object  $group
				 * @param object  $data
				 */
				do_action( 'sports-leagues/tmpl-tournament/after_list_of_games', $group, $data );
				?>
			</div>
			<?php
		endforeach; // End of Groups Loop
	endforeach; // End of Round Loop
	?>
</div>
<?php
/**
 * Hook: sports-leagues/tmpl-tournament-stage/after_wrapper
 *
 * @since 0.5.12
 *
 * @param int $stage_id Post ID
 */
do_action( 'sports-leagues/tmpl-tournament-stage/after_wrapper', $data->stage_id );
