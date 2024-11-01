<?php
/**
 * The Template for displaying Player >> Game Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.6.0
 *
 * @version       0.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'season_dropdown' => '',
		'season_id'       => '',
		'player_id'       => '',
		'header'          => true,
	]
);

$stats_columns = json_decode( get_option( 'sl_columns_game' ) );

if ( empty( $stats_columns ) ) {
	return;
}

$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : '';

// Load previous version (before stat groups)
if ( empty( $stat_groups ) ) {
	return sports_leagues()->load_partial( $data, 'player/player-stats', 'v1' );
}

$player_position    = get_post_meta( $data->player_id, '_sl_position', true );
$custom_date_format = sports_leagues()->get_option_value( 'custom_game_date_format' );

/*
|--------------------------------------------------------------------
| Prepare Stats Data
|--------------------------------------------------------------------
*/
$player_stats         = sports_leagues()->player_stats->get_player_stats( $data->player_id, $data->season_id );
$player_stats_grouped = sports_leagues()->player_stats->get_player_stats_grouped( $player_stats, $data );

/**
 * Hook: sports-leagues/tmpl-player/player_stats_before
 *
 * @param object $data PLayer data
 *
 * @since 0.6.0
 */
do_action( 'sports-leagues/tmpl-player/player_stats_before', $data );

$stats_class = 'yes' === Sports_Leagues_Customizer::get_value( 'general', 'stats_text_monospace' ) ? ' anwp-text-monospace ' : 'anwp-text-sm';
?>
<div class="anwp-section player_stats">

	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'player__player_stats__player_stats', __( 'Player Stats', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}

	if ( ! empty( $player_stats_grouped ) && is_array( $player_stats_grouped ) ) :

		/*
		|--------------------------------------------------------------------
		| Prepare game permalinks
		|--------------------------------------------------------------------
		*/
		$game_permalinks = [];

		if ( 'no' !== Sports_Leagues_Customizer::get_value( 'player', 'link_to_the_game_player_stats' ) ) {

			$game_ids = [];

			foreach ( $player_stats_grouped as $team_id => $team_data ) {
				foreach ( $team_data as $stat_group_id => $stat_group_data ) {
					$game_ids = array_merge( $game_ids, array_keys( $stat_group_data ) );
				}
			}

			$game_permalinks = sports_leagues()->game->get_permalinks_by_ids( $game_ids );
		}

		foreach ( $player_stats_grouped as $team_id => $team_data ) :

			// Team data
			$team = sports_leagues()->team->get_team_by_id( $team_id );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo sports_leagues()->helper->render_team_header( $team->logo, $team->title, $team_id );

			foreach ( $team_data as $stat_group_id => $stat_group_data ) :
				if ( ! empty( sports_leagues()->player_stats->get_stats_group_by_id( $stat_group_id )->name ) ) :
					sports_leagues()->load_partial(
						[
							'text'  => sports_leagues()->player_stats->get_stats_group_by_id( $stat_group_id )->name,
							'class' => 'mt-3 mb-2',
						],
						'general/subheader'
					);
				endif;
				?>
				<table class="anwp-stats-table anwp-sl-player-stats w-100 my-0 nowrap cell-border compact stripe">
					<thead>
					<tr class="small">
						<th class="no-sort text-left"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_stats__date', __( 'Date', 'sports-leagues' ) ) ); ?></th>
						<th class="no-sort text-left"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_stats__vs', __( 'VS', 'sports-leagues' ) ) ); ?></th>
						<?php
						foreach ( $stats_columns as $column ) :
							if ( 'hidden' === $column->visibility || ! in_array( $stat_group_id, $column->groups, true ) ) {
								continue;
							}
							?>
							<th class="text-right <?php echo esc_attr( 'composed' === $column->type ? 'no-sort' : '' ); ?>" data-toggle="<?php echo $column->name ? 'anwp-sl-tooltip' : ''; ?>" data-tippy-content="<?php echo esc_attr( $column->name ); ?>">
								<?php echo esc_html( $column->abbr ); ?>
							</th>
						<?php endforeach; ?>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $stat_group_data as $game_id => $game_data ) :
						$game_date = date_i18n( $custom_date_format ?: 'j M Y', strtotime( $game_data['data']->kickoff ) );

						// Get opposite team
						$team_id_opp = absint( $game_data['data']->home_team ) === $team_id ? absint( $game_data['data']->away_team ) : absint( $game_data['data']->home_team );
						$team_opp    = sports_leagues()->team->get_team_by_id( $team_id_opp );
						?>
						<tr data-sl-game-datetime="<?php echo esc_attr( date_i18n( 'c', strtotime( $game_data['data']->kickoff ) ) ); ?>">
							<td class="text-left text-nowrap align-middle text-right game__date-formatted anwp-text-xs px-1"><?php echo esc_html( $game_date ); ?></td>
							<td class="anwp-text-center text-nowrap small">
								<?php if ( ! empty( $game_permalinks[ $game_id ] ) ) : ?>
									<a href="<?php echo esc_url( $game_permalinks[ $game_id ] ); ?>" class="anwp-link-without-effects p-0 m-0 d-flex align-items-center">
										<?php if ( ! empty( $team_opp->logo ) ) : ?>
											<div class="team-logo__cover team-logo__cover--small mr-1" style="background-image: url('<?php echo esc_attr( $team_opp->logo ); ?>')"></div>
										<?php endif; ?>

										<span><?php echo esc_html( ( $team_opp->code ?? '' ) ? $team_opp->code : ( ( $team_opp->abbr ?? '' ) ? $team_opp->abbr : $team_opp->title ) ); ?></span>
									</a>
								<?php else : ?>
									<div class="p-0 m-0 d-flex align-items-center">
										<?php if ( ! empty( $team_opp->logo ) ) : ?>
											<div class="team-logo__cover team-logo__cover--small mr-1" style="background-image: url('<?php echo esc_attr( $team_opp->logo ); ?>')"></div>
										<?php endif; ?>

										<span><?php echo esc_html( ( $team_opp->code ?? '' ) ? $team_opp->code : ( ( $team_opp->abbr ?? '' ) ? $team_opp->abbr : $team_opp->title ) ); ?></span>
									</div>
								<?php endif; ?>
							</td>
							<?php
							foreach ( $stats_columns as $column ) :
								if ( 'hidden' === $column->visibility || ! in_array( $stat_group_id, $column->groups, true ) ) {
									continue;
								}

								$cell_value = sports_leagues()->player_stats->render_player_game_stats( $game_data['stats'], $column );
								$data_order = '';

								if ( 'time' === $column->type ) {
									$data_order = str_replace( ':', '', $cell_value );
								} elseif ( ( isset( $column->prefix ) && '' !== $column->prefix ) || ( isset( $column->postfix ) && '' !== $column->postfix ) ) {

									$data_order = $cell_value;

									if ( isset( $column->prefix ) && '' !== $column->prefix ) {
										$data_order = ltrim( $data_order, $column->prefix );
									}

									if ( isset( $column->postfix ) && '' !== $column->postfix ) {
										$data_order = rtrim( $data_order, $column->postfix );
									}
								}
								?>
								<td class="text-right anwp-w-15  <?php echo esc_attr( $stats_class ); ?>" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
									<?php echo esc_html( $cell_value ); ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
					<tr class="small">
						<th class="no-sort text-left"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_stats__date', __( 'Date', 'sports-leagues' ) ) ); ?></th>
						<th class="no-sort text-left""><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_stats__vs', __( 'VS', 'sports-leagues' ) ) ); ?></th>
						<?php
						foreach ( $stats_columns as $column ) :
							if ( 'hidden' === $column->visibility || ! in_array( $stat_group_id, $column->groups, true ) ) {
								continue;
							}
							?>
							<th class="text-right" data-toggle="<?php echo $column->name ? 'anwp-sl-tooltip' : ''; ?>" data-tippy-content="<?php echo esc_attr( $column->name ); ?>">
								<?php echo esc_html( $column->abbr ); ?>
							</th>
						<?php endforeach; ?>
					</tr>
					</tfoot>
				</table>
				<?php
			endforeach;
		endforeach;
	else :
		sports_leagues()->load_partial(
			[
				'no_data_text' => Sports_Leagues_Text::get_value( 'player__player_stats__no_data', __( 'No Data', 'sports-leagues' ) ),
			],
			'general/no-data'
		);
	endif;
	?>
</div>
