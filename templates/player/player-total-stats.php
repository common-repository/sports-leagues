<?php
/**
 * The Template for displaying Player >> Total Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/player/player-total-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.9.5
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

if ( ! absint( $data->player_id ) && ! absint( $data->season_id ) ) {
	return;
}

$stats_columns_game   = json_decode( get_option( 'sl_columns_game' ) );
$stats_columns_season = json_decode( get_option( 'sl_columns_season' ) );

if ( empty( $stats_columns_season ) || empty( $stats_columns_game ) ) {
	return;
}

$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : '';

// Load previous version (before stat groups)
if ( empty( $stat_groups ) ) {
	return sports_leagues()->load_partial( $data, 'player/player-total-stats', 'v1' );
}

$player_position = get_post_meta( $data->player_id, '_sl_position', true );

$player_stats_raw     = sports_leagues()->player_stats->get_player_total_stats( $data->player_id, $data->season_id );
$player_stats         = sports_leagues()->player_stats->get_player_total_stats_sums( $player_stats_raw, $stats_columns_season );
$player_stats_grouped = sports_leagues()->player_stats->get_player_total_stats_sums_grouped( $player_stats, $data, $stat_groups );

$stats_class = 'yes' === Sports_Leagues_Customizer::get_value( 'general', 'stats_text_monospace' ) ? ' anwp-text-monospace ' : 'anwp-text-sm';
?>
<div class="anwp-section player-total-stats table-responsive">

	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) :
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'player__player_total_stats__total_stats', __( 'Total Stats', 'sports-leagues' ) ),
			],
			'general/header'
		);
	endif;

	if ( ! empty( $player_stats_grouped ) && is_array( $player_stats_grouped ) ) :
		foreach ( $player_stats_grouped as $stat_group_id => $stat_group_data ) :
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
			<table class="table table-bordered anwp-stats-table table-sm anwp-sl-player-total-stats w-100 my-0 nowrap">
				<?php
				$thead_loaded = false;

				foreach ( $player_stats as $team_id => $team_data ) :

					if ( 'totals' === $team_id ) {
						continue;
					}

					if ( ! $thead_loaded ) :
						$thead_loaded = true;

						?>
						<thead>
						<tr class="anwp-text-sm">
							<th width="1%" class="align-middle no-sort px-2 anwp-text-left"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_total_stats__tournament_stage', __( 'Tournament Stage', 'sports-leagues' ) ) ); ?></th>
							<th class="align-middle no-sort px-1 anwp-text-left"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_total_stats__team', __( 'Team', 'sports-leagues' ) ) ); ?></th>
							<?php
							foreach ( $stats_columns_season as $column ) :
								if ( 'hidden' === $column->visibility || empty( $column->groups ) || ! in_array( $stat_group_id, $column->groups, true ) ) {
									continue;
								}
								?>
								<th width="1%" class="anwp-text-right anwp-w-10 px-2 <?php echo esc_attr( 'composed' === $column->type ? 'no-sort' : '' ); ?>" data-toggle="<?php echo $column->name ? 'anwp-sl-tooltip' : ''; ?>" data-tippy-content="<?php echo esc_attr( $column->name ); ?>">
									<?php echo esc_html( $column->abbr ); ?>
								</th>
							<?php endforeach; ?>
						</tr>
						</thead>
						<tbody>
					<?php endif; ?>
					<?php
					foreach ( $team_data as $stage_id => $stage_data ) :

						if ( empty( $stage_data ) ) {
							continue;
						}

						$team = sports_leagues()->team->get_team_by_id( $team_id );
						?>
						<tr>
							<td width="1%" class="anwp-text-left anwp-text-nowrap anwp-text-sm anwp-leading-1-25">
								<?php echo esc_html( sports_leagues()->tournament->get_title( wp_get_post_parent_id( $stage_id ) ) ); ?>
								<br>
								<?php echo esc_html( sports_leagues()->tournament->get_title( $stage_id ) ); ?>
							</td>
							<td class="anwp-text-left text-nowrap anwp-text-sm anwp-w-15 <?php echo esc_attr( $stats_class ); ?>">
								<div class="d-flex align-items-center">
									<?php if ( ! empty( $team->logo ) ) : ?>
										<div class="team-logo__cover team-logo__cover--small mr-1" style="background-image: url('<?php echo esc_attr( $team->logo ); ?>')"></div>
									<?php endif; ?>

									<span><?php echo esc_html( ( $team->code ?? '' ) ? $team->code : ( ( $team->abbr ?? '' ) ? $team->abbr : $team->title ) ); ?></span>
								</div>
							</td>
							<?php
							foreach ( $stats_columns_season as $column ) :
								if ( 'hidden' === $column->visibility || empty( $column->groups ) || ! in_array( $stat_group_id, $column->groups, true ) ) {
									continue;
								}

								// Get sell value
								$cell_value = sports_leagues()->player_stats->render_team_player_season_stats( $stage_data, $column );

								/*
								|--------------------------------------------------------------------
								| Prepare ordering raw values
								|--------------------------------------------------------------------
								*/
								$data_order = '';

								// Get cell value for time column
								if ( 'time' === $column->type && $column->game_field_id && is_numeric( $cell_value ) ) {
									$data_order = $cell_value;
									$cell_value = absint( $cell_value / 60 ) . ':' . str_pad( absint( $cell_value % 60 ), 2, '0', STR_PAD_LEFT );
								}

								if ( ( isset( $column->prefix ) && '' !== $column->prefix ) || ( isset( $column->postfix ) && '' !== $column->postfix ) ) {

									$data_order = $cell_value;

									if ( isset( $column->prefix ) && '' !== $column->prefix ) {
										$data_order = ltrim( $data_order, $column->prefix );
									}

									if ( isset( $column->postfix ) && '' !== $column->postfix ) {
										$data_order = rtrim( $data_order, $column->postfix );
									}
								}
								?>
								<td width="1%" class="anwp-text-right <?php echo esc_attr( $stats_class ); ?>" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
									<?php echo esc_html( $cell_value ); ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
					<tr class="anwp-bg-gray-200 font-weight-bold">
						<td class="anwp-text-left anwp-text-nowrap" colspan="2">
							<?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_total_stats__totals', __( 'Totals', 'sports-leagues' ) ) ); ?>
						</td>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( 'hidden' === $column->visibility || empty( $column->groups ) || ! in_array( $stat_group_id, $column->groups, true ) ) {
								continue;
							}

							// Get sell value
							$cell_value = sports_leagues()->player_stats->render_team_player_season_stats( $player_stats['totals'], $column );

							/*
							|--------------------------------------------------------------------
							| Prepare ordering raw values
							|--------------------------------------------------------------------
							*/
							$data_order = '';

							// Get cell value for time column
							if ( 'time' === $column->type && $column->game_field_id && is_numeric( $cell_value ) ) {
								$data_order = $cell_value;
								$cell_value = absint( $cell_value / 60 ) . ':' . str_pad( absint( $cell_value % 60 ), 2, '0', STR_PAD_LEFT );
							}

							if ( ( isset( $column->prefix ) && '' !== $column->prefix ) || ( isset( $column->postfix ) && '' !== $column->postfix ) ) {

								$data_order = $cell_value;

								if ( isset( $column->prefix ) && '' !== $column->prefix ) {
									$data_order = ltrim( $data_order, $column->prefix );
								}

								if ( isset( $column->postfix ) && '' !== $column->postfix ) {
									$data_order = rtrim( $data_order, $column->postfix );
								}
							}
							?>
							<td width="1%" class="anwp-text-right <?php echo esc_attr( $stats_class ); ?>" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
								<?php echo esc_html( $cell_value ); ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<tbody>
			</table>
			<?php
		endforeach;
	else :
		sports_leagues()->load_partial(
			[
				'no_data_text' => Sports_Leagues_Text::get_value( 'player__player_total_stats__no_data', __( 'No Data', 'sports-leagues' ) ),
			],
			'general/no-data'
		);
	endif;
	?>
</div>
