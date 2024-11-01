<?php
/**
 * Player Stats Configurator For Sports Leagues
 *
 * @link       https://anwp.pro
 * @since      0.5.18
 *
 * @package    Sports_Leagues
 * @subpackage Sports_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Must check that the user has the required capability
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
}

/*
|--------------------------------------------------------------------
| Game Player Stats Options
|--------------------------------------------------------------------
*/
$column_options = [
	[
		'type'       => 'simple',
		'name'       => 'Simple',
		'abbr'       => '',
		'visibility' => '',
		'groups'     => [],
		'postfix'    => '',
		'prefix'     => '',
		'digits'     => '',
	],
	[
		'type'       => 'time',
		'name'       => 'Time',
		'abbr'       => '',
		'visibility' => '',
		'groups'     => [],
	],
	[
		'type'       => 'composed',
		'name'       => 'Composed',
		'abbr'       => '',
		'visibility' => '',
		'groups'     => [],
		'separator'  => '',
		'field_1'    => '',
		'field_2'    => '',
		'field_3'    => '',
	],
	[
		'type'       => 'calculated',
		'name'       => 'Calculated',
		'abbr'       => '',
		'visibility' => '',
		'groups'     => [],
		'field_1'    => '',
		'field_2'    => '',
		'calc'       => '',
		'digits'     => '',
		'postfix'    => '',
		'prefix'     => '',
	],
];

$column_season_options = [
	[
		'type'          => 'simple',
		'name'          => 'Player Game Stat',
		'abbr'          => '',
		'visibility'    => '',
		'groups'        => [],
		'game_field_id' => '',
		'result'        => '',
		'postfix'       => '',
		'prefix'        => '',
		'digits'        => '',
	],
	[
		'type'          => 'time',
		'name'          => 'Player Game Time Stat',
		'abbr'          => '',
		'visibility'    => '',
		'groups'        => [],
		'game_field_id' => '',
		'result'        => '',
	],
	[
		'type'       => 'played',
		'name'       => 'Games Played',
		'abbr'       => 'GP',
		'visibility' => '',
		'groups'     => [],
	],
	[
		'type'       => 'composed',
		'name'       => 'Composed',
		'abbr'       => '',
		'visibility' => '',
		'groups'     => [],
		'separator'  => '',
		'field_1'    => '',
		'field_2'    => '',
		'field_3'    => '',
	],
	[
		'type'       => 'calculated',
		'name'       => 'Calculated',
		'abbr'       => '',
		'visibility' => '',
		'groups'     => [],
		'field_1'    => '',
		'field_2'    => '',
		'calc'       => '',
		'digits'     => '',
	],
];

/*
|--------------------------------------------------------------------
| Localization
|--------------------------------------------------------------------
*/
$config_l10n = [
	'are_you_sure'   => esc_html__( 'Are you sure?', 'anwp-football-leagues' ),
	'confirm_delete' => esc_html__( 'Confirm Delete', 'anwp-football-leagues' ),
];

/*
|--------------------------------------------------------------------
| Meta Data
|--------------------------------------------------------------------
*/
$data = [
	'columnOptions'       => $column_options,
	'columnSeasonOptions' => $column_season_options,
	'columnsGame'         => get_option( 'sl_columns_game' ),
	'columnsGameLastId'   => absint( get_option( 'sl_columns_game_last_id' ) ),
	'columnsSeason'       => get_option( 'sl_columns_season' ),
	'statGroups'          => get_option( 'sl_stat_groups' ),
	'columnsSeasonLastId' => get_option( 'sl_columns_season_last_id' ),
	'roster_groups'       => sports_leagues()->config->get_options( 'roster_groups' ),
	'player_roles'        => sports_leagues()->config->get_options( 'position' ),
	'l10n'                => $config_l10n,
	'dbStatIds'           => sports_leagues()->player_stats->get_player_db_stat_ids() ?: [],
	'spinnerUrl'          => admin_url( 'images/spinner.gif' ),
	'rest_root'           => esc_url_raw( rest_url() ),
	'rest_nonce'          => wp_create_nonce( 'wp_rest' ),
];

do_action( 'sports-leagues/stats/before_app' );
?>
<script type="text/javascript">
	var _slPlayerStats = <?php echo wp_json_encode( $data ); ?>;
</script>
<div class="wrap anwp-b-wrap">
	<div class="mb-4 pb-1">
		<h1 class="mb-0">Player Stats Configurator</h1>
	</div>
	<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info my-2">
		<svg class="anwp-icon">
			<use xlink:href="#icon-book"></use>
		</svg>
		<b class="mx-2"><?php echo esc_html__( 'Documentation', 'sports-leagues' ); ?>:</b>
		<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/6/articles/288-player-stats-game"><?php echo esc_html__( 'Player Stats - Game', 'sports-leagues' ); ?></a>
		<span class="mx-2 anwp-small">|</span>
		<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/6/articles/289-player-stats-season"><?php echo esc_html__( 'Player Stats - Season', 'sports-leagues' ); ?></a>
	</div>

	<div id="sl-app-player-stats"></div>
</div>
