<?php
/**
 * The Template for displaying Player >> Missed Games Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-missed.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.9.1
 *
 * @version       0.12.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'player_id'     => '',
		'season_id'     => '',
		'position_code' => '',
		'header'        => true,
	]
);

$missed_games       = sports_leagues()->game->get_player_missed_games_by_season( $data->player_id, $data->season_id );
$custom_date_format = sports_leagues()->get_option_value( 'custom_game_date_format' );

if ( empty( $missed_games ) ) {
	return;
}
?>
<div class="player__missed player-section anwp-section">

	<?php if ( sports_leagues()->string_to_bool( $data->header ) ) : ?>
		<div class="anwp-block-header__wrapper d-flex justify-content-between">
			<div class="anwp-block-header"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__missed__missed_games', __( 'Missed Games', 'sports-leagues' ) ) ); ?></div>
			<?php sports_leagues()->helper->season_dropdown( $data->season_id ); ?>
		</div>
	<?php endif; ?>

	<div class="table-responsive">
		<table class="w-100 table table-sm table-bordered">
			<thead>
			<tr>
				<th style="width: 5%;"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__missed__date', __( 'Date', 'sports-leagues' ) ) ); ?></th>
				<th><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__missed__against', __( 'Against', 'sports-leagues' ) ) ); ?></th>
				<th><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__missed__reason', __( 'Reason', 'sports-leagues' ) ) ); ?></th>
			</tr>
			</thead>

			<tbody>

			<?php
			foreach ( $missed_games as $game ) :

				$game_date = date_i18n( $custom_date_format ?: 'j M Y', strtotime( $game->kickoff ) );

				// Get opposite team
				$team_id_opp = $game->team_id === $game->home_team ? $game->away_team : $game->home_team;
				$team_opp    = sports_leagues()->team->get_team_by_id( $team_id_opp );

				?>
				<tr data-sl-game-datetime="<?php echo esc_attr( date_i18n( 'c', strtotime( $game->kickoff ) ) ); ?>">
					<td class="text-left text-nowrap anwp-bg-gray-200 game__date-formatted"><?php echo esc_html( $game_date ); ?></td>
					<td class="text-nowrap">
						<div class="d-flex align-items-center">
							<?php if ( ! empty( $team_opp->logo ) ) : ?>
								<div class="team-logo__cover team-logo__cover--small mr-1" style="background-image: url('<?php echo esc_attr( $team_opp->logo ); ?>')"></div>
							<?php endif; ?>
							<span><?php echo esc_html( empty( $team_opp->abbr ) ? $team_opp->title : $team_opp->abbr ); ?></span>
						</div>
					</td>
					<td class="small align-middle">
						<?php if ( 'suspended' === $game->reason ) : ?>
							<?php echo esc_html( Sports_Leagues_Text::get_value( 'match__missing__suspended', __( 'Suspended', 'sports-leagues' ) ) ); ?>
							<?php echo $game->comment ? ' - ' : ''; ?>
						<?php elseif ( 'injured' === $game->reason ) : ?>
							<?php echo esc_html( Sports_Leagues_Text::get_value( 'match__missing__injured', __( 'Injured', 'sports-leagues' ) ) ); ?>
							<?php echo $game->comment ? ' - ' : ''; ?>
						<?php endif; ?>
						<?php echo esc_html( $game->comment ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
