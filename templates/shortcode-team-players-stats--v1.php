<?php
/**
 * The Template for displaying Shortcode >> Team Players Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/shortcode-team-players-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.6.2
 *
 * @version       0.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'season_id' => '',
		'team_id'   => '',
		'header'    => true,
	]
);

$stats_columns_game   = json_decode( get_option( 'sl_columns_game' ) );
$stats_columns_season = json_decode( get_option( 'sl_columns_season' ) );

if ( empty( $stats_columns_season ) || empty( $stats_columns_game ) ) {
	return;
}

// Get players stats
$player_stats_raw = sports_leagues()->player_stats->get_team_players_stats( $data->team_id, $data->season_id );

// Get players stats sum
$player_stats = sports_leagues()->player_stats->get_team_players_stats_sums( $player_stats_raw, $stats_columns_season );

$player_ids = sports_leagues()->player_stats->get_team_season_players_grouped(
	[
		'team_id'   => $data->team_id,
		'season_id' => $data->season_id,
	]
);

$stats_class = 'yes' === Sports_Leagues_Customizer::get_value( 'general', 'stats_text_monospace' ) ? ' anwp-text-monospace ' : 'anwp-text-sm';
?>
<div class="anwp-b-wrap team-players-stats team-players-stats--v1">

	<?php
	if ( Sports_Leagues::string_to_bool( $data->header ) ) {
		sports_leagues()->load_partial(
			[
				'text' => Sports_Leagues_Text::get_value( 'team__team_players_stats__players_stats', __( 'Players Stats', 'sports-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<?php
	if ( ! empty( $player_stats ) && is_array( $player_stats ) ) :
		foreach ( $player_ids as $group_title => $group_ids ) :

			if ( empty( $group_ids ) ) {
				continue;
			}

			if ( '_' === $group_title ) :
				?>
				<table class="anwp-stats-table anwp-sl-team-players-stats w-100 my-0 nowrap cell-border compact stripe">
					<thead>
					<tr class="small">
						<th class="align-middle no-sort px-2"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__team_players_stats__player', __( 'Player', 'sports-leagues' ) ) ); ?></th>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( ! empty( $column->groups ) || 'hidden' === $column->visibility ) {
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
					<?php
					foreach ( $group_ids as $player_id ) :

						if ( empty( $stats_players_home_data->{$player_id} ) ) {
							continue;
						}

						$player_obj = sports_leagues()->player->get_player( $player_id );
						?>
						<tr>
							<td class="text-left text-nowrap"><?php echo esc_html( $player_obj->name_short ); ?></td>
							<?php
							foreach ( $stats_columns_season as $column ) :
								if ( ! empty( $column->groups ) || 'hidden' === $column->visibility ) {
									continue;
								}

								$cell_value = sports_leagues()->player_stats->render_team_player_season_stats( $player_stats[ $player_id ], $column );

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
								<td class="text-right anwp-w-15  <?php echo esc_attr( $stats_class ); ?>" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
									<?php echo esc_html( $cell_value ); ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
					<tr class="small">
						<th class="align-middle no-sort px-2"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__team_players_stats__player', __( 'Player', 'sports-leagues' ) ) ); ?></th>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( ! empty( $column->groups ) || 'hidden' === $column->visibility ) {
								continue;
							}
							?>
							<th class="text-right px-3" data-toggle="<?php echo $column->name ? 'anwp-sl-tooltip' : ''; ?>" data-tippy-content="<?php echo esc_attr( $column->name ); ?>">
								<?php echo esc_html( $column->abbr ); ?>
							</th>
						<?php endforeach; ?>
					</tr>
					</tfoot>
				</table>
			<?php elseif ( $group_title ) : ?>
				<div class="game-player-group mb-1 anwp-bg-gray-300"><?php echo esc_html( $group_title ); ?></div>
				<table class="anwp-stats-table anwp-sl-team-players-stats w-100 my-0 nowrap cell-border compact stripe">
					<thead>
					<tr>
						<th class="align-middle no-sort"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__team_players_stats__player', __( 'Player', 'sports-leagues' ) ) ); ?></th>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( ( ! empty( $column->groups ) && ! in_array( $group_title, $column->groups, true ) ) || 'hidden' === $column->visibility ) {
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
					foreach ( $group_ids as $player_id ) :

						if ( empty( $player_stats[ $player_id ] ) ) {
							continue;
						}

						$player_obj = sports_leagues()->player->get_player( $player_id );
						?>
						<tr>
							<td class="text-left text-nowrap"><?php echo esc_html( $player_obj->name_short ); ?></td>
							<?php
							foreach ( $stats_columns_season as $column ) :
								if ( ( ! empty( $column->groups ) && ! in_array( $group_title, $column->groups, true ) ) || 'hidden' === $column->visibility ) {
									continue;
								}

								// Get sell value
								$cell_value = sports_leagues()->player_stats->render_team_player_season_stats( $player_stats[ $player_id ], $column );

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
					</tbody>
					<tfoot class="w-100">
					<tr>
						<th class="align-middle"><?php echo esc_html( Sports_Leagues_Text::get_value( 'team__team_players_stats__player', __( 'Player', 'sports-leagues' ) ) ); ?></th>
						<?php
						foreach ( $stats_columns_season as $column ) :
							if ( ( ! empty( $column->groups ) && ! in_array( $group_title, $column->groups, true ) ) || 'hidden' === $column->visibility ) {
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
			endif;
		endforeach;
		else :
			sports_leagues()->load_partial(
				[
					'no_data_text' => Sports_Leagues_Text::get_value( 'team__team_players_stats__no_data', __( 'No Data', 'sports-leagues' ) ),
				],
				'general/no-data'
			);
		endif;
		?>
</div>
