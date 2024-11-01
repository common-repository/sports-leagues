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

$player_stats_raw    = sports_leagues()->player_stats->get_player_total_stats( $data->player_id, $data->season_id );
$player_stats        = sports_leagues()->player_stats->get_player_total_stats_sums( $player_stats_raw, $stats_columns_season );
$player_roster_group = '';
?>
<div class="anwp-section player-total-stats table-responsive" data-version="v1">

	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'player__player_total_stats__total_stats', __( 'Total Stats', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<?php if ( ! empty( $player_stats ) && is_array( $player_stats ) ) : ?>
		<table class="table table-bordered anwp-stats-table table-sm anwp-sl-player-total-stats w-100 my-0 nowrap">
			<?php
			$thead_loaded = false;

			foreach ( $player_stats as $team_id => $team_data ) :

				if ( 'totals' === $team_id ) {
					continue;
				}

				$player_roster_group = sports_leagues()->player_stats->get_player_season_roster_group( $team_id, $data->season_id, $data->player_id );

				if ( empty( $player_roster_group ) ) {
					continue;
				}

				if ( ! $thead_loaded ) :

					$thead_loaded = true;

					?>
					<thead>
					<tr class="small">
						<th class="align-middle no-sort px-2"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_total_stats__tournament_stage', __( 'Tournament Stage', 'sports-leagues' ) ) ); ?></th>
						<th class="align-middle no-sort px-2"><?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_total_stats__team', __( 'Team', 'sports-leagues' ) ) ); ?></th>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( 'hidden' === $column->visibility ) {
								continue;
							}

							if ( '_' === $player_roster_group && ! empty( $column->groups ) ) {
								continue;
							}

							if ( '_' !== $player_roster_group && ! empty( $column->groups ) && ! in_array( $player_roster_group, $column->groups, true ) ) {
								continue;
							}
							?>
							<th class="text-right px-3 <?php echo esc_attr( 'composed' === $column->type ? 'no-sort' : '' ); ?>" data-toggle="<?php echo $column->name ? 'anwp-sl-tooltip' : ''; ?>" data-tippy-content="<?php echo esc_attr( $column->name ); ?>">
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
						<td class="text-left text-nowrap">
							<?php echo esc_html( sports_leagues()->tournament->get_title( wp_get_post_parent_id( $stage_id ) ) ); ?>
							-
							<?php echo esc_html( sports_leagues()->tournament->get_title( $stage_id ) ); ?>
						</td>
						<td class="text-left text-nowrap small text-monospace anwp-w-15">
							<div class="d-flex align-items-center">
								<?php if ( ! empty( $team->logo ) ) : ?>
									<div class="team-logo__cover team-logo__cover--small mr-1" style="background-image: url('<?php echo esc_attr( $team->logo ); ?>')"></div>
								<?php endif; ?>

								<?php if ( ! empty( $team->abbr ) ) : ?>
									<span><?php echo esc_html( $team->abbr ); ?></span>
								<?php elseif ( ! empty( $team->title ) ) : ?>
									<span><?php echo esc_html( $team->title ); ?></span>
								<?php endif; ?>
							</div>
						</td>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( 'hidden' === $column->visibility ) {
								continue;
							}

							if ( '_' === $player_roster_group && ! empty( $column->groups ) ) {
								continue;
							}

							if ( '_' !== $player_roster_group && ! empty( $column->groups ) && ! in_array( $player_roster_group, $column->groups, true ) ) {
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
							<td class="text-right anwp-w-15 text-monospace" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
								<?php echo esc_html( $cell_value ); ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
				<?php if ( ! empty( $player_roster_group ) ) : ?>
					<tr class="anwp-bg-gray-200 font-weight-bold">
						<td class="text-left text-nowrap" colspan="2">
							<?php echo esc_html( Sports_Leagues_Text::get_value( 'player__player_total_stats__totals', __( 'Totals', 'sports-leagues' ) ) ); ?>
						</td>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( 'hidden' === $column->visibility ) {
								continue;
							}

							if ( '_' === $player_roster_group && ! empty( $column->groups ) ) {
								continue;
							}

							if ( '_' !== $player_roster_group && ! empty( $column->groups ) && ! in_array( $player_roster_group, $column->groups, true ) ) {
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
							<td class="text-right anwp-w-15 text-monospace" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
								<?php echo esc_html( $cell_value ); ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endif; ?>
			<tbody>
		</table>
		<?php
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
