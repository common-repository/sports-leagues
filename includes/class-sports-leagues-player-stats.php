<?php
/**
 * Sports Leagues Player Stats.
 *
 * @since   0.5.18
 * @package Sports_Leagues
 */

class Sports_Leagues_Player_Stats {

	/**
	 * Parent plugin class.
	 *
	 * @var    Sports_Leagues
	 * @since  0.5.18
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
	 *
	 * @since  0.5.18
	 */
	public function hooks() {
		add_action( 'sports-leagues/game/after_save', [ $this, 'save_player_stats_data' ], 10, 3 );
		add_filter( 'sports-leagues/game/edit_form_data', [ $this, 'modify_edit_vue_data' ], 10, 2 );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'sports-leagues/stats',
			'/save_stat_config/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_stat_config' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Save Player Stats Game data.
	 *
	 * @param WP_Post $post
	 * @param array   $data
	 * @param array   $post_data - Already unslashed
	 *
	 * @since 0.5.18
	 */
	public function save_player_stats_data( $post, $data, $post_data ) {

		if ( empty( get_option( 'sl_stat_groups' ) ) ) {
			return;
		}

		global $wpdb;

		/*
		|--------------------------------------------------------------------
		| Game Players Stats
		|--------------------------------------------------------------------
		*/
		$stats_table = $wpdb->prefix . 'sl_player_statistics';

		// Fetch time type columns
		$player_stats_columns = json_decode( get_option( 'sl_columns_game' ) );
		$time_stats_columns   = [];

		if ( ! empty( $player_stats_columns ) ) {
			foreach ( $player_stats_columns as $stat ) {
				if ( 'time' === $stat->type ) {
					$time_stats_columns[] = absint( $stat->id );
				}
			}
		}

		// Prepare data
		$player_stats_home = json_decode( $post_data['_sl_player_stats_home'] );
		$player_stats_away = json_decode( $post_data['_sl_player_stats_away'] );

		// Remove old stats
		$wpdb->delete( $stats_table, [ 'game_id' => $post->ID ] );

		$temporary_stats = [];

		foreach ( [ $player_stats_home, $player_stats_away ] as $side_index => $player_stats ) {

			$team_id     = $side_index ? $data['team_away'] : $data['team_home'];
			$update_data = [];

			if ( ! empty( $player_stats ) && is_object( $player_stats ) ) {
				foreach ( $player_stats as $group_data ) {
					foreach ( $group_data as $player_data ) {

						if ( empty( $player_data ) || empty( $player_data->id ) || ( ! absint( $player_data->id ) && mb_strpos( $player_data->id, 'temp__' ) === false ) ) {
							continue;
						}

						if ( ! isset( $update_data[ $player_data->id ] ) ) {
							$update_data[ $player_data->id ] = [];
						}

						foreach ( $player_data as $stat_id => $stat_value ) {

							if ( '' === $stat_value || ! is_numeric( $stat_id ) ) {
								continue;
							}

							if ( 0 === absint( $stat_id ) ) {
								$value = (int) Sports_Leagues::string_to_bool( $stat_value );

								if ( ! $value ) {
									continue;
								}
							}

							if ( in_array( absint( $stat_id ), $time_stats_columns, true ) ) {
								if ( mb_strlen( $stat_value ) && ':' !== mb_substr( $stat_value, - 3, 1 ) ) {
									continue;
								}

								if ( mb_strlen( $stat_value ) < 4 ) {
									continue;
								}
							}

							if ( mb_strpos( $player_data->id, 'temp__' ) !== false ) {
								$temporary_stats[] = [
									'player_id' => $player_data->id,
									'stats_id'  => $stat_id,
									'team_id'   => $team_id,
									'value'     => $stat_value,
								];
							} else {
								$update_data[ $player_data->id ][ $stat_id ] = $stat_value;
							}
						}
					}
				}
			}

			foreach ( $update_data as $player_id => $update_player_data ) {
				if ( mb_strpos( $player_id, 'temp__' ) === false ) {
					$this->update_player_advanced_game_stats( $post->ID, $player_id, $team_id, $update_player_data );
				}
			}
		}

		if ( ! empty( $temporary_stats ) ) {
			update_post_meta( $post->ID, '_sl_temp_player_stats', $temporary_stats );
		}
	}

	/**
	 * Modify Game edit JS data.
	 *
	 * @param $data
	 * @param $post_id
	 *
	 * @since 0.5.18
	 * @return array
	 */
	public function modify_edit_vue_data( $data, $post_id ) {

		if ( is_array( $data ) ) {

			$season_id = get_post_meta( $post_id, '_sl_season_id', true );

			// Player Stats Options
			$data['statsPlayersColumns'] = get_option( 'sl_columns_game' );

			// Home and Away players for stats
			$data['statsPlayersHomeIDs'] = $this->get_team_season_players_grouped(
				[
					'team_id'   => isset( $data['team_home']->id ) ? $data['team_home']->id : 0,
					'season_id' => $season_id,
				]
			);

			$data['statsPlayersAwayIDs'] = $this->get_team_season_players_grouped(
				[
					'team_id'   => isset( $data['team_away']->id ) ? $data['team_away']->id : 0,
					'season_id' => $season_id,
				]
			);

			// Saved Stats
			$data['statsPlayersHomeData'] = $this->get_game_stats( $post_id, $data['team_home']->id );
			$data['statsPlayersAwayData'] = $this->get_game_stats( $post_id, $data['team_away']->id );

			$data['statsRosterGroups'] = sports_leagues()->config->get_options( 'roster_groups' );
			$data['statGroups']        = get_option( 'sl_stat_groups' );
		}

		return $data;
	}

	/**
	 * Get game stats for team.
	 *
	 * @param int $post_id
	 * @param int $team_id
	 *
	 * @return object
	 * @since 0.5.18
	 */
	public function get_game_stats( $post_id, $team_id ) {

		global $wpdb;

		$output = [];

		$saved_items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}sl_player_statistics WHERE game_id = %d AND team_id = %d",
				$post_id,
				$team_id
			)
		);

		foreach ( $saved_items as $player_data ) {
			foreach ( $player_data as $col => $val ) {
				if ( in_array( $col, [ 'game_id', 'player_id', 'team_id' ], true ) || '' === $val ) {
					continue;
				}

				$stat_id = absint( str_replace( 'c_id__', '', $col ) );

				if ( $stat_id || 'c_id__0' === $col ) {
					$output[ $player_data->player_id ][ $stat_id ] = $val;
				}
			}
		}

		$temp_stats = get_post_meta( $post_id, '_sl_temp_player_stats', true );
		$temp_items = [];

		if ( ! empty( $temp_stats ) && is_array( $temp_stats ) ) {
			foreach ( $temp_stats as $stat_row ) {

				if ( absint( $stat_row['team_id'] ) === absint( $team_id ) ) {
					$temp_items[] = (object) [
						'player_id' => $stat_row['player_id'],
						'value'     => $stat_row['value'],
						'stats_id'  => $stat_row['stats_id'],
					];
				}
			}
		}

		foreach ( $temp_items as $item ) {
			$output[ $item->player_id ][ $item->stats_id ] = $item->value;
		}

		return (object) $output;
	}

	/**
	 * Get game players grouped by Stat Group
	 *
	 * @param $home_team
	 * @param $season_id
	 * @param $stat_groups
	 * @param $stats_players_data
	 * @param $game_players
	 * @param $temp_players
	 *
	 * @return array
	 */
	public function get_game_stats_grouped( $home_team, $season_id, $stat_groups, $stats_players_data, $game_players, $temp_players ) {

		static $positions_map = null;

		if ( null === $positions_map ) {
			$positions_map = sports_leagues()->player->get_positions_map();
		}

		$roster_players_ids = sports_leagues()->player_stats->get_team_season_players_grouped(
			[
				'team_id'   => $home_team,
				'season_id' => $season_id,
			]
		);

		$grouped_stats              = [];
		$position_to_group_map      = [];
		$player_to_roster_group_map = [];

		foreach ( $stat_groups as $group_index => $group ) {
			$grouped_stats[ $group->id ] = [
				'ids'   => [],
				'index' => $group_index,
				'name'  => $group->name,
				'id'    => $group->id,
				'type'  => $group->type,
			];

			if ( ! empty( $group->roles ) && is_array( $group->roles ) ) {
				foreach ( $group->roles as $role ) {
					$position_to_group_map[ $role ] = $group->id;
				}
			}
		}

		if ( ! empty( $roster_players_ids ) && is_array( $roster_players_ids ) ) {
			foreach ( $roster_players_ids as $roster_group => $player_ids ) {
				if ( ! empty( $player_ids ) && is_array( $player_ids ) ) {
					foreach ( $player_ids as $player_id ) {
						$player_to_roster_group_map[ $player_id ] = $roster_group;
					}
				}
			}
		}

		$game_players = $game_players ? explode( ',', $game_players ) : [];

		if ( ! empty( $game_players ) && is_array( $game_players ) ) {
			foreach ( $game_players as $game_player_id ) {
				if ( '_' === mb_substr( $game_player_id, 0, 1 ) || ( empty( $stats_players_data->{$game_player_id} ) && mb_strpos( $game_player_id, 'temp__' ) === false ) ) {
					continue;
				}

				if ( mb_strpos( $game_player_id, 'temp__' ) !== false ) {
					if ( empty( $temp_players[ $game_player_id ] ) ) {
						continue;
					}

					$player_position = empty( $temp_players[ $game_player_id ]->position ) ? '--no--' : $temp_players[ $game_player_id ]->position;
				} else {
					$player_position = empty( $positions_map[ $game_player_id ] ) ? '--no--' : $positions_map[ $game_player_id ];
				}

				if ( ! empty( $position_to_group_map[ $player_position ] ) ) {
					$grouped_stats[ $position_to_group_map[ $player_position ] ]['ids'][] = $game_player_id;
					continue;
				}

				if ( ! empty( $player_to_roster_group_map[ $game_player_id ] )
				     && isset( $grouped_stats[ $player_to_roster_group_map[ $game_player_id ] ]['type'] )
				     && 'roster' === $grouped_stats[ $player_to_roster_group_map[ $game_player_id ] ]['type'] ) {
					$grouped_stats[ $player_to_roster_group_map[ $game_player_id ] ]['ids'][] = $game_player_id;
				}
			}
		}

		return wp_list_sort( array_values( $grouped_stats ), 'index' );
	}

	/**
	 * Get game players grouped by Stat Group
	 *
	 * @param $team_id
	 * @param $season_id
	 * @param $player_stats
	 * @param $stat_groups
	 *
	 * @return array
	 */
	public function get_team_season_player_stats_grouped_position( $team_id, $season_id, $player_stats, $stat_groups ) {

		/*
		|--------------------------------------------------------------------
		| Prepare group map
		|--------------------------------------------------------------------
		*/
		$stat_group_map = [
			'roster' => [],
			'custom' => [],
		];

		foreach ( $stat_groups as $stat_group_index => $stat_group ) {

			$stat_group->platers = [];

			if ( ! empty( $stat_group->roles ) && is_array( $stat_group->roles ) ) {
				foreach ( $stat_group->roles as $role ) {
					if ( 'roster' === $stat_group->type ) {
						$stat_group_map['roster'][ $role ] = $stat_group_index;
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Ger player position from the roster
		|--------------------------------------------------------------------
		*/
		$player_position_map = sports_leagues()->player->get_positions_map();
		$roster_position_map = [];

		$roster_data = json_decode( get_post_meta( $team_id, '_sl_roster', true ) );

		if ( ! empty( $roster_data->{'s:' . $season_id} ) ) {
			foreach ( $roster_data->{'s:' . $season_id} as $roster_item ) {
				if ( 'player' === $roster_item->type && absint( $roster_item->id ) ) {
					$roster_position_map[ $roster_item->id ] = $roster_item->role;
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Ger player position from the roster
		|--------------------------------------------------------------------
		*/
		foreach ( $player_stats as $player_id => $player_stat ) {
			$player_stat_group_index = null;

			// Get roster position
			$player_position = '';

			if ( ! empty( $roster_position_map[ $player_id ] ) ) {
				$player_position = $roster_position_map[ $player_id ];
			} elseif ( ! empty( $player_position_map[ $player_id ] ) ) {
				$player_position = $player_position_map[ $player_id ];
			}

			if ( empty( $player_position ) ) {
				continue;
			}

			// Get group index
			if ( isset( $stat_group_map['roster'][ $player_position ] ) ) {
				$player_stat_group_index = $stat_group_map['roster'][ $player_position ];
			} elseif ( isset( $stat_group_map['custom'][ $player_position ] ) ) {
				$player_stat_group_index = $stat_group_map['custom'][ $player_position ];
			}

			if ( null === $player_stat_group_index ) {
				continue;
			}

			$stat_groups[ $player_stat_group_index ]->players[] = [
				'player_id' => $player_id,
				'stats'     => $player_stat,
			];
		}

		return $stat_groups;
	}

	/**
	 * Get game players grouped by Stat Group
	 *
	 * @param $player_stats
	 * @param $stat_groups
	 *
	 * @return array
	 */
	public function get_team_season_player_stats_grouped( $player_stats, $stat_groups, $stats_columns_season ) {
		foreach ( $stat_groups as $stat_group ) {
			$stat_group->players = [];

			$stat_ids = [];

			foreach ( $stats_columns_season as $column ) {
				if ( ( ! empty( $column->groups ) && ! in_array( $stat_group->id, $column->groups, true ) ) || 'hidden' === $column->visibility || 'played' === $column->type || empty( $column->game_field_id ) ) {
					continue;
				}

				$stat_ids[] = absint( $column->game_field_id );

				foreach ( $player_stats as $player_id => $player_stat ) {
					if ( ! empty( array_intersect( array_keys( $player_stat ), $stat_ids ) ) ) {
						$stat_group->players[ $player_id ] = [
							'player_id' => $player_id,
							'stats'     => $player_stat,
						];
					}
				}
			}
		}

		return $stat_groups;
	}

	/**
	 * Get all player stats for selected season.
	 * Used at the Player page.
	 *
	 * @param array $player_stats = [
	 *     $team_id => [
	 *          $game_id => [
	 *              [stats] => [
	 *                  0 => 12,
	 *                  1 => 14,
	 *              ],
	 *              [game] => (object) [
	 *                  'value => 0,
	 *                  'team_id => 14,
	 *                  'stats_id => 18,
	 *                  'game_id => 151,
	 *                  'tournament_id => 68,
	 *                  'stage_id => 69,
	 *                  'home_team => 16,
	 *                  'away_team => 18,
	 *                  'kickoff => '2022-08-28 22:30:00',
	 *                  'home_scores => 9,
	 *                  'away_scores => 11,
	 *                  'home_outcome => 'ft_loss',
	 *                  'away_outcome => 'ft_win,
	 *              ]
	 *          ]
	 *      ]
	 * ]
	 * @param $data
	 *
	 * @return array
	 * @since 0.12.6
	 */
	public function get_player_stats_grouped( $player_stats, $data ) {

		$output = [];

		$player_position = get_post_meta( $data->player_id, '_sl_position', true );
		$stat_groups     = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : '';
		$stats_columns   = json_decode( get_option( 'sl_columns_game' ) );

		foreach ( $player_stats as $team_id => $team_data ) {
			$player_stat_groups = sports_leagues()->player_stats->get_player_stat_groups( $player_position, $stat_groups );
			$roster_position    = sports_leagues()->player_stats->get_player_season_roster_position( $team_id, $data->season_id, $data->player_id );

			if ( $roster_position !== $player_position ) {
				$maybe_player_stat_groups = sports_leagues()->player_stats->get_player_stat_groups( $roster_position, $stat_groups );

				if ( $maybe_player_stat_groups ) {
					$player_stat_groups = array_merge( $player_stat_groups, $maybe_player_stat_groups );
				}
			}

			if ( empty( $player_stat_groups ) ) {
				continue;
			}

			$player_stat_groups = array_unique( $player_stat_groups );

			$team_output = [];

			foreach ( $player_stat_groups as $player_stat_group ) {
				$stat_group_output = [];

				foreach ( $team_data as $game_id => $game_data ) {

					$game_output = [];

					foreach ( $stats_columns as $column ) {
						if ( 'hidden' === $column->visibility || ! in_array( $player_stat_group, $column->groups, true ) ) {
							continue;
						}

						if ( in_array( $column->type, [ 'simple', 'time' ], true ) ) {
							if ( ! isset( $game_data['stats'][ $column->id ] ) ) {
								continue;
							}
						} elseif ( in_array( $column->type, [ 'calculated', 'composed' ], true ) ) {
							if ( ! isset( $game_data['stats'][ $column->field_1 ] ) ) {
								continue;
							}
						}

						$game_output[] = $game_data;
					}

					if ( ! empty( $game_output ) ) {
						$stat_group_output[ $game_id ] = $game_data;
					}
				} // END OF $team_data

				if ( ! empty( $stat_group_output ) ) {
					$team_output[ $player_stat_group ] = $stat_group_output;
				}
			} // END OF $player_stat_groups

			if ( ! empty( $team_output ) ) {
				$output[ $team_id ] = $team_output;
			}
		} // END OF $player_stats

		return $output;
	}

	/**
	 * Get all player stats for selected season.
	 * Used at the Player page.
	 *
	 * @param int $player_id
	 * @param int $season_id
	 *
	 * @return array
	 * @since 0.6.0
	 */
	public function get_player_stats( $player_id, $season_id ) {

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'SL-get_player_stats__' . md5( maybe_serialize( $player_id . '-' . $season_id ) );

		if ( sports_leagues()->cache->get( $cache_key, 'sl_game' ) ) {
			return sports_leagues()->cache->get( $cache_key, 'sl_game' );
		}

		/*
		|--------------------------------------------------------------------
		| Get from DB
		|--------------------------------------------------------------------
		*/
		global $wpdb;

		$output = [];

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT a.*, b.tournament_id, b.stage_id, b.home_team, b.away_team, b.kickoff, b.home_scores, b.away_scores, b.home_outcome, b.away_outcome
					FROM {$wpdb->prefix}sl_player_statistics a
					LEFT JOIN {$wpdb->prefix}sl_games b ON a.game_id = b.game_id
					WHERE a.player_id = %d AND b.season_id = %d
					ORDER BY b.kickoff
					",
				$player_id,
				$season_id
			)
		);

		foreach ( $items as $item ) {

			foreach ( $item as $col => $val ) {
				if ( str_contains( $col, 'c_id__' ) && '' !== $val ) {
					$stat_id = str_replace( 'c_id__', '', $col );

					$output[ $item->team_id ][ $item->game_id ]['stats'][ $stat_id ] = $val;
				}
			}

			// Game data
			$output[ $item->team_id ][ $item->game_id ]['data'] = $item;
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $output ) ) {
			sports_leagues()->cache->set( $cache_key, $output, 'sl_game' );
		}

		return $output;
	}

	/**
	 * Get aggregate players statistics.
	 * Used in widgets and shortcodes.
	 *
	 * @param array|object $options
	 *
	 * @return array
	 * @since 0.7.0
	 */
	public function get_players_aggregate_stats( $options ) {

		$options = (object) wp_parse_args(
			$options,
			[
				'stats_id'        => '',
				'game_id'         => '',
				'team_id'         => '',
				'tournament_id'   => '',
				'stage_id'        => '',
				'league_id'       => '',
				'season_id'       => '',
				'group_id'        => '',
				'round_id'        => '',
				'venue_id'        => '',
				'game_day'        => '',
				'order'           => '',
				'position'        => '',
				'link_to_profile' => 0,
				'limit'           => 0,
				'soft_limit'      => 0,
				'soft_limit_qty'  => '',
				'offset'          => '',
				'hide_zero'       => 1,
			]
		);

		/*
		|--------------------------------------------------------------------
		| Build DataBase Query
		|--------------------------------------------------------------------
		*/
		global $wpdb;

		$stats_id    = absint( $options->stats_id );
		$stats_table = $wpdb->prefix . 'sl_player_statistics';
		$games_table = $wpdb->prefix . 'sl_games';
		$ordering    = 'ASC' === mb_strtoupper( $options->order ) ? '' : 'DESC';

		$stats_column = sports_leagues()->player_stats->get_stats_player_game_column_by_id( $stats_id );

		if ( empty( $stats_column ) ) {
			return [];
		}

		$stat_query = '';

		$maybe_join_positions = '';
		if ( trim( $options->position ) ) {
			$maybe_join_positions = "LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = st1.player_id AND pm1.meta_key = '_sl_position' )";
		}

		if ( 'calculated' === $stats_column->type ) {

			if ( ! absint( $stats_column->field_1 ) || ! absint( $stats_column->field_2 ) ) {
				return [];
			}

			$stats_column_1 = sports_leagues()->player_stats->get_stats_player_game_column_by_id( $stats_column->field_1 );
			$stats_column_2 = sports_leagues()->player_stats->get_stats_player_game_column_by_id( $stats_column->field_2 );

			if ( ! in_array( $stats_column_1->type, [ 'simple' ], true ) || ! in_array( $stats_column_2->type, [ 'simple' ], true ) ) {
				return [];
			}

			$stat_query_1 = 'SUM( st1.c_id__' . absint( $stats_column->field_1 ) . ' )';
			$stat_query_2 = 'SUM( st1.c_id__' . absint( $stats_column->field_2 ) . ' )';

			switch ( $stats_column->calc ) {
				case 'sum':
					$stat_query = "( $stat_query_1 + $stat_query_2 ) as qty";
					break;

				case 'difference':
					$stat_query = "( $stat_query_1 - $stat_query_2 ) as qty";
					break;

				case 'ratio':
					$stat_query = "( $stat_query_1 / $stat_query_2 ) as qty";
					break;

				case 'ratio_pr':
					$stat_query = "( $stat_query_1 / $stat_query_2 * 100 ) as qty";
					break;
			}

			$query = "
				SELECT st1.player_id ID, $stat_query, SUM( st1.c_id__0 ) as gp, GROUP_CONCAT( DISTINCT st1.team_id ) as teams
				FROM $stats_table st1
				JOIN $games_table gt ON ( gt.game_id = st1.game_id )
				$maybe_join_positions
				WHERE gt.status = 'official'
				";
		} else {
			switch ( $stats_column->type ) {
				case 'time':
					$stat_query = 'SUM( TIME_TO_SEC( st1.c_id__' . absint( $stats_id ) . ' ) ) as qty';
					break;

				case 'simple':
					$stat_query = 'SUM( st1.c_id__' . absint( $stats_id ) . ' ) as qty';
					break;
			}

			$query = "
				SELECT st1.player_id ID, $stat_query, SUM( st1.c_id__0 ) as gp, GROUP_CONCAT( DISTINCT st1.team_id ) as teams
				FROM $stats_table st1
				JOIN $games_table gt ON ( gt.game_id = st1.game_id )
				$maybe_join_positions
				WHERE gt.status = 'official'
				";
		}

		/**==================
		 * WHERE filter by positions
		 *================ */
		if ( trim( $options->position ) ) {
			$positions = explode( ',', trim( $options->position ) );

			if ( ! empty( $positions ) && is_array( $positions ) && count( $positions ) ) {

				// Prepare include format and placeholders
				$include_placeholders = array_fill( 0, count( $positions ), '%s' );
				$include_format       = implode( ', ', $include_placeholders );

				$query .= $wpdb->prepare( " AND pm1.meta_value IN ({$include_format})", $positions ); // phpcs:ignore
			}
		}

		/**==================
		 * WHERE filter by game_id
		 *================ */
		if ( absint( $options->game_id ) ) {
			$query .= $wpdb->prepare( ' AND st1.game_id = %d ', $options->game_id );
		}

		/**==================
		 * WHERE filter by team_id
		 *================ */
		if ( absint( $options->team_id ) ) {
			$query .= $wpdb->prepare( ' AND st1.team_id = %d ', $options->team_id );
		}

		/**==================
		 * WHERE filter by tournament_id
		 *================ */
		if ( absint( $options->tournament_id ) ) {
			$query .= $wpdb->prepare( ' AND gt.tournament_id = %d ', $options->tournament_id );
		}

		/**==================
		 * WHERE filter by stage_id
		 *================ */
		if ( absint( $options->stage_id ) ) {
			$query .= $wpdb->prepare( ' AND gt.stage_id = %d ', $options->stage_id );
		}

		/**==================
		 * WHERE filter by league_id
		 *================ */
		if ( absint( $options->league_id ) ) {
			$query .= $wpdb->prepare( ' AND gt.league_id = %d ', $options->league_id );
		}

		/**==================
		 * WHERE filter by season_id
		 *================ */
		if ( absint( $options->season_id ) ) {
			$query .= $wpdb->prepare( ' AND gt.season_id = %d ', $options->season_id );
		}

		/**==================
		 * WHERE filter by group_id
		 *================ */
		if ( absint( $options->group_id ) ) {
			$query .= $wpdb->prepare( ' AND gt.group_id = %d ', $options->group_id );
		}

		/**==================
		 * WHERE filter by round_id
		 *================ */
		if ( absint( $options->round_id ) ) {
			$query .= $wpdb->prepare( ' AND gt.round_id = %d ', $options->round_id );
		}

		/**==================
		 * WHERE filter by venue_id
		 *================ */
		if ( absint( $options->venue_id ) ) {
			$query .= $wpdb->prepare( ' AND gt.venue_id = %d ', $options->venue_id );
		}

		/**==================
		 * WHERE filter by game_day
		 *================ */
		if ( absint( $options->game_day ) ) {
			$query .= $wpdb->prepare( ' AND gt.game_day = %d ', $options->game_day );
		}

		/**==================
		 * GROUP
		 *================ */
		$query .= ' GROUP BY st1.player_id';

		/*
		|--------------------------------------------------------------------
		| Soft Limit & Hide Zero
		|--------------------------------------------------------------------
		*/
		$having_query = [];

		if ( absint( $options->soft_limit_qty ) ) {
			if ( 'DESC' !== $ordering ) {
				$having_query[] = $wpdb->prepare( ' HAVING qty <= %d ', $options->soft_limit_qty );
			} else {
				$having_query[] = $wpdb->prepare( ' HAVING qty >= %d ', $options->soft_limit_qty );
			}
		}

		if ( Sports_Leagues::string_to_bool( $options->hide_zero ) ) {
			$having_query[] = ' qty != 0 ';
		}

		if ( ! empty( $having_query ) ) {
			$query .= ' HAVING ' . implode( ' AND ', $having_query );
		}

		/*
		|--------------------------------------------------------------------
		| Order
		|--------------------------------------------------------------------
		*/
		$query .= " ORDER BY qty $ordering";

		/**==================
		 * LIMIT clause
		 *================ */
		$limit = absint( $options->limit );

		if ( $limit ) {
			if ( absint( $options->offset ) ) {
				$query .= $wpdb->prepare( ' LIMIT %d, %d', $options->offset, $options->limit );
			} else {
				$query .= $wpdb->prepare( ' LIMIT %d', $options->limit );
			}

			if ( Sports_Leagues::string_to_bool( $options->soft_limit ) ) {
				$soft_limit_qty = $wpdb->get_row( $query, OBJECT, ( $limit - 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL

				if ( ! empty( $soft_limit_qty ) && isset( $soft_limit_qty->qty ) ) {
					$options->limit          = 0;
					$options->soft_limit     = 0;
					$options->soft_limit_qty = $soft_limit_qty->qty;

					return $this->get_players_aggregate_stats( $options );
				}
			}
		}

		/**==================
		 * Bump Query
		 *================ */
		$players = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		foreach ( $players as $player ) {
			$player_data = sports_leagues()->player->get_player( $player->ID );

			$player->player_name        = $player_data->name;
			$player->player_position    = $player_data->position;
			$player->player_nationality = $player_data->nationality;
		}

		/**==================
		 * Add photo ID
		 *================ */
		if ( Sports_Leagues::string_to_bool( $options->link_to_profile ) ) {

			// Get player data
			$player_ids    = wp_list_pluck( $players, 'ID' );
			$posts_players = [];

			if ( ! empty( $player_ids ) && is_array( $player_ids ) ) {
				$player_ids = array_unique( $player_ids );

				$args = [
					'include'       => $player_ids,
					'cache_results' => false,
					'post_type'     => [ 'sl_player' ],
				];

				/** @var WP_Post $player_post */
				foreach ( get_posts( $args ) as $player_post ) {
					$posts_players[ $player_post->ID ] = [
						'link' => get_permalink( $player_post ),
					];
				}
			}

			foreach ( $players as $player ) {
				$player->link = empty( $posts_players[ $player->ID ] ) ? '' : $posts_players[ $player->ID ]['link'];
			}
		}

		return $players;
	}

	/**
	 * Get player stats for season.
	 *
	 * @param int $team_id
	 * @param int $season_id
	 *
	 * @return array
	 * @since 0.6.1
	 */
	public function get_team_players_stats( $team_id, $season_id ) {

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'SL-get_team_players_stats_v2__' . md5( maybe_serialize( $team_id . '-' . $season_id ) );

		if ( sports_leagues()->cache->get( $cache_key, 'sl_game' ) ) {
			return sports_leagues()->cache->get( $cache_key, 'sl_game' );
		}

		/*
		|--------------------------------------------------------------------
		| Get from DB
		|--------------------------------------------------------------------
		*/
		global $wpdb;

		$output = [];

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT a.*
					FROM {$wpdb->prefix}sl_player_statistics a
					LEFT JOIN {$wpdb->prefix}sl_games b ON a.game_id = b.game_id
					WHERE a.team_id = %d AND b.season_id = %d AND b.`status` = 'official'
					",
				$team_id,
				$season_id
			)
		);

		foreach ( $items as $item ) {
			foreach ( $item as $col => $val ) {
				if ( str_contains( $col, 'c_id__' ) && '' !== $val ) {
					$stat_id = str_replace( 'c_id__', '', $col );

					$output[ $item->player_id ][ $item->game_id ][ $stat_id ] = $val;
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $output ) ) {
			sports_leagues()->cache->set( $cache_key, $output, 'sl_game' );
		}

		return $output;
	}

	/**
	 * Get player total stats for season.
	 *
	 * @param int $player_id
	 * @param int $season_id
	 *
	 * @return array
	 * @since 0.9.5
	 */
	public function get_player_total_stats( $player_id, $season_id ) {
		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'SL-get_player_total_stats__' . md5( maybe_serialize( $player_id . '-' . $season_id ) );

		if ( sports_leagues()->cache->get( $cache_key, 'sl_game' ) ) {
			return sports_leagues()->cache->get( $cache_key, 'sl_game' );
		}

		/*
		|--------------------------------------------------------------------
		| Get from DB
		|--------------------------------------------------------------------
		*/
		global $wpdb;

		$output = [];

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT a.*, b.stage_id
					FROM {$wpdb->prefix}sl_player_statistics a
					LEFT JOIN {$wpdb->prefix}sl_games b ON a.game_id = b.game_id
					WHERE a.player_id = %d AND b.season_id = %d
					",
				$player_id,
				$season_id
			)
		);

		foreach ( $items as $item ) {
			foreach ( $item as $col => $val ) {
				if ( str_contains( $col, 'c_id__' ) && '' !== $val ) {
					$stat_id = str_replace( 'c_id__', '', $col );

					$output[ $item->team_id ][ $item->stage_id ][ $item->game_id ][ $stat_id ] = $val;
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $output ) ) {
			sports_leagues()->cache->set( $cache_key, $output, 'sl_game' );
		}

		return $output;
	}

	/**
	 * Get sums of player stats (grouped and filtered)
	 *
	 * @param array $player_stats = [
	 *  $team_id => [
	 *      $stage_id => [
	 *         0 => 12, // stat_id => value
	 *         1 => 14,
	 *     ]
	 *  ]
	 *]
	 *
	 * @param object $data
	 * @param array $stat_groups
	 *
	 * @return array
	 * @since 0.12.6
	 */
	public function get_player_total_stats_sums_grouped( $player_stats, $data, $stat_groups ) {

		$output = [];

		$player_position      = get_post_meta( $data->player_id, '_sl_position', true );
		$player_stat_groups   = sports_leagues()->player_stats->get_player_stat_groups( $player_position, $stat_groups );
		$stats_columns_season = json_decode( get_option( 'sl_columns_season' ) );

		foreach ( array_keys( $player_stats ) as $player_team_id ) {
			if ( 'totals' !== $player_team_id ) {
				$roster_position = sports_leagues()->player_stats->get_player_season_roster_position( $player_team_id, $data->season_id, $data->player_id );

				if ( $roster_position !== $player_position ) {
					$maybe_player_stat_groups = sports_leagues()->player_stats->get_player_stat_groups( $roster_position, $stat_groups );

					if ( $maybe_player_stat_groups ) {
						$player_stat_groups = array_merge( $player_stat_groups, $maybe_player_stat_groups );
					}
				}
			}
		}

		$player_stat_groups = array_unique( $player_stat_groups );

		foreach ( $player_stat_groups as $player_stat_group ) {

			$stat_group_output = [];

			foreach ( $player_stats as $team_id => $team_data ) {
				$team_output = [];

				foreach ( $team_data as $stage_id => $stage_data ) {
					$stage_output = [];

					foreach ( $stats_columns_season as $column ) {

						if ( 'hidden' === $column->visibility || ! in_array( $player_stat_group, $column->groups, true ) ) {
							continue;
						}

						if ( in_array( $column->type, [ 'simple', 'time' ], true ) ) {
							if ( ! isset( $stage_data[ $column->game_field_id ] ) ) {
								continue;
							}
						} elseif ( in_array( $column->type, [ 'calculated', 'composed', 'played' ], true ) ) {
							continue;
						}

						$stage_output[] = $stage_data;
					}

					if ( ! empty( $stage_output ) ) {
						$team_output[ $stage_id ] = $stage_data;
					}
				} // END OF $team_data

				if ( ! empty( $team_output ) ) {
					$stat_group_output[ $team_id ] = $team_output;
				}
			} // END OF $player_stats

			if ( ! empty( $stat_group_output ) ) {
				$output[ $player_stat_group ] = $stat_group_output;
			}
		} // END OF $player_stat_groups

		return $output;
	}

	/**
	 * Get sums of player stats.
	 *
	 * @param array $player_stats
	 * @param array $stats_columns_season
	 *
	 * @return array
	 * @since 0.9.5
	 */
	public function get_player_total_stats_sums( $player_stats, $stats_columns_season ) {

		$output_data = [];

		if ( empty( $player_stats ) ) {
			return [];
		}

		// Get field ids to count
		$simple_ids = [ 0 ];

		foreach ( $stats_columns_season as $column ) {
			if ( 'simple' === $column->type && ! empty( $column->game_field_id ) && absint( $column->game_field_id ) ) {
				$simple_ids[] = absint( $column->game_field_id );
			}
		}

		// Get time ids
		$time_ids = [];

		foreach ( $stats_columns_season as $column ) {
			if ( 'time' === $column->type && ! empty( $column->game_field_id ) && absint( $column->game_field_id ) ) {
				$time_ids[] = absint( $column->game_field_id );
			}
		}

		foreach ( $player_stats as $team_id => $team_data ) {
			if ( ! empty( $team_data ) && is_array( $team_data ) ) {
				foreach ( $team_data as $stage_id => $stage_data ) {
					if ( ! empty( $stage_data ) && is_array( $stage_data ) ) {
						foreach ( $stage_data as $game_data ) {
							if ( ! empty( $game_data ) && is_array( $game_data ) ) {
								foreach ( $game_data as $stats_id => $stats_value ) {
									if ( in_array( (int) $stats_id, $time_ids, true ) ) {

										if ( mb_strlen( $stats_value ) && ':' !== mb_substr( $stats_value, - 3, 1 ) ) {
											continue;
										}

										if ( mb_strlen( $stats_value ) < 4 ) {
											continue;
										}

										if ( ! isset( $output_data[ $team_id ][ $stage_id ][ $stats_id ] ) ) {
											$output_data[ $team_id ][ $stage_id ][ $stats_id ] = 0;
										}

										if ( ! isset( $output_data['totals'][ $stats_id ] ) ) {
											$output_data['totals'][ $stats_id ] = 0;
										}

										$time_arr = explode( ':', $stats_value, 2 );

										$output_data[ $team_id ][ $stage_id ][ $stats_id ] += absint( $time_arr[0] ) * 60 + absint( $time_arr[1] );
										$output_data['totals'][ $stats_id ]                += absint( $time_arr[0] ) * 60 + absint( $time_arr[1] );

									} elseif ( in_array( (int) $stats_id, $simple_ids, true ) && is_numeric( $stats_value ) ) {

										if ( ! isset( $output_data[ $team_id ][ $stage_id ][ $stats_id ] ) ) {
											$output_data[ $team_id ][ $stage_id ][ $stats_id ]        = 0;
											$output_data[ $team_id ][ $stage_id ]['min'][ $stats_id ] = 0;
											$output_data[ $team_id ][ $stage_id ]['max'][ $stats_id ] = 0;
										}

										if ( ! isset( $output_data['totals'][ $stats_id ] ) ) {
											$output_data['totals'][ $stats_id ]        = 0;
											$output_data['totals']['min'][ $stats_id ] = $stats_value;
											$output_data['totals']['max'][ $stats_id ] = $stats_value;
										}

										$output_data[ $team_id ][ $stage_id ][ $stats_id ] += $stats_value;
										$output_data['totals'][ $stats_id ]                += $stats_value;

										if ( $output_data['totals']['min'][ $stats_id ] > $stats_value ) {
											$output_data['totals']['min'][ $stats_id ] = $stats_value;
										}

										if ( $output_data['totals']['max'][ $stats_id ] < $stats_value ) {
											$output_data['totals']['max'][ $stats_id ] = $stats_value;
										}

										if ( $output_data[ $team_id ][ $stage_id ]['min'][ $stats_id ] > $stats_value ) {
											$output_data[ $team_id ][ $stage_id ]['min'][ $stats_id ] = $stats_value;
										}

										if ( $output_data[ $team_id ][ $stage_id ]['max'][ $stats_id ] < $stats_value ) {
											$output_data[ $team_id ][ $stage_id ]['max'][ $stats_id ] = $stats_value;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $output_data;
	}

	/**
	 * Get sums of player stats.
	 *
	 * @param array $player_stats
	 * @param array $stats_columns_season
	 *
	 * @return array
	 * @since 0.6.1
	 */
	public function get_team_players_stats_sums( $player_stats, $stats_columns_season ) {

		$output_data = [];

		// Get field ids to count
		$simple_ids = [ 0 ];

		foreach ( $stats_columns_season as $column ) {
			if ( 'simple' === $column->type && ! empty( $column->game_field_id ) && absint( $column->game_field_id ) ) {
				$simple_ids[] = absint( $column->game_field_id );
			}
		}

		// Get time ids
		$time_ids = [];

		foreach ( $stats_columns_season as $column ) {
			if ( 'time' === $column->type && ! empty( $column->game_field_id ) && absint( $column->game_field_id ) ) {
				$time_ids[] = absint( $column->game_field_id );
			}
		}

		foreach ( $player_stats as $player_id => $player_data ) {
			if ( ! empty( $player_data ) && is_array( $player_data ) ) {
				foreach ( $player_data as $game_data ) {
					if ( ! empty( $game_data ) && is_array( $game_data ) ) {
						foreach ( $game_data as $stats_id => $stats_value ) {
							if ( in_array( (int) $stats_id, $time_ids, true ) ) {

								if ( mb_strlen( $stats_value ) && ':' !== mb_substr( $stats_value, - 3, 1 ) ) {
									continue;
								}

								if ( mb_strlen( $stats_value ) < 4 ) {
									continue;
								}

								if ( ! isset( $output_data[ $player_id ][ $stats_id ] ) ) {
									$output_data[ $player_id ][ $stats_id ] = 0;
								}

								$time_arr = explode( ':', $stats_value, 2 );

								$output_data[ $player_id ][ $stats_id ] += absint( $time_arr[0] ) * 60 + absint( $time_arr[1] );

							} elseif ( in_array( (int) $stats_id, $simple_ids, true ) && is_numeric( $stats_value ) ) {

								if ( ! isset( $output_data[ $player_id ][ $stats_id ] ) ) {
									$output_data[ $player_id ][ $stats_id ]        = 0;
									$output_data[ $player_id ]['min'][ $stats_id ] = $stats_value;
									$output_data[ $player_id ]['max'][ $stats_id ] = $stats_value;
								}

								$output_data[ $player_id ][ $stats_id ] += $stats_value;

								if ( $output_data[ $player_id ]['min'][ $stats_id ] > $stats_value ) {
									$output_data[ $player_id ]['min'][ $stats_id ] = $stats_value;
								}

								if ( $output_data[ $player_id ]['max'][ $stats_id ] < $stats_value ) {
									$output_data[ $player_id ]['max'][ $stats_id ] = $stats_value;
								}
							}
						}
					}
				}
			}
		}

		return $output_data;
	}

	/**
	 * Get teams players for selected season grouped by roster groups.
	 *
	 * @param array $args
	 *
	 * @return array $output_data
	 * @since 0.5.18
	 */
	public function get_team_season_players_grouped( $args ) {

		$output_data = [];

		// Prepare data
		$team_id   = absint( $args['team_id'] );
		$season_id = absint( $args['season_id'] );

		// Check season id assigned
		if ( ! $season_id || ! $team_id ) {
			return $output_data;
		}

		// Get team squad meta (for all seasons)
		$squad_all = json_decode( get_post_meta( $team_id, '_sl_roster', true ) );

		if ( empty( $squad_all ) ) {
			return $output_data;
		}

		if ( ! empty( $squad_all->{'s:' . $season_id} ) ) {

			$squad_items = $squad_all->{'s:' . $season_id};
			$last_group  = '_';

			foreach ( $squad_items as $squad_item ) {
				if ( 'group' === $squad_item->type ) {
					$last_group = $squad_item->title;
				} elseif ( 'player' === $squad_item->type && absint( $squad_item->id ) ) {
					$output_data[ $last_group ][] = $squad_item->id;
				}
			}
		}

		return $output_data;
	}

	/**
	 * Get player roster group.
	 *
	 * @param int $team_id
	 * @param int $season_id
	 * @param int $player_id
	 *
	 * @return string
	 * @since 0.6.0
	 */
	public function get_player_season_roster_group( $team_id, $season_id, $player_id ) {

		$roster_group = '';

		// Prepare data
		$team_id   = absint( $team_id );
		$season_id = absint( $season_id );
		$player_id = absint( $player_id );

		// Check season id assigned
		if ( ! $season_id || ! $team_id || ! $player_id ) {
			return $roster_group;
		}

		// Get team squad meta (for all seasons)
		$squad_all = json_decode( get_post_meta( $team_id, '_sl_roster', true ) );

		if ( empty( $squad_all ) ) {
			return $roster_group;
		}

		if ( ! empty( $squad_all->{'s:' . $season_id} ) ) {

			$squad_items = $squad_all->{'s:' . $season_id};
			$last_group  = '_';

			foreach ( $squad_items as $squad_item ) {
				if ( 'group' === $squad_item->type ) {
					$last_group = $squad_item->title;
				} elseif ( 'player' === $squad_item->type && absint( $squad_item->id ) && absint( $squad_item->id ) === $player_id ) {
					$roster_group = $last_group;
				}
			}
		}

		return $roster_group;
	}

	/**
	 * Get player roster group.
	 *
	 * @param int $team_id
	 * @param int $season_id
	 * @param int $player_id
	 *
	 * @return string
	 * @since 0.12.0
	 */
	public function get_player_season_roster_position( $team_id, $season_id, $player_id ) {

		// Prepare data
		$team_id   = absint( $team_id );
		$season_id = absint( $season_id );
		$player_id = absint( $player_id );

		// Check season id assigned
		if ( ! $season_id || ! $team_id || ! $player_id ) {
			return '';
		}

		// Get team squad meta (for all seasons)
		$squad_all = json_decode( get_post_meta( $team_id, '_sl_roster', true ) );

		if ( empty( $squad_all ) ) {
			return '';
		}

		$roster_position = '';

		if ( ! empty( $squad_all->{'s:' . $season_id} ) ) {

			$squad_items = $squad_all->{'s:' . $season_id};

			foreach ( $squad_items as $squad_item ) {
				if ( 'player' === $squad_item->type && $squad_item->role && absint( $squad_item->id ) === $player_id ) {
					$roster_position = $squad_item->role;
				}
			}
		}

		return $roster_position;
	}

	/**
	 * Render player game stats value.
	 *
	 * @param      $player_stats
	 * @param      $column
	 * @param bool $raw_value
	 *
	 * @return string
	 */
	public function render_player_game_stats( $player_stats, $column, $raw_value = false ) {

		$output = 0;

		if ( empty( $column->type ) || ! in_array( $column->type, [ 'simple', 'time', 'composed', 'calculated' ], true ) ) {
			return $output;
		}

		if ( in_array( $column->type, [ 'simple', 'time' ], true ) && ! isset( $player_stats[ $column->id ] ) ) {
			return $output;
		}

		switch ( $column->type ) {
			case 'time':
				$output = $player_stats[ $column->id ];
				break;

			case 'simple':
				$stats_value = empty( $player_stats[ $column->id ] ) ? 0 : $player_stats[ $column->id ];
				$output      = number_format( floatval( $stats_value ), absint( $column->digits ), '.', '' );

				if ( ! empty( $column->prefix ) && ! $raw_value ) {
					$output = $column->prefix . $output;
				}

				if ( ! empty( $column->postfix ) && ! $raw_value ) {
					$output = $output . $column->postfix;
				}

				break;

			case 'calculated':
				$field_1 = ( isset( $column->field_1 ) && '' !== $column->field_1 ) ? $this->render_player_game_stats( $player_stats, $this->get_stats_player_game_column_by_id( $column->field_1 ), true ) : 0;
				$field_2 = ( isset( $column->field_2 ) && '' !== $column->field_2 ) ? $this->render_player_game_stats( $player_stats, $this->get_stats_player_game_column_by_id( $column->field_2 ), true ) : 0;

				$field_1 = is_numeric( $field_1 ) ? $field_1 : 0;
				$field_2 = is_numeric( $field_2 ) ? $field_2 : 0;

				switch ( $column->calc ) {
					case 'sum':
						$output = $field_1 + $field_2;
						break;

					case 'difference':
						$output = $field_1 - $field_2;
						break;

					case 'ratio':
						$output = $field_2 ? ( $field_1 / $field_2 ) : 0;
						break;

					case 'ratio_pr':
						$output = $field_2 ? ( $field_1 / $field_2 * 100 ) : 0;
						break;
				}

				$output = number_format( $output, absint( $column->digits ), '.', '' );

				if ( ! empty( $column->prefix ) && ! $raw_value ) {
					$output = $column->prefix . $output;
				}

				if ( ! empty( $column->postfix ) && ! $raw_value ) {
					$output = $output . $column->postfix;
				}
				break;

			case 'composed':
				$fields = [];

				if ( isset( $column->field_1 ) && '' !== $column->field_1 ) {
					$fields[] = $this->render_player_game_stats( $player_stats, $this->get_stats_player_game_column_by_id( $column->field_1 ), true );
				}

				if ( isset( $column->field_2 ) && '' !== $column->field_2 ) {
					$fields[] = $this->render_player_game_stats( $player_stats, $this->get_stats_player_game_column_by_id( $column->field_2 ), true );
				}

				if ( isset( $column->field_3 ) && '' !== $column->field_3 ) {
					$fields[] = $this->render_player_game_stats( $player_stats, $this->get_stats_player_game_column_by_id( $column->field_3 ), true );
				}

				$separator = $column->separator ?: '-';

				if ( ! empty( $fields ) ) {
					$output = implode( $separator, $fields );
				}

				break;
		}

		return $output;
	}

	/**
	 * Render team player season stats value.
	 *
	 * @param      $player_stats
	 * @param      $column
	 * @param bool $raw_value
	 * @param bool $time_formated
	 *
	 * @return string
	 * @since 0.6.1
	 */
	public function render_team_player_season_stats( $player_stats, $column, $raw_value = false, $time_formated = false ) {

		$output = 0;

		if ( empty( $column->type ) || ! in_array( $column->type, [ 'simple', 'time', 'played', 'composed', 'calculated' ], true ) ) {
			return $output;
		}

		if ( in_array( $column->type, [ 'simple', 'time' ], true ) && ! isset( $player_stats[ $column->game_field_id ] ) ) {
			return $output;
		}

		$game_played = isset( $player_stats[0] ) ? $player_stats[0] : 0;

		switch ( $column->type ) {
			case 'played':
				$output = $game_played;
				break;

			case 'time':
				if ( 'sum' === $column->result ) {
					$output = isset( $player_stats[ $column->game_field_id ] ) ? $player_stats[ $column->game_field_id ] : 0;
				} elseif ( 'average' === $column->result ) {
					if ( absint( $game_played ) && ! empty( $player_stats[ $column->game_field_id ] ) ) {
						$output = round( $player_stats[ $column->game_field_id ] / $game_played );
					}
				}

				if ( $time_formated ) {
					$output = absint( $output / 60 ) . ':' . str_pad( absint( $output % 60 ), 2, '0', STR_PAD_LEFT );
				}

				break;

			case 'simple':
				if ( 'sum' === $column->result ) {
					$output = isset( $player_stats[ $column->game_field_id ] ) ? $player_stats[ $column->game_field_id ] : 0;
				} elseif ( 'max' === $column->result ) {
					$output = isset( $player_stats['max'][ $column->game_field_id ] ) ? $player_stats['max'][ $column->game_field_id ] : 0;
				} elseif ( 'min' === $column->result ) {
					$output = isset( $player_stats['min'][ $column->game_field_id ] ) ? $player_stats['min'][ $column->game_field_id ] : 0;
				} elseif ( 'average' === $column->result ) {
					if ( absint( $game_played ) && ! empty( $player_stats[ $column->game_field_id ] ) ) {
						$output = $player_stats[ $column->game_field_id ] / $game_played;
					}
				}

				// Check output not empty
				if ( empty( $output ) ) {
					$output = 0;
				}

				$output = number_format( $output, absint( $column->digits ), '.', '' );

				if ( ! empty( $column->prefix ) && ! $raw_value ) {
					$output = $column->prefix . $output;
				}

				if ( ! empty( $column->postfix ) && ! $raw_value ) {
					$output = $output . $column->postfix;
				}

				break;

			case 'calculated':
				$field_1 = ( isset( $column->field_1 ) && '' !== $column->field_1 ) ? $this->render_team_player_season_stats( $player_stats, $this->get_stats_season_player_game_column_by_id( $column->field_1 ), true ) : 0;
				$field_2 = ( isset( $column->field_2 ) && '' !== $column->field_2 ) ? $this->render_team_player_season_stats( $player_stats, $this->get_stats_season_player_game_column_by_id( $column->field_2 ), true ) : 0;

				$field_1 = is_numeric( $field_1 ) ? $field_1 : 0;
				$field_2 = is_numeric( $field_2 ) ? $field_2 : 0;

				switch ( $column->calc ) {
					case 'sum':
						$output = $field_1 + $field_2;
						break;

					case 'difference':
						$output = $field_1 - $field_2;
						break;

					case 'ratio':
						$output = $field_2 ? ( $field_1 / $field_2 ) : 0;
						break;

					case 'ratio_pr':
						$output = $field_2 ? ( $field_1 / $field_2 * 100 ) : 0;
						break;
				}

				$output = number_format( $output, absint( $column->digits ), '.', '' );

				if ( ! empty( $column->prefix ) && ! $raw_value ) {
					$output = $column->prefix . $output;
				}

				if ( ! empty( $column->postfix ) && ! $raw_value ) {
					$output = $output . $column->postfix;
				}
				break;

			case 'composed':
				$fields = [];

				if ( isset( $column->field_1 ) && '' !== $column->field_1 ) {
					$fields[] = $this->render_team_player_season_stats( $player_stats, $this->get_stats_season_player_game_column_by_id( $column->field_1 ), true, true );
				}

				if ( isset( $column->field_2 ) && '' !== $column->field_2 ) {
					$fields[] = $this->render_team_player_season_stats( $player_stats, $this->get_stats_season_player_game_column_by_id( $column->field_2 ), true, true );
				}

				if ( isset( $column->field_3 ) && '' !== $column->field_3 ) {
					$fields[] = $this->render_team_player_season_stats( $player_stats, $this->get_stats_season_player_game_column_by_id( $column->field_3 ), true, true );
				}

				$separator = $column->separator ?: '-';

				if ( ! empty( $fields ) ) {
					$output = implode( $separator, $fields );
				}

				break;
		}

		return $output;
	}

	/**
	 * Get Player Game Stats column by id.
	 *
	 * @param int $column_id
	 *
	 * @return object
	 * @since 0.5.18
	 */
	public function get_stats_player_game_column_by_id( $column_id ) {
		$column = (object) [];

		$columns = json_decode( get_option( 'sl_columns_game' ) );

		if ( empty( $columns ) ) {
			return $column;
		}

		$column_filtered = wp_list_filter( $columns, [ 'id' => absint( $column_id ) ] );

		if ( ! empty( $column_filtered ) && is_array( $column_filtered ) ) {
			return reset( $column_filtered );
		}

		return $column;
	}

	/**
	 * Get Player Stats Group by id.
	 *
	 * @param int $group_id
	 *
	 * @return object
	 * @since 0.12.5
	 */
	public function get_stats_group_by_id( $group_id ) {
		$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : '';

		foreach ( $stat_groups as $stat_group ) {
			if ( $group_id === $stat_group->id ) {
				return $stat_group;
			}
		}

		return (object) [];
	}

	/**
	 * Get Stats Game options
	 *
	 * @return array
	 * @since 0.7.0
	 */
	public function get_stats_game_simple_options() {
		$options = [];

		$columns = json_decode( get_option( 'sl_columns_game' ) );

		if ( empty( $columns ) || ! is_array( $columns ) ) {
			return $options;
		}

		foreach ( $columns as $column ) {
			if ( 'simple' === $column->type ) {
				$options[ $column->id ] = $column->name;
			}
		}

		return $options;
	}

	/**
	 * Get Stats Game options (simple + time + calculated)
	 *
	 * @return array
	 * @since 0.9ю5
	 */
	public function get_stats_game_countable_options() {

		$options = [];

		$columns = json_decode( get_option( 'sl_columns_game' ) );

		if ( empty( $columns ) || ! is_array( $columns ) ) {
			return $options;
		}

		foreach ( $columns as $column ) {
			if ( in_array( $column->type, [ 'simple', 'time', 'calculated' ], true ) ) {
				$options[ $column->id ] = $column->name;
			}
		}

		return $options;
	}

	/**
	 * Get Season Player Game Stats column by id.
	 *
	 * @param int $column_id
	 *
	 * @return object
	 * @since 0.5.18
	 */
	public function get_stats_season_player_game_column_by_id( $column_id ) {
		$column = (object) [];

		$columns = json_decode( get_option( 'sl_columns_season' ) );

		if ( empty( $columns ) ) {
			return $column;
		}

		$column_filtered = wp_list_filter( $columns, [ 'id' => absint( $column_id ) ] );

		if ( ! empty( $column_filtered ) && is_array( $column_filtered ) ) {
			return reset( $column_filtered );
		}

		return $column;
	}

	/**
	 * Remove game player stats.
	 *
	 * @param $game_id
	 *
	 * @return bool
	 * @since 0.6.1
	 */
	public function remove_game_player_stats( $game_id ) {
		global $wpdb;

		if ( ! absint( $game_id ) ) {
			return false;
		}

		$stats_table = $wpdb->prefix . 'sl_player_statistics';

		return $wpdb->delete( $stats_table, [ 'game_id' => $game_id ] );
	}

	public function get_player_stat_group( $position, $stat_groups = [] ) {
		if ( empty( $stat_groups ) ) {
			$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : [];
		}

		$group_map = [];

		foreach ( $stat_groups as $stat_group ) {
			if ( ! empty( $stat_group->roles ) ) {
				foreach ( $stat_group->roles as $role ) {
					$group_map[ $role ] = $stat_group->id;
				}
			}
		}

		if ( empty( $position ) ) {
			$position = '--no--';
		}

		return isset( $group_map[ $position ] ) ? $group_map[ $position ] : '';
	}

	/**
	 * Get player statistical groups assigned.
	 *
	 * @param $position
	 * @param $stat_groups
	 *
	 * @return mixed|string
	 * @since 0.12.5
	 */
	public function get_player_stat_groups( $position, $stat_groups = [] ) {
		if ( empty( $stat_groups ) ) {
			$stat_groups = get_option( 'sl_stat_groups' ) ? json_decode( get_option( 'sl_stat_groups' ) ) : [];
		}

		$group_map = [];

		foreach ( $stat_groups as $stat_group ) {
			if ( ! empty( $stat_group->roles ) ) {
				foreach ( $stat_group->roles as $role ) {
					$group_map[ $role ][] = $stat_group->id;
				}
			}
		}

		if ( empty( $position ) ) {
			$position = '--no--';
		}

		return isset( $group_map[ $position ] ) ? $group_map[ $position ] : [];
	}

	/**
	 * Check if table structure sync needed with Player Statistics config
	 *
	 * @return int[] Stat/Column IDs to create
	 */
	public function check_player_stats_db_sync_needed(): array {
		global $wpdb;

		$stat_config = json_decode( get_option( 'sl_columns_game' ), true ) ?: [];

		if ( empty( $stat_config ) ) {
			return [];
		}

		$columns_to_create = [];

		foreach ( $stat_config as $stat_column ) {
			if ( ! in_array( $stat_column['type'], [ 'simple', 'time' ], true ) || ! absint( $stat_column['id'] ) ) {
				continue;
			}

			$column_slug = 'c_id__' . absint( $stat_column['id'] );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM {$wpdb->prefix}sl_player_statistics LIKE '$column_slug';" ) ) {
				$columns_to_create[] = absint( $stat_column['id'] );
			}
		}

		return $columns_to_create;
	}

	/**
	 * Create Statistical column in Players Table
	 *
	 * @param int $stat_id
	 *
	 * @return bool
	 */
	public function create_stat_column_in_players_table( int $stat_id ) {
		global $wpdb;

		$columns = json_decode( get_option( 'sl_columns_game' ), true );

		if ( empty( $columns ) ) {
			return true;
		}

		$stat_column_exists_in_config = false;

		foreach ( $columns as $column ) {
			if ( absint( $column['id'] ) === $stat_id && in_array( $column['type'], [ 'simple', 'time' ], true ) ) {
				$stat_column_exists_in_config = true;
				break;
			}
		}

		if ( ! $stat_column_exists_in_config ) {
			return true;
		}

		$column_slug = 'c_id__' . absint( $stat_id );

		try {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}sl_player_statistics` LIKE '$column_slug';" ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				return (bool) $wpdb->query( "ALTER TABLE {$wpdb->prefix}sl_player_statistics ADD COLUMN `{$column_slug}` varchar(100) NOT NULL DEFAULT '';" );
			}
		} catch ( Exception $exception ) {
			error_log( $exception ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}

		return true;
	}

	/**
	 * Update Player Advanced Stats game data
	 *
	 * @param int   $game_id
	 * @param int   $player_id
	 * @param int   $team_id
	 * @param array $st_data
	 *
	 * @since 0.13.0
	 * @return bool
	 */
	public function update_player_advanced_game_stats( int $game_id, int $player_id, int $team_id, array $st_data ): bool {
		global $wpdb;

		if ( empty( $game_id ) || empty( $player_id ) || empty( $team_id ) ) {
			return false;
		}

		$columns = json_decode( get_option( 'sl_columns_game' ), true );

		if ( empty( $columns ) ) {
			return false;
		}

		$update_data_arr = [];

		foreach ( $columns as $column ) {
			if ( in_array( $column['type'], [ 'simple', 'time' ], true ) && absint( $column['id'] ) ) {
				$update_data_arr[ 'c_id__' . absint( $column['id'] ) ] = $st_data[ $column['id'] ] ?? '';
			}
		}

		return false !== $wpdb->replace(
			$wpdb->prefix . 'sl_player_statistics',
			array_merge(
				$update_data_arr,
				[
					'game_id'   => $game_id,
					'player_id' => $player_id,
					'team_id'   => $team_id,
				]
			)
		);
	}

	public function get_player_db_stat_ids() {
		global $wpdb;

		$columns = [];

		$fields = $wpdb->get_results( 'DESCRIBE ' . $wpdb->prefix . 'sl_player_statistics' );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				if ( false !== strpos( $column->Field, 'c_id__' ) ) { //phpcs:ignore WordPress.NamingConventions
					$columns[] = absint( str_replace( 'c_id__', '', $column->Field ) ); //phpcs:ignore WordPress.NamingConventions
				}
			}
		}

		return $columns;
	}

	/**
	 * Save statistic configuration
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function save_stat_config( WP_REST_Request $request ) {
		$stat_options = $request->get_params();

		update_option( 'sl_stat_groups', wp_json_encode( $stat_options['stat_groups'] ?? [] ), false );
		update_option( 'sl_columns_game', wp_json_encode( $stat_options['columns_game'] ?? [] ), false );
		update_option( 'sl_columns_game_last_id', absint( $stat_options['columns_game_last_id'] ?? 0 ), false );
		update_option( 'sl_columns_season', wp_json_encode( $stat_options['columns_season'] ?? [] ), true );
		update_option( 'sl_columns_season_last_id', absint( $stat_options['columns_season_last_id'] ?? 0 ), false );

		foreach ( $this->check_player_stats_db_sync_needed() as $stat_column_to_create ) {
			$this->create_stat_column_in_players_table( $stat_column_to_create );
		}

		return rest_ensure_response( [ 'result' => true ] );
	}
}
