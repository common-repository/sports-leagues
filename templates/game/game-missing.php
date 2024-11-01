<?php
/**
 * The Template for displaying Game >> Missing Players Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-missing.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.9.1
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
		'header'     => true,
	]
);

$missing_players = json_decode( get_post_meta( $data->game_id, '_sl_missing_players', true ) );

if ( empty( $missing_players ) || ! is_array( $missing_players ) ) {
	return;
}

$show_player_position = 'no' !== Sports_Leagues_Customizer::get_value( 'game', 'show_player_position' );

// Prepare squad
$home_squad = sports_leagues()->team->tmpl_prepare_team_roster( $data->home_team, $data->season_id );
$away_squad = sports_leagues()->team->tmpl_prepare_team_roster( $data->away_team, $data->season_id );
?>
<div class="anwp-section game__missing-players">

	<?php if ( Sports_Leagues::string_to_bool( $data->header ) ) : ?>
		<div class="anwp-block-header mb-0">
			<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__missing__missing_players', __( 'Missing Players', 'sports-leagues' ) ) ); ?>
		</div>
	<?php endif; ?>

	<div class="anwp-row anwp-no-gutters">
		<div class="anwp-col-sm pr-sm-3">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo sports_leagues()->helper->render_team_header( $data->home_logo, $data->home_title, $data->home_team, true );

			/*
			|--------------------------------------------------------------------------
			| Home Club Missing
			|--------------------------------------------------------------------------
			*/
			foreach ( $missing_players as $missing_player ) :

				if ( absint( $missing_player->team ) !== absint( $data->home_team ) ) {
					continue;
				}

				$player_id = absint( $missing_player->player );

				if ( empty( $home_squad[ $player_id ] ) ) {
					continue;
				}

				$player = $home_squad[ $player_id ];
				?>
				<div class="missing-player d-flex border-bottom py-1">
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
					<div class="flex-grow-1 w-100">
						<div class="d-flex">
							<div class="game-player__name">
								<?php echo esc_html( $player['name'] ); ?>
							</div>
							<div class="game-player__flag ml-2 d-flex">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo sports_leagues()->helper->render_player_flag( $player['nationality'] );
								?>
							</div>
						</div>
						<?php if ( $show_player_position ) : ?>
							<div class="game-player__role"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', $player['role'] ) ); ?></div>
						<?php endif; ?>
						<div class="small mt-1">
							<?php if ( 'suspended' === $missing_player->reason ) : ?>
								<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__missing__suspended', __( 'Suspended', 'sports-leagues' ) ) ); ?>
								<?php echo $missing_player->comment ? ' - ' : ''; ?>
							<?php elseif ( 'injured' === $missing_player->reason ) : ?>
								<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__missing__injured', __( 'Injured', 'sports-leagues' ) ) ); ?>
								<?php echo $missing_player->comment ? ' - ' : ''; ?>
							<?php endif; ?>
							<?php echo esc_html( $missing_player->comment ); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="anwp-col-sm mt-4 mt-sm-0 pl-sm-3">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo sports_leagues()->helper->render_team_header( $data->away_logo, $data->away_title, $data->away_team, false );

			/*
			|--------------------------------------------------------------------------
			| Away Club Missing players
			|--------------------------------------------------------------------------
			*/
			foreach ( $missing_players as $missing_player ) :
				if ( absint( $missing_player->team ) !== absint( $data->away_team ) ) {
					continue;
				}

				$player_id = absint( $missing_player->player );

				if ( empty( $away_squad[ $player_id ] ) ) {
					continue;
				}

				$player = $away_squad[ $player_id ];
				?>
				<div class="missing-player d-flex border-bottom py-1">
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
					<div class="flex-grow-1 w-100">
						<div class="d-flex">
							<div class="game-player__name">
								<?php echo esc_html( $player['name'] ); ?>
							</div>
							<div class="game-player__flag ml-2 d-flex">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo sports_leagues()->helper->render_player_flag( $player['nationality'] );
								?>
							</div>
						</div>
						<?php if ( $show_player_position ) : ?>
							<div class="game-player__role"><?php echo esc_html( sports_leagues()->config->get_name_by_id( 'position', $player['role'] ) ); ?></div>
						<?php endif; ?>
						<div class="small mt-1">
							<?php if ( 'suspended' === $missing_player->reason ) : ?>
								<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__missing__suspended', __( 'Suspended', 'sports-leagues' ) ) ); ?>
								<?php echo $missing_player->comment ? ' - ' : ''; ?>
							<?php elseif ( 'injured' === $missing_player->reason ) : ?>
								<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__missing__injured', __( 'Injured', 'sports-leagues' ) ) ); ?>
								<?php echo $missing_player->comment ? ' - ' : ''; ?>
							<?php endif; ?>
							<?php echo esc_html( $missing_player->comment ); ?>
						</div>
					</div>

				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
