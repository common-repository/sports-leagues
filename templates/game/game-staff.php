<?php
/**
 * The Template for displaying Game >> Staff List section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-staff.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.5.14
 *
 * @version       0.11.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_team'  => '',
		'game_id'    => '',
		'away_team'  => '',
		'home_logo'  => '',
		'away_logo'  => '',
		'home_title' => '',
		'away_title' => '',
		'home_link'  => '',
		'away_link'  => '',
		'season_id'  => '',
		'staff_home' => '',
		'staff_away' => '',
		'header'     => true,
	]
);

if ( empty( array_filter( array_map( 'absint', explode( ',', $data->staff_home ) ) ) ) && empty( array_filter( array_map( 'absint', explode( ',', $data->staff_away ) ) ) ) ) {
	return;
}

$show_staff_job = 'no' !== Sports_Leagues_Customizer::get_value( '', 'show_staff_job' );

// Prepare squad
$home_squad = sports_leagues()->team->tmpl_prepare_team_staff_roster( $data->home_team, $data->season_id );
$away_squad = sports_leagues()->team->tmpl_prepare_team_staff_roster( $data->away_team, $data->season_id );

$temp_staff = sports_leagues()->game->get_temp_staff( $data->game_id );

if ( $data->staff_home || $data->staff_away ) :
	/**
	 * Trigger on before rendering game players.
	 *
	 * @param object $data Game data
	 *
	 * @since 0.5.14
	 */
	do_action( 'sports-leagues/tmpl-game/staff_before', $data );
	?>
	<div class="anwp-section game__players-list game__staff-list">

		<?php if ( Sports_Leagues::string_to_bool( $data->header ) ) : ?>
			<div class="anwp-block-header mb-0">
				<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__staff__staff', __( 'Staff', 'sports-leagues' ) ) ); ?>
			</div>
		<?php endif; ?>

		<div class="pb-3 pt-1 game__players-list-inner">
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
					$home_players = $data->staff_home ? explode( ',', $data->staff_home ) : [];

					if ( ! empty( $home_players ) && is_array( $home_players ) ) :
						foreach ( $home_players as $player_id ) :

							if ( '_' === mb_substr( $player_id, 0, 1 ) ) {
								echo '<div class="game-player-group">' . esc_html( mb_substr( $player_id, 1 ) ) . '</div>';
								continue;
							}

							if ( mb_strpos( $player_id, 'temp__' ) !== false ) {
								if ( empty( $temp_staff[ $player_id ] ) ) {
									continue;
								}

								$player = [
									'name'        => $temp_staff[ $player_id ]->title,
									'nationality' => $temp_staff[ $player_id ]->country,
									'job'         => $temp_staff[ $player_id ]->job,
								];
							} elseif ( empty( $home_squad[ $player_id ] ) ) {
								$player_obj = sports_leagues()->staff->get_single_staff( $player_id );

								if ( empty( $player_obj ) ) {
									continue;
								}

								$player = [
									'name'        => $player_obj->name_short,
									'nationality' => $player_obj->nationality,
									'job'         => $player_obj->job,
								];
							} else {
								$player = $home_squad[ $player_id ];
							}
							?>
							<div class="game-player d-flex py-1 align-items-center">
								<div class="game-player__meta">
									<div class="d-flex align-items-center">
										<div class="game-player__name"><?php echo esc_html( $player['name'] ); ?></div>
										<div class="game-player__flag d-flex">
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo sports_leagues()->helper->render_player_flag( $player['nationality'] );
											?>
										</div>
									</div>
									<?php if ( $show_staff_job ) : ?>
										<div class="game-player__role"><?php echo esc_html( $player['job'] ); ?></div>
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
					$away_players = $data->staff_away ? explode( ',', $data->staff_away ) : [];

					if ( ! empty( $away_players ) && is_array( $away_players ) ) :
						foreach ( $away_players as $player_id ) :

							if ( '_' === mb_substr( $player_id, 0, 1 ) ) {
								echo '<div class="game-player-group">' . esc_html( mb_substr( $player_id, 1 ) ) . '</div>';
								continue;
							}

							if ( mb_strpos( $player_id, 'temp__' ) !== false ) {
								if ( empty( $temp_staff[ $player_id ] ) ) {
									continue;
								}

								$player = [
									'name'        => $temp_staff[ $player_id ]->title,
									'nationality' => $temp_staff[ $player_id ]->country,
									'job'         => $temp_staff[ $player_id ]->job,
								];
							} elseif ( empty( $away_squad[ $player_id ] ) ) {
								$player_obj = sports_leagues()->staff->get_single_staff( $player_id );

								if ( empty( $player_obj ) ) {
									continue;
								}

								$player = [
									'name'        => $player_obj->name_short,
									'nationality' => $player_obj->nationality,
									'job'         => $player_obj->job,
								];
							} else {
								$player = $away_squad[ $player_id ];
							}
							?>
							<div class="game-player d-flex py-1 align-items-center">
								<div class="game-player__meta">
									<div class="d-flex align-items-center">
										<div class="game-player__name"><?php echo esc_html( $player['name'] ); ?></div>
										<div class="game-player__flag d-flex">
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo sports_leagues()->helper->render_player_flag( $player['nationality'] );
											?>
										</div>
									</div>
									<?php if ( $show_staff_job ) : ?>
										<div class="game-player__role"><?php echo esc_html( $player['job'] ); ?></div>
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
