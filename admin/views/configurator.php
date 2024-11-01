<?php
/**
 * Sport Configurator
 *
 * @link       https://anwp.pro
 * @since      0.10.0
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

$app_id = apply_filters( 'sports-leagues/configurator-page/vue_app_id', 'sl-app-sport-configurator' );

// Get menu collapsed status
$is_menu_collapsed = 'yes' === get_user_setting( 'anwp-sl-collapsed-menu' );

/*
|--------------------------------------------------------------------
| App Options
|--------------------------------------------------------------------
*/
$app_options = [
	'sports_available' => [
		[
			'title' => esc_html__( 'Basketball', 'sports-leagues' ),
			'slug'  => 'basketball',
		],
		[
			'title' => esc_html__( 'Handball', 'sports-leagues' ),
			'slug'  => 'handball',
		],
		[
			'title' => esc_html__( 'Rugby', 'sports-leagues' ),
			'slug'  => 'rugby',
		],
		[
			'title' => esc_html__( 'American football', 'sports-leagues' ),
			'slug'  => 'football',
		],
		[
			'title' => esc_html__( 'Ice Hockey', 'sports-leagues' ),
			'slug'  => 'ice_hockey',
		],
		[
			'title' => esc_html__( 'Other', 'sports-leagues' ),
			'slug'  => 'other',
		],
	],
];

/*
|--------------------------------------------------------------------
| Data Options
|--------------------------------------------------------------------
*/
$data_options = [
	'position'            => [
		'name_min' => 1,
		'abbr'     => true,
		'id'       => true,
		'title'    => 'Position',
	],
	'roster_status'       => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => false,
		'title'    => 'Roster Status',
	],
	'roster_groups'       => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => true,
		'title'    => 'Roster Group',
	],
	'game_player_groups'  => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => true,
		'title'    => 'Player Game Group',
	],
	'staff_roster_groups' => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => true,
		'title'    => 'Staff Roster Group',
	],
	'game_staff_groups'   => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => true,
		'title'    => 'Staff Game Group',
	],
	'official_groups'     => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => true,
		'title'    => 'Official Group',
	],
	'game_team_stats'     => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => true,
		'title'    => 'Game Team Stat',
	],
	'tournament_types'    => [
		'name_min' => 3,
		'abbr'     => false,
		'id'       => true,
		'title'    => 'Tournament Type',
	],
];

/*
|--------------------------------------------------------------------
| Recommended Data
|--------------------------------------------------------------------
*/
$recommended_config = [
	'basketball' => [
		'title'                  => esc_html__( 'Basketball', 'sports-leagues' ),
		'icon'                   => '🏀',
		'position'               => esc_html_x( 'Point Guard|P-G;Shooting Guard|S-G;Small Forward|S-F;Power Forward|P-F;Center|C;Power Forward and Center|C-F;Point Guard and Shooting Guard|G;Forward Center|F-C;Small Forward and Power Forward|F;Shooting Guard and Small Forward|G-F', 'config basketball: position', 'sports-leagues' ),
		'roster_status'          => esc_html_x( 'in team;on loan;on trial', 'config basketball: roster-status', 'sports-leagues' ),
		'game_player_groups'     => esc_html_x( 'Starters;Bench', 'config basketball: game-player-groups', 'sports-leagues' ),
		'game_team_stats'        => esc_html_x( 'Field Goals %;Three Pointers %;Free Throws %;Rebounds;Assists;Steals;Blocks;Turnovers;Fouls', 'config basketball: game-team-stats', 'sports-leagues' ),
		'num_of_periods'         => esc_html_x( '4', 'config basketball: num_of_periods', 'sports-leagues' ),
		'standing_str__all_win'  => esc_html_x( 'Wins|W', 'config basketball: standing_str__all_win', 'sports-leagues' ),
		'standing_str__all_loss' => esc_html_x( 'Losses|L', 'config basketball: standing_str__all_loss', 'sports-leagues' ),
		'standing_str__ratio'    => esc_html_x( 'Winning Percentage|PCT', 'config basketball: standing_str__ratio', 'sports-leagues' ),
	],
	'ice_hockey' => [
		'title'                  => esc_html__( 'Ice Hockey', 'sports-leagues' ),
		'icon'                   => '🏒',
		'position'               => esc_html_x( 'Goaltender|G;Left Defenceman|L;Right Defenceman|R;Left Wing|LW;Centre|C;Right Wing|RW', 'config ice_hockey: position', 'sports-leagues' ),
		'roster_status'          => esc_html_x( 'in team;on loan;on trial', 'config ice_hockey: roster-status', 'sports-leagues' ),
		'roster_groups'          => esc_html_x( 'Goalies;Defense;Forwards', 'config ice_hockey: roster-groups', 'sports-leagues' ),
		'game_player_groups'     => esc_html_x( 'Goalies;Defense;Forwards', 'config ice_hockey: game-player-groups', 'sports-leagues' ),
		'game_team_stats'        => esc_html_x( 'Shots;Hits;Faceoffs Won;Power Play Opportunities;Total Penalties;Penalty Minutes;Blocked Shots;Takeaways;Giveaways', 'config ice_hockey: game-team-stats', 'sports-leagues' ),
		'num_of_periods'         => esc_html_x( '3', 'config ice_hockey: num_of_periods', 'sports-leagues' ),
		'points_ft_win'          => esc_html_x( '2', 'config ice_hockey: points_ft_win', 'sports-leagues' ),
		'points_ov_win'          => esc_html_x( '2', 'config ice_hockey: points_ov_win', 'sports-leagues' ),
		'points_pen_win'         => esc_html_x( '2', 'config ice_hockey: points_pen_win', 'sports-leagues' ),
		'points_ft_loss'         => esc_html_x( '0', 'config ice_hockey: points_ft_loss', 'sports-leagues' ),
		'points_ov_loss'         => esc_html_x( '1', 'config ice_hockey: points_ov_loss', 'sports-leagues' ),
		'points_pen_loss'        => esc_html_x( '1', 'config ice_hockey: points_pen_loss', 'sports-leagues' ),
		'standing_str__played'   => esc_html_x( 'Games Played|GP', 'config ice_hockey: standing_str__played', 'sports-leagues' ),
		'standing_str__all_win'  => esc_html_x( 'Wins|W', 'config ice_hockey: standing_str__all_win', 'sports-leagues' ),
		'standing_str__ft_loss'  => esc_html_x( 'Losses|L', 'config ice_hockey: standing_str__ft_loss', 'sports-leagues' ),
		'standing_str__ov_loss'  => esc_html_x( 'Overtime Losses|OTL', 'config ice_hockey: standing_str_ov_loss', 'sports-leagues' ),
		'standing_str__pen_loss' => esc_html_x( 'Shootout Losses|SOL', 'config ice_hockey: standing_str_pen_loss', 'sports-leagues' ),
		'standing_str__pts'      => esc_html_x( 'Total Points|PTS', 'config ice_hockey: standing_str__pts', 'sports-leagues' ),
		'standing_str__sf'       => esc_html_x( 'Goals For|GF', 'config ice_hockey: standing_str__sf', 'sports-leagues' ),
		'standing_str__sa'       => esc_html_x( 'Goals Against|GA', 'config ice_hockey: standing_str__sa', 'sports-leagues' ),
		'standing_str__sd'       => esc_html_x( 'Goal Differential|DIFF', 'config ice_hockey: standing_str__sd', 'sports-leagues' ),
	],
	'handball'   => [
		'title'                  => esc_html__( 'Handball', 'sports-leagues' ),
		'icon'                   => '🤾',
		'position'               => esc_html_x( 'Goalkeeper|GK;Left Back|LB;Centre Back|CB;Right Back|RB;Left Winger|LW;Right Winger|RW;Line Player|LP', 'config handball: position', 'sports-leagues' ),
		'roster_status'          => esc_html_x( 'in team;on loan;on trial', 'config handball: roster-status', 'sports-leagues' ),
		'roster_groups'          => esc_html_x( 'Goalkeepers;Field players', 'config handball: roster-groups', 'sports-leagues' ),
		'game_player_groups'     => esc_html_x( 'Lineups;Substitutes', 'config handball: game-player-groups', 'sports-leagues' ),
		'game_team_stats'        => esc_html_x( 'Total Shots;Total Goals;7m Shots;7m Goals;Total Misses;Technical Faults;2 min Suspensions;Warnings;Disqualifications', 'config handball: game-team-stats', 'sports-leagues' ),
		'num_of_periods'         => esc_html_x( '2', 'config handball: num_of_periods', 'sports-leagues' ),
		'points_ft_win'          => esc_html_x( '2', 'config handball: points_ft_win', 'sports-leagues' ),
		'points_draw'            => esc_html_x( '1', 'config handball: points_draw', 'sports-leagues' ),
		'points_ft_loss'         => esc_html_x( '0', 'config handball: points_ft_loss', 'sports-leagues' ),
		'standing_str__played'   => esc_html_x( 'Played|Pld', 'config handball: standing_str__played', 'sports-leagues' ),
		'standing_str__all_win'  => esc_html_x( 'Won|W', 'config handball: standing_str__all_win', 'sports-leagues' ),
		'standing_str__draw'     => esc_html_x( 'Drawn|D', 'config handball: standing_str__draw', 'sports-leagues' ),
		'standing_str__all_loss' => esc_html_x( 'Lost|L', 'config handball: standing_str__all_loss', 'sports-leagues' ),
		'standing_str__sf'       => esc_html_x( 'Goals For|GF', 'config handball: standing_str__sf', 'sports-leagues' ),
		'standing_str__sa'       => esc_html_x( 'Goals Against|GA', 'config handball: standing_str__sa', 'sports-leagues' ),
		'standing_str__sd'       => esc_html_x( 'Goal Difference|GD', 'config handball: standing_str__sd', 'sports-leagues' ),
		'standing_str__pts'      => esc_html_x( 'Points|Pts', 'config handball: standing_str__pts', 'sports-leagues' ),
	],
	'rugby'      => [
		'title'                  => esc_html__( 'Rugby', 'sports-leagues' ),
		'icon'                   => '🏉',
		'position'               => esc_html_x( 'Full back;Right wing;Outside centre;Inside centre;Left wing;Fly-half;Scrum-half;Loosehead prop;Hooker;Tighthead prop;Lock;Blindside flanker;Openside flanker;Number eight', 'config rugby: position', 'sports-leagues' ),
		'roster_status'          => esc_html_x( 'in team;on loan;on trial', 'config rugby: roster-status', 'sports-leagues' ),
		'game_player_groups'     => esc_html_x( 'Lineups;Substitutes', 'config rugby: game-player-groups', 'sports-leagues' ),
		'game_team_stats'        => esc_html_x( 'Possession;Territory;Clean breaks;Defenders beaten;Rucks won%;Mauls won;Offloads;Turnovers won;Tackles made;Tackles missed;Tackle success %;Lineouts won;Lineouts lost;Scrums won;Scrums lost;Restarts won;Restarts lost;Penalties conceded;Yellow cards;Red cards', 'config rugby: game-team-stats', 'sports-leagues' ),
		'num_of_periods'         => esc_html_x( '2', 'config rugby: num_of_periods', 'sports-leagues' ),
		'points_ft_win'          => esc_html_x( '4', 'config rugby: points_ft_win', 'sports-leagues' ),
		'points_draw'            => esc_html_x( '2', 'config rugby: points_draw', 'sports-leagues' ),
		'points_ft_loss'         => esc_html_x( '0', 'config rugby: points_ft_loss', 'sports-leagues' ),
		'standing_str__played'   => esc_html_x( 'Played|P', 'config rugby: standing_str__played', 'sports-leagues' ),
		'standing_str__all_win'  => esc_html_x( 'Win|W', 'config rugby: standing_str__all_win', 'sports-leagues' ),
		'standing_str__draw'     => esc_html_x( 'Draw|D', 'config rugby: standing_str__draw', 'sports-leagues' ),
		'standing_str__all_loss' => esc_html_x( 'Lose|L', 'config rugby: standing_str__all_loss', 'sports-leagues' ),
		'standing_str__sf'       => esc_html_x( 'Points For|PF', 'config rugby: standing_str__sf', 'sports-leagues' ),
		'standing_str__sa'       => esc_html_x( 'Points Against|PA', 'config rugby: standing_str__sa', 'sports-leagues' ),
		'standing_str__sd'       => esc_html_x( 'Points Difference|PD', 'config rugby: standing_str__sd', 'sports-leagues' ),
		'standing_str__bpts'     => esc_html_x( 'Bonus Points|BP', 'config rugby: standing_str__bpts', 'sports-leagues' ),
		'standing_str__pts'      => esc_html_x( 'Points|Pts', 'config rugby: standing_str__pts', 'sports-leagues' ),
	],
	'football'   => [
		'title'                => esc_html__( 'American football', 'sports-leagues' ),
		'icon'                 => '🏈',
		'position'             => esc_html_x( 'Safety|S;Cornerback|CB;Defensive end|DE;Defensive tackle|DT;Fullback|FB;Guard|G;Linebacker|LB;Long snapper|LS;Offensive tackle|OT;Punter|P;Placekicker|PK;Quarterback|QB;Running back|RB;Tight end|TE;Wide receiver|WR', 'config football: position', 'sports-leagues' ),
		'roster_status'        => esc_html_x( 'in team;on loan;on trial', 'config football: roster-status', 'sports-leagues' ),
		'game_team_stats'      => esc_html_x( 'Total 1st Downs;Passing 1st Downs;Rushing 1st Downs;1st Downs from Penalties;Total Plays;Total Yards;Yards per Play;Total Drives;Total Passing;Yards per Pass;Interceptions Thrown;Sacks-Yards Lost;Total Rushing;Rushing Attempts;Yards per Rush;Penalties;Total Turnovers;Fumbles lost;Turnovers Interceptions;Possession;Total Interceptions;Total Safeties', 'config football: game-team-stats', 'sports-leagues' ),
		'num_of_periods'       => esc_html_x( '4', 'config football: num_of_periods', 'sports-leagues' ),
		'bonus_points_hide'    => 'hide',
		'standing_points_hide' => 'hide',
		'penalty_hide'         => 'hide',
	],
];

$standing_columns = [
	'standing_str__played',
	'standing_str__all_win',
	'standing_str__ft_win',
	'standing_str__ov_win',
	'standing_str__pen_win',
	'standing_str__draw',
	'standing_str__all_loss',
	'standing_str__ft_loss',
	'standing_str__ov_loss',
	'standing_str__pen_loss',
	'standing_str__sf',
	'standing_str__sa',
	'standing_str__sd',
	'standing_str__ratio',
	'standing_str__bpts',
	'standing_str__pts',
];

// Parse recommended config
foreach ( $recommended_config as $sport_slug => $sport_config ) {
	foreach ( [ 'position' ] as $field_slug ) {
		$parsed_data = [];

		if ( ! empty( $sport_config[ $field_slug ] ) ) {
			foreach ( explode( ';', $sport_config[ $field_slug ] ) as $field_data ) {
				if ( ! empty( $field_data ) ) {
					$field_data_inner = explode( '|', $field_data );

					$parsed_data[] = [
						'name' => $field_data_inner[0],
						'id'   => $field_data_inner[0],
						'abbr' => isset( $field_data_inner[1] ) ? $field_data_inner[1] : '',
					];
				}
			}
		}

		$recommended_config[ $sport_slug ][ $field_slug ] = $parsed_data;
	}

	foreach ( [ 'roster_status' ] as $field_slug_2 ) {
		$parsed_data = [];

		if ( ! empty( $sport_config[ $field_slug_2 ] ) ) {
			foreach ( explode( ';', $sport_config[ $field_slug_2 ] ) as $field_data_2 ) {
				if ( ! empty( $field_data_2 ) ) {
					$parsed_data[] = $field_data_2;
				}
			}
		}

		$recommended_config[ $sport_slug ][ $field_slug_2 ] = $parsed_data;
	}

	foreach ( [ 'roster_groups', 'game_player_groups', 'game_team_stats' ] as $field_slug_3 ) {
		$parsed_data = [];

		if ( ! empty( $sport_config[ $field_slug_3 ] ) ) {
			foreach ( explode( ';', $sport_config[ $field_slug_3 ] ) as $field_data_3 ) {
				if ( ! empty( $field_data_3 ) ) {
					$parsed_data[] = [
						'name' => $field_data_3,
						'id'   => $field_data_3,
					];
				}
			}
		}

		$recommended_config[ $sport_slug ][ $field_slug_3 ] = $parsed_data;
	}

	foreach ( $standing_columns as $field_slug_4 ) {
		$parsed_data = [
			'name' => '',
			'abbr' => '',
		];

		if ( ! empty( $sport_config[ $field_slug_4 ] ) ) {
			$field_data_4 = explode( '|', $sport_config[ $field_slug_4 ] );

			$parsed_data = [
				'name' => $field_data_4[0],
				'abbr' => $field_data_4[1],
			];
		}

		$recommended_config[ $sport_slug ][ $field_slug_4 ] = $parsed_data;
	}
}

?>
<script type="text/javascript">
	var _slConfiguratorDataOptions = <?php echo wp_json_encode( $data_options ); ?>;
	var _slConfiguratorOptions     = <?php echo wp_json_encode( $app_options ); ?>;
	var _slConfiguratorRecommended = <?php echo wp_json_encode( $recommended_config ); ?>;
	var _slConfigurator            = <?php echo wp_json_encode( get_option( 'sports_leagues_config', [] ) ); ?>;
</script>
<div class="wrap anwp-b-wrap">
	<div class="mb-4 pb-1">
		<h1 class="mb-0"><?php echo esc_html__( 'Sports Leagues', 'sports-leagues' ) . ' :: ' . esc_html__( 'Sport Configurator', 'sports-leagues' ); ?></h1>
		<ul class="subsubsub">
			<li><a href="#" class="current"><?php echo esc_html__( 'Sport Configurator', 'sports-leagues' ); ?> </a> |</li>
			<li><a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=sl-player-stats' ) ); ?>"><?php echo esc_html__( 'Player Statistics', 'sports-leagues' ); ?></a> |</li>
			<li><a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=sports_leagues_event' ) ); ?>"><?php echo esc_html__( 'Game Events', 'sports-leagues' ); ?></a></li>
		</ul>
		<div class="clear"></div>
	</div>

	<div class="d-flex mt-2" id="anwp-sl-metabox-page-nav">
		<div class="anwp-sl-menu-wrapper mr-3 d-none d-md-block sticky-top align-self-start anwp-flex-none <?php echo esc_attr( $is_menu_collapsed ? 'anwp-sl-collapsed-menu' : '' ); ?>" style="top: 50px;">

			<button id="anwp-sl-publish-click-proxy" class="w-100 button button-primary py-2 mb-4 d-flex align-items-center justify-content-center" type="submit">
				<svg class="anwp-icon anwp-icon--feather anwp-icon--s16"><use xlink:href="#icon-save"></use></svg>
				<span class="ml-2"><?php echo esc_html__( 'Save', 'sports-leagues' ); ?></span>
				<span class="spinner m-0"></span>
			</button>

			<ul class="m-0 p-0 list-unstyled">
				<?php
				$nav_items = [
					[
						'icon'  => 'gear',
						'label' => esc_html__( 'General Info', 'sports-leagues' ),
						'slug'  => 'anwp-sl-general-metabox',
					],
					[
						'icon'  => 'jersey',
						'label' => esc_html__( 'Player Position', 'sports-leagues' ),
						'slug'  => 'anwp-sl-player-position-metabox',
					],
					[
						'icon'  => 'jersey',
						'label' => esc_html__( 'Player Roster Status', 'sports-leagues' ),
						'slug'  => 'anwp-sl-player-roster-status-metabox',
					],
					[
						'icon'  => 'jersey',
						'label' => esc_html__( 'Player Roster Groups', 'sports-leagues' ),
						'slug'  => 'anwp-sl-player-roster-groups-metabox',
					],
					[
						'icon'  => 'jersey',
						'label' => esc_html__( 'Player Game Groups', 'sports-leagues' ),
						'slug'  => 'anwp-sl-player-game-groups-metabox',
					],
					[
						'icon'  => 'organization',
						'label' => esc_html__( 'Staff Roster Groups', 'sports-leagues' ),
						'slug'  => 'anwp-sl-staff-roster-groups-metabox',
					],
					[
						'icon'  => 'organization',
						'label' => esc_html__( 'Staff Game Groups', 'sports-leagues' ),
						'slug'  => 'anwp-sl-staff-game-groups-metabox',
					],
					[
						'icon'  => 'organization',
						'label' => esc_html__( 'Official Groups', 'sports-leagues' ),
						'slug'  => 'anwp-sl-official-groups-metabox',
					],
					[
						'icon'  => 'graph',
						'label' => esc_html__( 'Game Team Statistics', 'sports-leagues' ),
						'slug'  => 'anwp-sl-game-team-stats-metabox',
					],
					[
						'icon'  => 'law',
						'label' => esc_html__( 'Standing Points', 'sports-leagues' ),
						'slug'  => 'anwp-sl-points-awarded-metabox',
					],
					[
						'icon'  => 'repo-forked',
						'label' => esc_html__( 'Game Structure', 'sports-leagues' ),
						'slug'  => 'anwp-sl-number-of-periods-metabox',
					],
					[
						'icon'  => 'note',
						'label' => esc_html__( 'Standing Column Names', 'sports-leagues' ),
						'slug'  => 'anwp-sl-standing-columns-text-metabox',
					],
					[
						'icon'  => 'note',
						'label' => esc_html__( 'Team Series (letter)', 'sports-leagues' ),
						'slug'  => 'anwp-sl-team-series-metabox',
					],
					[
						'icon'  => 'repo-forked',
						'label' => esc_html__( 'Tournament Types', 'sports-leagues' ),
						'slug'  => 'anwp-sl-tournament-type-metabox',
					],
				];

				/**
				 * Modify metabox nav items
				 *
				 * @since 0.10.0
				 */
				$nav_items = apply_filters( 'sports-leagues/configurator/metabox_nav_items', $nav_items );

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo sports_leagues()->helper->create_metabox_navigation( $nav_items );

				/**
				 * Fires at the bottom of Metabox Nav.
				 *
				 * @since 0.10.0
				 */
				do_action( 'sports-leagues/configurator/metabox_nav_bottom' );
				?>
			</ul>

		</div>
		<div class="flex-grow-1 anwp-min-width-0 mb-4">

			<div class="anwp-border anwp-border-blue-300 p-3 anwp-bg-blue-100 mb-4 d-flex align-items-center">
				<svg class="anwp-icon anwp-icon--s36 anwp-icon--octi mr-3 anwp-text-blue-800 anwp-fill-current"><use xlink:href="#icon-info"></use></svg>
				<p class="my-0 anwp-text-sm anwp-text-blue-900">
					It is the most important part of your app, the basis of your sports data.
					<br>Please spend a bit more time on this page to set up everything properly.
					You can use predefined configurations for different sports as a hint.
				</p>
			</div>

			<div id="<?php echo esc_attr( $app_id ); ?>"></div>

			<?php
			/**
			 * Fires at the bottom of Metabox.
			 *
			 * @since 0.10.0
			 */
			do_action( 'sports-leagues/configurator/metabox_bottom' );
			?>
		</div>
	</div>

	<?php do_action( 'sports-leagues/configurator-page/after_config' ); ?>
</div>
