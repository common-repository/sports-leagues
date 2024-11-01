<?php
/**
 * Sports Leagues :: Upgrade
 *
 * @since   0.5.13
 * @package Sports_Leagues
 */

class Sports_Leagues_Upgrade {

	/**
	 * Parent plugin class.
	 *
	 * @var    Sports_Leagues
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'update_db_check' ], 1 );
		add_action( 'init', [ $this, 'version_upgrade' ], 2 );
		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Check Plugin's DB version.
	 *
	 * @since 0.1.0
	 */
	public function update_db_check() {

		if ( (int) get_option( 'sl_db_version' ) < Sports_Leagues::DB_VERSION ) {
			$this->update_db();
		}
	}


	/**
	 * Update plugin DB
	 *
	 * @since 0.1.0
	 */
	public function update_db() {

		global $wpdb;

		$charset_collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$charset_collate = $wpdb->get_charset_collate();
		}

		/**
		 * Remove OLD Stats keys
		 */
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}sl_events';" ) ) {
			if ( $wpdb->get_var( "SHOW KEYS FROM `{$wpdb->prefix}sl_events` WHERE Key_name = 'type' AND Column_name = 'type';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}sl_events DROP KEY `type`;" );
			}

			if ( $wpdb->get_var( "SHOW KEYS FROM `{$wpdb->prefix}sl_events` WHERE Key_name = 'team_id' AND Column_name = 'team_id';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}sl_events DROP KEY `team_id`;" );
			}

			if ( $wpdb->get_var( "SHOW KEYS FROM `{$wpdb->prefix}sl_events` WHERE Key_name = 'player_id' AND Column_name = 'player_id';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}sl_events DROP KEY `player_id`;" );
			}

			if ( $wpdb->get_var( "SHOW KEYS FROM `{$wpdb->prefix}sl_events` WHERE Key_name = 'team_id_opp' AND Column_name = 'team_id_opp';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}sl_events DROP KEY `team_id_opp`;" );
			}
		}

		$sql = "
CREATE TABLE {$wpdb->prefix}sl_games (
  game_id bigint(20) UNSIGNED NOT NULL,
  tournament_id bigint(20) UNSIGNED NOT NULL,
  stage_id bigint(20) UNSIGNED NOT NULL,
  league_id bigint(20) UNSIGNED NOT NULL,
  season_id bigint(20) UNSIGNED NOT NULL,
  group_id int(10) UNSIGNED NOT NULL,
  round_id int(10) UNSIGNED NOT NULL,
  home_team bigint(20) UNSIGNED NOT NULL,
  away_team bigint(20) UNSIGNED NOT NULL,
  kickoff datetime NOT NULL default '0000-00-00 00:00:00',
  kickoff_gmt datetime NOT NULL default '0000-00-00 00:00:00',
  venue_id bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  game_day tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  status varchar(20) DEFAULT '' NOT NULL,
  finished tinyint(1) NOT NULL DEFAULT '0',
  priority tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  home_scores varchar(10) NOT NULL DEFAULT '',
  away_scores varchar(10) NOT NULL DEFAULT '',
  home_points tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  away_points tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  home_bonus_points tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  away_bonus_points tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  home_outcome varchar(10) NOT NULL DEFAULT '',
  away_outcome varchar(10) NOT NULL DEFAULT '',
  special_status varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY  (game_id),
  KEY tournament_id (tournament_id),
  KEY stage_id (stage_id),
  KEY home_team (home_team),
  KEY away_team (away_team),
  KEY kickoff (kickoff),
  KEY venue_id (venue_id),
  KEY status (status),
  KEY finished (finished),
  KEY priority (priority)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}sl_events (
  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  type varchar(200) DEFAULT '' NOT NULL,
  game_id bigint(20) UNSIGNED NOT NULL,
  team_id bigint(20) UNSIGNED NOT NULL,
  team_id_opp bigint(20) UNSIGNED NOT NULL,
  time varchar(50) DEFAULT '' NOT NULL,
  player_id bigint(20) UNSIGNED NOT NULL,
  sorting int(10) UNSIGNED NOT NULL,
  score varchar(50) DEFAULT '' NOT NULL,
  comment text NOT NULL,
  params longtext NOT NULL,
  PRIMARY KEY  (id),
  KEY game_id (game_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}sl_missing_players (
  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  game_id bigint(20) UNSIGNED NOT NULL,
  player_id bigint(20) UNSIGNED NOT NULL,
  team_id bigint(20) UNSIGNED NOT NULL,
  reason varchar(20) NOT NULL DEFAULT '',
  comment text NOT NULL,
  PRIMARY KEY  (id),
  KEY game_id (game_id),
  KEY player_id (player_id),
  KEY team_id (team_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}sl_player_statistics (
  game_id bigint(20) UNSIGNED NOT NULL,
  player_id bigint(20) UNSIGNED NOT NULL,
  team_id bigint(20) UNSIGNED NOT NULL,
  c_id__0 tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY  (game_id,team_id,player_id),
  KEY player_id (player_id),
  KEY game_id (game_id),
  KEY c_id__0 (c_id__0),
  KEY team_id (team_id)
) $charset_collate;
";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		$success = empty( $wpdb->last_error );

		if ( (int) get_option( 'sl_db_version' ) < Sports_Leagues::DB_VERSION && $success ) {
			update_option( 'sl_db_version', Sports_Leagues::DB_VERSION, true );
		}

		// v0.13.0 - special column for migration
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}sl_player_stats';" ) ) {
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}sl_player_stats` LIKE 'migrated';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}sl_player_stats ADD COLUMN `migrated` TINYINT UNSIGNED NOT NULL DEFAULT '0';" );
			}
		}

		return $success;
	}

	/**
	 * Maybe run version upgrade
	 */
	public function version_upgrade() {
		$version_saved   = get_option( 'sl_version', '0.1.0' );
		$version_current = Sports_Leagues::VERSION;

		if ( $version_saved === $version_current ) {
			return;
		}

		if ( version_compare( $version_saved, '0.5.14', '<' ) ) {
			$this->finish_upgrade();
		}

		if ( version_compare( $version_saved, '0.10.0', '<' ) ) {
			$this->convert_old_config_to_new_format();
		}

		if ( version_compare( $version_saved, '0.10.0', '=' ) ) {
			$this->reset_v010_cache();
		}

		if ( version_compare( $version_saved, '0.11.0', '<' ) ) {
			$this->upgrade_0_11_customizer();
		}

		if ( version_compare( $version_saved, '0.12.5.1', '<' ) ) {
			$this->upgrade_0_12_6_football();
		}

		/*
		|--------------------------------------------------------------------
		| Introduce Data Schema in v0.13
		|--------------------------------------------------------------------
		*/
		if ( version_compare( $version_saved, '0.12.8', '<' ) ) {
			update_option( 'sl_data_schema', empty( $this->get_toolbox_updater_tasks( 'tasks' ) ) ? 13 : 12, true );
		}

		update_option( 'sl_version', $version_current );

		/*
		|--------------------------------------------------------------------
		| Setup Notices
		|--------------------------------------------------------------------
		*/
		add_action( 'wp_loaded', [ $this, 'hide_setup_notices' ] );

		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_print_styles', [ $this, 'add_setup_notices' ] );
		}
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'sports-leagues/api-toolbox-updater',
			'/get_toolbox_updater_tasks/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_toolbox_updater_tasks' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/api-toolbox-updater',
			'/sync_table__player_statistics/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_sync_table_player_statistics' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/api-toolbox-updater',
			'/migrate_table__sl_player_stats/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_migrate_table_sl_player_stats' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

	}

	/**
	 * v0.12.6
	 */
	public function upgrade_0_12_6_football() {
		if ( 'am_football' === Sports_Leagues_Config::get_value( 'sport' ) ) {
			$sports_leagues_config          = get_option( 'sports_leagues_config', [] );
			$sports_leagues_config['sport'] = 'football';

			update_option( 'sports_leagues_config', $sports_leagues_config, true );
		}
	}

	/**
	 * v0.11.0
	 */
	public function upgrade_0_11_customizer() {

		$customizer_settings = [];

		if ( Sports_Leagues_Options::get_value( 'load_alternative_page_layout' ) ) {
			if ( ! isset( $customizer_settings['general'] ) ) {
				$customizer_settings['general'] = [];
			}

			$customizer_settings['general']['load_alternative_page_layout'] = Sports_Leagues_Options::get_value( 'load_alternative_page_layout' );
		}

		if ( 'no' === Sports_Leagues_Options::get_value( 'hide_post_titles' ) ) {
			if ( ! isset( $customizer_settings['general'] ) ) {
				$customizer_settings['general'] = [];
			}

			$customizer_settings['general']['hide_post_titles'] = 'no';
		}

		if ( Sports_Leagues_Options::get_value( 'team_roster_layout' ) ) {
			if ( ! isset( $customizer_settings['roster'] ) ) {
				$customizer_settings['roster'] = [];
			}

			$customizer_settings['roster']['team_roster_layout'] = 'grid';
		}

		if ( Sports_Leagues_Options::get_value( 'default_player_photo' ) ) {
			if ( ! isset( $customizer_settings['player'] ) ) {
				$customizer_settings['player'] = [];
			}

			$customizer_settings['player']['default_player_photo'] = Sports_Leagues_Options::get_value( 'default_player_photo' );
		}

		if ( 'no' === Sports_Leagues_Options::get_value( 'link_to_the_game_player_stats' ) ) {
			if ( ! isset( $customizer_settings['player'] ) ) {
				$customizer_settings['player'] = [];
			}

			$customizer_settings['player']['link_to_the_game_player_stats'] = 'no';
		}

		if ( 'title' === Sports_Leagues_Options::get_value( 'team_name_slim' ) ) {
			if ( ! isset( $customizer_settings['game_list'] ) ) {
				$customizer_settings['game_list'] = [];
			}

			$customizer_settings['game_list']['team_name_slim'] = 'title';
		}

		if ( 'no' === Sports_Leagues_Options::get_value( 'colorize_team_header' ) ) {
			if ( ! isset( $customizer_settings['game'] ) ) {
				$customizer_settings['game'] = [];
			}

			$customizer_settings['game']['colorize_team_header'] = 'no';
		}

		if ( 'no' === Sports_Leagues_Options::get_value( 'show_player_position' ) ) {
			if ( ! isset( $customizer_settings['game'] ) ) {
				$customizer_settings['game'] = [];
			}

			$customizer_settings['game']['show_player_position'] = 'no';
		}

		if ( 'no' === Sports_Leagues_Options::get_value( 'show_staff_job' ) ) {
			if ( ! isset( $customizer_settings['game'] ) ) {
				$customizer_settings['game'] = [];
			}

			$customizer_settings['game']['show_staff_job'] = 'no';
		}

		if ( 'hide' === Sports_Leagues_Options::get_value( 'team_series_game_header' ) ) {
			if ( ! isset( $customizer_settings['game'] ) ) {
				$customizer_settings['game'] = [];
			}

			$customizer_settings['game']['team_series_game_header'] = 'hide';
		}

		if ( 'no' === Sports_Leagues_Options::get_value( 'show_team_series' ) ) {
			if ( ! isset( $customizer_settings['standing'] ) ) {
				$customizer_settings['standing'] = [];
			}

			$customizer_settings['standing']['show_team_series'] = 'no';
		}

		if ( 'no' === Sports_Leagues_Options::get_value( 'show_standing_full_screen_link' ) ) {
			if ( ! isset( $customizer_settings['standing'] ) ) {
				$customizer_settings['standing'] = [];
			}

			$customizer_settings['standing']['show_standing_full_screen_link'] = 'no';
		}

		if ( in_array( Sports_Leagues_Options::get_value( 'game_period_scores' ), [ 'hide', 'line' ], true ) ) {
			if ( ! isset( $customizer_settings['game'] ) ) {
				$customizer_settings['game'] = [];
			}

			$customizer_settings['game']['game_period_scores'] = Sports_Leagues_Options::get_value( 'game_period_scores' );
		}

		if ( ! empty( $customizer_settings ) ) {
			update_option( 'anwp-sl-customizer', $customizer_settings );
		}
	}

	/**
	 * Reset v0.10 cache
	 */
	public function reset_v010_cache() {

		$cached_keys = get_option( 'sl_cached_keys', [] );

		if ( empty( $cached_keys ) ) {
			return;
		}

		foreach ( $cached_keys as $cached_key ) {
			delete_transient( $cached_key );
		}

		delete_option( 'sl_cached_keys' );
	}

	/**
	 * Migrate config to v0.10
	 */
	public function convert_old_config_to_new_format() {

		$old_config = get_option( 'sports_leagues_config' );

		if ( ! empty( $old_config ) ) {

			$new_config = [];

			// Position
			if ( ! empty( $old_config['position'] ) && is_array( $old_config['position'] ) ) {

				$new_config['position'] = [];

				foreach ( $old_config['position'] as $position_option ) {
					$new_config['position'][] = [
						'name' => isset( $position_option['name'] ) ? $position_option['name'] : '',
						'id'   => isset( $position_option['name'] ) ? $position_option['name'] : '',
						'abbr' => isset( $position_option['abbr'] ) ? $position_option['abbr'] : '',
					];
				}
			}

			// ID -> NAME options
			foreach ( [ 'roster_groups', 'game_player_groups', 'official_groups', 'staff_roster_groups', 'game_staff_groups', 'game_team_stats' ] as $config_option_2 ) {
				if ( ! empty( $old_config[ $config_option_2 ] ) && is_array( $old_config[ $config_option_2 ] ) ) {

					$new_config[ $config_option_2 ] = [];

					foreach ( $old_config[ $config_option_2 ] as $config_option_2_item ) {
						$new_config[ $config_option_2 ][] = [
							'name' => isset( $config_option_2_item ) ? $config_option_2_item : '',
							'id'   => isset( $config_option_2_item ) ? $config_option_2_item : '',
						];
					}
				}
			}

			// Copy
			$copied_options = [
				'roster_status',
				'points_ft_win',
				'points_ov_win',
				'points_pen_win',
				'points_draw',
				'points_pen_loss',
				'points_ov_loss',
				'points_ft_loss',
				'num_of_periods',
				'team_series_w',
				'team_series_d',
				'team_series_l',
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

			foreach ( $copied_options as $config_option_3 ) {
				if ( isset( $old_config[ $config_option_3 ] ) ) {
					$new_config[ $config_option_3 ] = $old_config[ $config_option_3 ];
				}
			}

			update_option( 'sports_leagues_config', $new_config );
			update_option( 'sports_leagues_config__b10', $old_config );
		}
	}

	/**
	 * Finishing Upgrade
	 */
	public function finish_upgrade() {
		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	/**
	 * Hide a Setup Helper notices.
	 *
	 * @since 0.6.4
	 */
	public function hide_setup_notices() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['sports-leagues-hide-setup-notice'] ) && isset( $_GET['_sports_leagues_notice_nonce'] ) ) {

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_sports_leagues_notice_nonce'] ) ), 'sports_leagues_hide_setup_notices_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'sports-leagues' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'sports-leagues' ) );
			}

			if ( 'yes' === sanitize_text_field( $_GET['sports-leagues-hide-setup-notice'] ) ) {
				update_option( 'sports_leagues_setup_notice', - 1, true );
			}
		}
	}

	/**
	 * Add Setup Helper notices and its styles.
	 *
	 * @version 0.6.4
	 */
	public function add_setup_notices() {

		// Check notice step
		$setup_step = intval( get_option( 'sports_leagues_setup_notice' ) );

		if ( $setup_step < 0 ) {
			return;
		}

		// Add styles
		wp_enqueue_style( 'sports-leagues-setup', Sports_Leagues::url( 'admin/css/setup.css' ), [], Sports_Leagues::VERSION );

		// Render notice
		add_action( 'admin_notices', [ $this, 'render_setup_helper_notice' ] );
	}

	/**
	 * Render Setup Helper notice
	 *
	 * @version 0.6.4
	 */
	public static function render_setup_helper_notice() {

		$setup_step   = intval( get_option( 'sports_leagues_setup_notice' ) );
		$is_dashboard = isset( $_GET['page'] ) && 'sports-leagues' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification

		if ( 0 === $setup_step && ! $is_dashboard ) {
			?>
			<div id="message" class="updated sports-leagues-setup-notice">
				<span class="sports-leagues-setup-notice-title"><?php echo esc_html__( 'AnWP Sports Leagues Setup Helper', 'sports-leagues' ); ?></span>
				<p>
					<strong><?php echo esc_html__( 'Welcome to AnWP Sports Leagues!', 'sports-leagues' ); ?></strong>
					<br>
					<?php echo esc_html__( 'Visit the Dashboard and start building your awesome sport website', 'sports-leagues' ); ?> :)
				</p>
				<p class="submit">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=sports-leagues' ) ); ?>" class="button-primary"><?php echo esc_html__( 'Visit Dashboard', 'sports-leagues' ); ?></a>
					<a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'sports-leagues-hide-setup-notice', 'yes' ), 'sports_leagues_hide_setup_notices_nonce', '_sports_leagues_notice_nonce' ) ); ?>"><?php echo esc_html__( 'Close', 'sports-leagues' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 */
	public function get_toolbox_updater_tasks( $output = '' ) {
		global $wpdb;

		$tasks = [];

		/*
		|--------------------------------------------------------------------
		| Check database structure mirrors statistic's config
		|--------------------------------------------------------------------
		*/
		$stat_columns_to_create = sports_leagues()->player_stats->check_player_stats_db_sync_needed();

		if ( $stat_columns_to_create ) {
			$tasks[] = [
				'status'      => 'pending',
				'total'       => count( $stat_columns_to_create ),
				'order'       => 5,
				'title'       => 'Sync table "player_statistics" with statistics configuration',
				'slug'        => 'sync_table__player_statistics',
				'description' => 'Create new columns in the "player_statistics" table according to the statistics configuration.',
				'subtasks'    => $stat_columns_to_create,
			];
		}

		/*
		|--------------------------------------------------------------------
		| Migrate Player Statistics to players table
		|--------------------------------------------------------------------
		*/
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}sl_player_stats';" ) ) {
			if ( intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}sl_player_stats WHERE migrated = 0;" ) ) ) {
				$games_to_migrate = $wpdb->get_col( "SELECT DISTINCT game_id FROM {$wpdb->prefix}sl_player_stats WHERE migrated = 0;" );

				$tasks[] = [
					'status'      => 'pending',
					'total'       => count( $games_to_migrate ),
					'order'       => 10,
					'title'       => 'Migrate table "player_stats" to "player_statistics"',
					'slug'        => 'migrate_table__sl_player_stats',
					'description' => 'Move data from "sl_player_stats" table to new format into "player_statistics" table.',
					'subtasks'    => $games_to_migrate,
				];
			}
		}

		/*
		|--------------------------------------------------------------------
		| Output
		|--------------------------------------------------------------------
		*/
		$updater_tasks = wp_list_sort( apply_filters( 'sports-leagues/toolbox-updater/get_updater_tasks', $tasks ), 'order' );

		if ( 12 === absint( get_option( 'sl_data_schema' ) ) && empty( $updater_tasks ) ) {
			sports_leagues()->cache->flush_all_cache();
			update_option( 'sl_data_schema', 13, true );
		}

		do_action( 'sports-leagues/toolbox-updater/after_get_updater_tasks', $updater_tasks );

		if ( 'tasks' === $output ) {
			return $updater_tasks;
		}

		return rest_ensure_response( [ 'tasks' => $updater_tasks ] );
	}

	/**
	 * Run task to create statistical column in players table.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_sync_table_player_statistics( WP_REST_Request $request ) {
		$subtasks = array_map( 'absint', $request->get_param( 'subtasks' ) );

		foreach ( $subtasks as $subtask ) {
			$create_result = sports_leagues()->player_stats->create_stat_column_in_players_table( $subtask );

			if ( false === $create_result ) {
				global $wpdb;

				return new WP_Error( 'anwp_rest_error', 'Error DB - :' . absint( $wpdb->last_error ?? '' ), [ 'status' => 400 ] );
			}
		}

		return rest_ensure_response( [] );
	}

	/**
	 * Run task to migrate "player_stats" data to "players" table.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_migrate_table_sl_player_stats( WP_REST_Request $request ) {
		global $wpdb;

		$game_ids = array_map( 'absint', $request->get_param( 'subtasks' ) );
		$columns  = json_decode( get_option( 'sl_columns_game' ), true );

		if ( empty( $game_ids ) ) {
			return rest_ensure_response( [] );
		}

		foreach ( $game_ids as $game_id ) {
			$items = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT *
				FROM {$wpdb->prefix}sl_player_stats
				WHERE game_id = %d
				",
					$game_id
				)
			);

			$club_player__grouped = [];

			foreach ( $items as $item ) {
				$slug = $item->team_id . '-' . $item->player_id;

				if ( empty( $club_player__grouped[ $slug ] ) ) {
					$club_player__grouped[ $slug ] = [];
				}

				$club_player__grouped[ $slug ][ $item->stats_id ] = $item->value;
			}

			foreach ( $club_player__grouped as $group_slug => $group_data ) {
				$slugs = explode( '-', $group_slug );

				if ( ! sports_leagues()->player_stats->update_player_advanced_game_stats( $game_id, $slugs[1], $slugs[0], $group_data ) ) {
					if ( 'sl_game' === get_post_type( $game_id ) && ! empty( $columns ) ) {
						return new WP_Error( 'anwp_rest_error', 'Error updating data - ID:' . absint( $game_id ) . ' - Data: ' . wp_json_encode( $group_data ), [ 'status' => 400 ] );
					}
				}
			}

			$wpdb->update(
				$wpdb->prefix . 'sl_player_stats',
				[ 'migrated' => 1 ],
				[ 'game_id' => $game_id ]
			);
		}

		return rest_ensure_response( [] );
	}

}
