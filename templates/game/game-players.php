<?php
/**
 * The Template for displaying Game >> Players List section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-players.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.3
 *
 * @version       0.11.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'game_id'        => '',
		'home_team'      => '',
		'away_team'      => '',
		'home_logo'      => '',
		'away_logo'      => '',
		'home_title'     => '',
		'away_title'     => '',
		'home_link'      => '',
		'away_link'      => '',
		'season_id'      => '',
		'players_home'   => '',
		'players_away'   => '',
		'custom_numbers' => (object) [],
		'header'         => true,
	]
);

if ( empty( array_filter( array_map( 'absint', explode( ',', $data->players_home ) ) ) ) && empty( array_filter( array_map( 'absint', explode( ',', $data->players_away ) ) ) ) ) {
	return;
}

$show_player_position = 'no' !== Sports_Leagues_Customizer::get_value( 'game', 'show_player_position' );

// Get players events
$players_events = sports_leagues()->event->get_game_events_to_render( $data->game_id, 'players' );
$temp_players   = sports_leagues()->game->get_temp_players( $data->game_id );

// Prepare squad
$home_squad = sports_leagues()->team->tmpl_prepare_team_roster( $data->home_team, $data->season_id );
$away_squad = sports_leagues()->team->tmpl_prepare_team_roster( $data->away_team, $data->season_id );

if ( $data->players_home || $data->players_away ) :
	/**
	 * Trigger on before rendering game players.
	 *
	 * @param object $data Game data
	 *
	 * @since 0.5.3
	 */
	do_action( 'sports-leagues/tmpl-game/players_before', $data );
	?>
	<div class="anwp-section game__players-list">

		<?php if ( Sports_Leagues::string_to_bool( $data->header ) ) : ?>
			<div class="anwp-block-header mb-0">
				<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__players__players', __( 'Players', 'sports-leagues' ) ) ); ?>
			</div>
		<?php endif; ?>

		<?php
		/**
		* Trigger on before rendering game players.
		*
		* @param object $data Game data
		*
		* @since 0.5.3
		*/
		do_action( 'sports-leagues/tmpl-game/players_after_header', $data );
		?>

		<div class="pb-3 game__players-list-inner">
			<div class="anwp-row">
				<div class="anwp-col-md d-flex flex-column pr-3 mb-3 mb-md-0">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo sports_leagues()->helper->render_team_header( $data->home_logo, $data->home_title, $data->home_team, true );

					/*
					|--------------------------------------------------------------------------
					| Home Team List
					|--------------------------------------------------------------------------
					*/
					$home_players = $data->players_home ? explode( ',', $data->players_home ) : [];

					if ( ! empty( $home_players ) && is_array( $home_players ) ) :
						foreach ( $home_players as $player_id ) :

							if ( '_' === mb_substr( $player_id, 0, 1 ) ) {
								echo '<div class="game-player-group">' . esc_html( sports_leagues()->config->get_name_by_id( 'game_player_groups', mb_substr( $player_id, 1 ) ) ) . '</div>';
								continue;
							}

							if ( mb_strpos( $player_id, 'temp__' ) !== false ) {
								if ( empty( $temp_players[ $player_id ] ) ) {
									continue;
								}

								$player = [
									'number'      => '',
									'name'        => $temp_players[ $player_id ]->title,
									'nationality' => $temp_players[ $player_id ]->country,
									'role'        => $temp_players[ $player_id ]->position,
								];
							} elseif ( empty( $home_squad[ $player_id ] ) ) {
								$player_obj = sports_leagues()->player->get_player( $player_id );

								if ( empty( $player_obj->id ) ) {
									continue;
								}

								$player = [
									'number'      => '',
									'name'        => $player_obj->name_short,
									'nationality' => $player_obj->nationality,
									'role'        => $player_obj->position_id,
								];
							} else {
								$player = $home_squad[ $player_id ];
							}
							?>
							<div class="game-player d-flex py-1 align-items-center">
								<div class="game-player__number mr-2">
									<?php
									$player_number = '';

									if ( isset( $data->custom_numbers->{$player_id} ) ) {
										$player_number = $data->custom_numbers->{$player_id};
									} elseif ( isset( $player['number'] ) ) {
										$player_number = $player['number'];
									}

									echo esc_html( $player_number );
									?>
								</div>
								<div class="game-player__meta flex-grow-1">
									<div class="d-flex align-items-center">
										<div class="game-player__name"><?php echo esc_html( $player['name'] ); ?></div>
										<div class="game-player__flag mr-2 d-flex">
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo sports_leagues()->helper->render_player_flag( $player['nationality'] );
											?>
										</div>
										<div class="game-player__events ml-auto d-flex flex-wrap">
											<?php
											if ( ! empty( $players_events[ $player_id ] ) && is_array( $players_events[ $player_id ] ) ) :
												foreach ( $players_events[ $player_id ] as $player_event ) :
													sports_leagues()->event->render_event( 'players', $player_event );
												endforeach;
											endif;
											?>
										</div>
									</div>
									<?php if ( $show_player_position ) : ?>
										<div class="game-player__role"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', $player['role'] ) ); ?></div>
									<?php endif; ?>
								</div>
							</div>
							<?php
						endforeach;
					endif;
					?>
				</div>
				<div class="anwp-col-md d-flex flex-column pr-3">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo sports_leagues()->helper->render_team_header( $data->away_logo, $data->away_title, $data->away_team, false );

					/*
					|--------------------------------------------------------------------------
					| Away Team List
					|--------------------------------------------------------------------------
					*/
					$away_players = $data->players_away ? explode( ',', $data->players_away ) : [];

					if ( ! empty( $away_players ) && is_array( $away_players ) ) :
						foreach ( $away_players as $player_id ) :

							if ( '_' === mb_substr( $player_id, 0, 1 ) ) {
								echo '<div class="game-player-group">' . esc_html( sports_leagues()->config->get_name_by_id( 'game_player_groups', mb_substr( $player_id, 1 ) ) ) . '</div>';
								continue;
							}

							if ( mb_strpos( $player_id, 'temp__' ) !== false ) {
								if ( empty( $temp_players[ $player_id ] ) ) {
									continue;
								}

								$player = [
									'number'      => '',
									'name'        => $temp_players[ $player_id ]->title,
									'nationality' => $temp_players[ $player_id ]->country,
									'role'        => $temp_players[ $player_id ]->position,
								];
							} elseif ( empty( $away_squad[ $player_id ] ) ) {
								$player_obj = sports_leagues()->player->get_player( $player_id );

								if ( empty( $player_obj->id ) ) {
									continue;
								}

								$player = [
									'number'      => '',
									'name'        => $player_obj->name_short,
									'nationality' => $player_obj->nationality,
									'role'        => $player_obj->position_id,
								];
							} else {
								$player = $away_squad[ $player_id ];
							}
							?>
							<div class="game-player d-flex py-1 align-items-center">
								<div class="game-player__number mr-2">
									<?php
									$player_number = '';

									if ( isset( $data->custom_numbers->{$player_id} ) ) {
										$player_number = $data->custom_numbers->{$player_id};
									} elseif ( isset( $player['number'] ) ) {
										$player_number = $player['number'];
									}

									echo esc_html( $player_number );
									?>
								</div>
								<div class="game-player__meta flex-grow-1">
									<div class="d-flex align-items-center">
										<div class="game-player__name"><?php echo esc_html( $player['name'] ); ?></div>
										<div class="game-player__flag mr-2 d-flex">
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo sports_leagues()->helper->render_player_flag( $player['nationality'] );
											?>
										</div>
										<div class="game-player__events ml-auto d-flex flex-wrap">
											<?php
											if ( ! empty( $players_events[ $player_id ] ) && is_array( $players_events[ $player_id ] ) ) :
												foreach ( $players_events[ $player_id ] as $player_event ) :
													sports_leagues()->event->render_event( 'players', $player_event );
												endforeach;
											endif;
											?>
										</div>
									</div>
									<?php if ( $show_player_position ) : ?>
										<div class="game-player__role"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', $player['role'] ) ); ?></div>
									<?php endif; ?>
								</div>
							</div>
							<?php
						endforeach;
					endif;
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
endif;
