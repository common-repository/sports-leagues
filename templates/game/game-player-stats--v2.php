<?php
/**
 * The Template for displaying Game >> Player Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/sports-leagues/game/game-player-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       Sports-Leagues/Templates
 * @since         0.6.0
 *
 * @version       0.13.0
 */
// phpcs:disable WordPress.NamingConventions.ValidVariableName

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_team'  => '',
		'away_team'  => '',
		'home_logo'  => '',
		'away_logo'  => '',
		'home_title' => '',
		'away_title' => '',
		'game_id'    => '',
		'header'     => true,
	]
);

/*
|--------------------------------------------------------------------
| Prepare Stats Data
|--------------------------------------------------------------------
*/
$stats_players_columns = json_decode( get_option( 'sl_columns_game' ) );

if ( empty( $stats_players_columns ) ) {
	return;
}

$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : '';

// Load previous version (before stat groups)
if ( empty( $stat_groups ) ) {
	return sports_leagues()->load_partial( $data, 'game/game-player-stats', 'v1' );
}

$stats_players_home_data = sports_leagues()->player_stats->get_game_stats( $data->game_id, $data->home_team );
$stats_players_away_data = sports_leagues()->player_stats->get_game_stats( $data->game_id, $data->away_team );

if ( empty( (array) $stats_players_home_data ) && empty( (array) $stats_players_away_data ) ) {
	return;
}

$temp_players         = sports_leagues()->game->get_temp_players( $data->game_id );
$grouped_home_players = sports_leagues()->player_stats->get_game_stats_grouped( $data->home_team, $data->season_id, $stat_groups, $stats_players_home_data, $data->players_home, $temp_players );
$grouped_away_players = sports_leagues()->player_stats->get_game_stats_grouped( $data->away_team, $data->season_id, $stat_groups, $stats_players_away_data, $data->players_away, $temp_players );

/**
 * Hook: sports-leagues/tmpl-game/player_stats_before
 *
 * @param object $data Game data
 *
 * @since 0.5.18
 */
do_action( 'sports-leagues/tmpl-game/player_stats_before', $data );

$stats_class = 'yes' === Sports_Leagues_Customizer::get_value( 'general', 'stats_text_monospace' ) ? ' anwp-text-monospace ' : 'anwp-text-sm';
?>
<div class="anwp-section game__player-stats game__player-stats--v2">

	<?php if ( Sports_Leagues::string_to_bool( $data->header ) ) : ?>
		<div class="anwp-block-header mb-0">
			<?php echo esc_html( Sports_Leagues_Text::get_value( 'game__player_stats__players_statistics', __( 'Players Statistics', 'sports-leagues' ) ) ); ?>
		</div>
	<?php endif; ?>

	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->helper->render_team_header( $data->home_logo, $data->home_title, $data->home_team, true );

	foreach ( $grouped_home_players as $group_data ) :

		if ( empty( $group_data['ids'] ) ) {
			continue;
		}

		if ( count( $stat_groups ) > 1 ) :
			?>
			<div class="game-player-group mb-1 anwp-bg-gray-300"><?php echo esc_html( $group_data['name'] ); ?></div>
		<?php endif; ?>

		<table class="anwp-stats-table anwp-sl-game-player-stats w-100 my-0 nowrap cell-border compact stripe">

			<thead>
			<tr class="small">
				<th class="align-middle no-sort"><?php echo esc_html( Sports_Leagues_Text::get_value( 'game__player_stats__player', __( 'Player', 'sports-leagues' ) ) ); ?></th>
				<?php
				foreach ( $stats_players_columns as $column ) :
					if ( ( ! empty( $column->groups ) && ! in_array( $group_data['id'], $column->groups, true ) ) || 'hidden' === $column->visibility ) {
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
			foreach ( $group_data['ids'] as $player_id ) :

				if ( empty( $stats_players_home_data->{$player_id} ) ) {
					continue;
				}
				?>
				<tr>
					<td class="text-left text-nowrap">
						<?php
						if ( mb_strpos( $player_id, 'temp__' ) === false ) {
							echo esc_html( sports_leagues()->player->get_player( $player_id )->name_short );
						} elseif ( ! empty( $temp_players[ $player_id ] ) ) {
							echo esc_html( $temp_players[ $player_id ]->title );
						}
						?>
					</td>
					<?php
					foreach ( $stats_players_columns as $column ) :

						if ( ( ! empty( $column->groups ) && ! in_array( $group_data['id'], $column->groups, true ) ) || 'hidden' === $column->visibility ) {
							continue;
						}

						$cell_value = sports_leagues()->player_stats->render_player_game_stats( $stats_players_home_data->{$player_id}, $column );
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
						<td class="text-right anwp-w-15 <?php echo esc_attr( $stats_class ); ?>" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
							<?php echo esc_html( $cell_value ); ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	endforeach;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sports_leagues()->helper->render_team_header( $data->away_logo, $data->away_title, $data->away_team, false );

	foreach ( $grouped_away_players as $group_data ) :

		if ( empty( $group_data['ids'] ) ) {
			continue;
		}

		if ( count( $stat_groups ) > 1 ) :
			?>
			<div class="game-player-group mb-1 anwp-bg-gray-300"><?php echo esc_html( $group_data['name'] ); ?></div>
		<?php endif; ?>

		<table class="anwp-stats-table anwp-sl-game-player-stats w-100 my-0 nowrap cell-border compact stripe">

			<thead>
			<tr class="small">
				<th class="align-middle no-sort"><?php echo esc_html( Sports_Leagues_Text::get_value( 'game__player_stats__player', __( 'Player', 'sports-leagues' ) ) ); ?></th>
				<?php
				foreach ( $stats_players_columns as $column ) :
					if ( ( ! empty( $column->groups ) && ! in_array( $group_data['id'], $column->groups, true ) ) || 'hidden' === $column->visibility ) {
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
			foreach ( $group_data['ids'] as $player_id ) :

				if ( empty( $stats_players_away_data->{$player_id} ) ) {
					continue;
				}
				?>
				<tr>
					<td class="text-left text-nowrap">
						<?php
						if ( mb_strpos( $player_id, 'temp__' ) === false ) {
							echo esc_html( sports_leagues()->player->get_player( $player_id )->name_short );
						} elseif ( ! empty( $temp_players[ $player_id ] ) ) {
							echo esc_html( $temp_players[ $player_id ]->title );
						}
						?>
					</td>
					<?php
					foreach ( $stats_players_columns as $column ) :

						if ( ( ! empty( $column->groups ) && ! in_array( $group_data['id'], $column->groups, true ) ) || 'hidden' === $column->visibility ) {
							continue;
						}

						$cell_value = sports_leagues()->player_stats->render_player_game_stats( $stats_players_away_data->{$player_id}, $column );
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
						<td class="text-right anwp-w-15 <?php echo esc_attr( $stats_class ); ?>" <?php echo $data_order ? sprintf( 'data-order="%s"', esc_attr( $data_order ) ) : ''; ?>>
							<?php echo esc_html( $cell_value ); ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	endforeach;
	?>
</div>
