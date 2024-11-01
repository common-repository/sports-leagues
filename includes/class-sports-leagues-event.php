<?php
/**
 * Sports Leagues Event.
 *
 * @since   0.5.15
 * @package Sports_Leagues
 */

class Sports_Leagues_Event {

	/**
	 * Parent plugin class.
	 *
	 * @var    Sports_Leagues
	 * @since  0.5.15
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
	 * @since  0.5.15
	 */
	public function hooks() {
		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.13.2
	 */
	public function add_rest_routes() {
		register_rest_route(
			'sports-leagues/v1',
			'/events/save_events_config',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_events_config' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Save events Config
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.11.0
	 */
	public function save_events_config( WP_REST_Request $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_invalid', 'Access Denied !!!', [ 'status' => 400 ] );
		}

		$params    = $request->get_params();
		$old_value = get_option( 'sports_leagues_event', [] );

		$events_config = [
			'events' => isset( $params['events'] ) ? sports_leagues()->helper->recursive_sanitize( $params['events'] ) : [],
		];

		if ( $events_config === $old_value || maybe_serialize( $events_config ) === maybe_serialize( $old_value ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => 'Nothing to Update',
				],
				200
			);
		}

		if ( ! update_option( 'sports_leagues_event', $events_config, true ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'data'    => 'Error : Update Problem',
				],
				200
			);
		}

		return new WP_REST_Response(
			[
				'success' => true,
			],
			200
		);
	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @since  0.5.15
	 *
	 * @param  string $key     Options array key
	 * @param  mixed  $default Optional default value
	 * @return mixed           Option value
	 */
	public static function get_value( $key = '', $default = false ) {

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( 'sports_leagues_event', $default );

		$val = $default;

		if ( 'all' === $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}

	/**
	 * Get predefined event icons.
	 *
	 * @since 0.5.15
	 * @return array
	 */
	public function get_event_icons() {

		static $options = null;

		if ( null === $options ) {

			$options = [
				[
					'slug'  => 'puck',
					'url'   => Sports_Leagues::url( 'icons/puck.svg' ),
					'sport' => 'ice-hockey',
				],
				[
					'slug'  => 'basket-ball',
					'url'   => Sports_Leagues::url( 'icons/basket-ball.svg' ),
					'sport' => 'basketball',
				],
				[
					'slug'  => 'arrow-in',
					'url'   => Sports_Leagues::url( 'icons/arrow-in.svg' ),
					'sport' => 'all',
				],
				[
					'slug'  => 'arrow-out',
					'url'   => Sports_Leagues::url( 'icons/arrow-out.svg' ),
					'sport' => 'all',
				],
				[
					'slug'  => 'am',
					'url'   => Sports_Leagues::url( 'icons/am.svg' ),
					'sport' => 'football',
				],
				[
					'slug'  => 'am-1',
					'url'   => Sports_Leagues::url( 'icons/am-1.svg' ),
					'sport' => 'football',
				],
				[
					'slug'  => 'am-2',
					'url'   => Sports_Leagues::url( 'icons/am-2.svg' ),
					'sport' => 'football',
				],
				[
					'slug'  => 'am-3',
					'url'   => Sports_Leagues::url( 'icons/am-3.svg' ),
					'sport' => 'football',
				],
				[
					'slug'  => 'am-6',
					'url'   => Sports_Leagues::url( 'icons/am-6.svg' ),
					'sport' => 'football',
				],
				[
					'slug'  => 'am-goal',
					'url'   => Sports_Leagues::url( 'icons/am-goal.svg' ),
					'sport' => 'football',
				],
				[
					'slug'  => 'am-sf',
					'url'   => Sports_Leagues::url( 'icons/am-sf.svg' ),
					'sport' => 'football',
				],
			];

			/**
			 * Extend list of predefined icons.
			 *
			 * @param array  List default icons.
			 *
			 * @since 0.5.15
			 */
			$options = apply_filters( 'sports-leagues/config/event_icons_default', $options );
		}

		return $options;
	}

	/**
	 * Get event icon URL.
	 *
	 * @param string $slug
	 *
	 * @return string
	 * @since 0.5.15
	 */
	public function get_icon_url_by_slug( $slug ) {

		$url = '';

		$icons = $this->get_event_icons();

		foreach ( $icons as $icon ) {
			if ( $slug === $icon['slug'] ) {
				$url = $icon['url'];
				break;
			}
		}

		return $url;
	}

	/**
	 * Get event options.
	 *
	 * @return array (name => icon)
	 * @since 0.5.15
	 */
	public function get_options() {

		$options = [];
		$events  = self::get_value( 'events' );

		if ( ! empty( $events ) && is_array( $events ) ) {
			foreach ( $events as $event ) {

				if ( empty( $event['name'] ) ) {
					continue;
				}

				if ( ! empty( $event['icon'] ) && $this->get_icon_url_by_slug( $event['icon'] ) ) {
					$options[ $event['name'] ] = $this->get_icon_url_by_slug( $event['icon'] );
				} elseif ( ! empty( $event['custom_icon'] ) ) {
					$options[ $event['name'] ] = $event['custom_icon'];
				} else {
					$options[ $event['name'] ] = '';
				}
			}
		}

		return $options;
	}

	/**
	 * Get Events for Configurator
	 *
	 * @return array
	 * @since 0.7.1
	 */
	public function get_configurator_events() {

		$parsed_events = [];

		// Get Saved Events
		$game_events = self::get_value( 'events' );

		$parsed_fields = [
			'name'                  => '',
			'icon'                  => '',
			'custom_icon'           => '',
			'custom_icon_id'        => '',
			'show_in_game_header'   => '',
			'show_in_players_block' => '',
		];

		/**
		 * Extend list of events.
		 *
		 * @param array  List default parsed events.
		 *
		 * @since 0.7.1
		 */
		$parsed_fields = apply_filters( 'sports-leagues/config/event_parsed_fields', $parsed_fields );

		if ( ! empty( $game_events ) && is_array( $game_events ) ) {
			foreach ( $game_events as $event_index => $game_event ) {
				$game_event = wp_parse_args(
					$game_event,
					$parsed_fields
				);

				$game_event['id'] = $event_index;
				$parsed_events[]  = $game_event;
			}
		}

		return $parsed_events;
	}

	/**
	 * Save Game events
	 *
	 * @param WP_Post $post
	 * @param array   $game_data
	 * @param array   $posted
	 *
	 * @since 0.5.15
	 */
	public function save_game_events( $post, $game_data, $posted ) {

		global $wpdb;

		$table = $wpdb->prefix . 'sl_events';

		$events = [];
		$saved  = [];

		/*
		|--------------------------------------------------------------------
		| Get Events from POST data
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $posted['_sl_game_events'] ) && ! empty( json_decode( $posted['_sl_game_events'] ) ) ) {
			$events = json_decode( $posted['_sl_game_events'] );
		}

		/*
		|--------------------------------------------------------------------
		| Delete not exist events
		|--------------------------------------------------------------------
		*/
		// Get Saved IDs
		$ids_old = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT id
				FROM $table
				WHERE game_id = %d
				",
				$post->ID
			)
		);

		$ids_to_delete = array_diff( $ids_old, wp_list_pluck( $events, 'id' ) );

		foreach ( $ids_to_delete as $id ) {
			$wpdb->delete( $table, [ 'id' => $id ] );
		}

		/*
		|--------------------------------------------------------------------
		| Prepare data for save
		|--------------------------------------------------------------------
		*/
		foreach ( $events as $sorting => $event ) {

			$is_new = '_' === mb_substr( $event->id, 0, 1 );

			// Prepare data to insert
			$data = [
				'type'        => $event->type,
				'game_id'     => $post->ID,
				'team_id'     => absint( $event->team ),
				'team_id_opp' => '',
				'time'        => empty( $event->time ) ? '' : $event->time,
				'player_id'   => empty( $event->player ) ? '' : $event->player,
				'sorting'     => $sorting,
				'score'       => '',
				'params'      => mb_strpos( $event->player, 'temp__' ) !== false ? $event->player : '',
				'comment'     => sanitize_textarea_field( $event->comment ),
			];

			if ( $data['team_id'] ) {
				$data['team_id_opp'] = absint( $data['team_id'] ) === absint( $game_data['team_home'] ) ? $game_data['team_away'] : $game_data['team_home'];
			}

			if ( ! $is_new ) {
				$data['id'] = $event->id;
			}

			/**
			 * Filters event data before save to DB
			 *
			 * @param array   $data
			 * @param WP_Post $post
			 * @param array   $game_data
			 * @param array   $posted
			 * @param object  $event
			 *
			 * @since 0.5.15
			 */
			$data = apply_filters( 'sports-leagues/event/saved_data', $data, $post, $game_data, $posted, $event );

			// Insert data to DB
			$wpdb->replace( $table, $data );

			if ( $is_new ) {
				$data['id'] = $wpdb->insert_id;
			}

			$saved[ $sorting ] = $data;
		}

		update_post_meta( $post->ID, '_sl_events', wp_slash( wp_json_encode( $saved ) ) );
	}

	/**
	 * Get Game events to edit
	 *
	 * @param int $post_id
	 *
	 * @return array
	 * @since 0.5.15
	 */
	public function get_game_events_to_edit( $post_id ) {

		global $wpdb;

		$events = [];

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT *
					FROM {$wpdb->prefix}sl_events
					WHERE game_id = %d
					ORDER BY sorting
					",
				$post_id
			)
		);

		foreach ( $rows as $row ) {

			$event = [
				'id'      => $row->id,
				'type'    => $row->type,
				'team'    => absint( $row->team_id ) ?: '',
				'time'    => $row->time,
				'comment' => $row->comment,
				'player'  => absint( $row->player_id ) ?: '',
			];

			if ( mb_strpos( $row->params, 'temp__' ) !== false ) {
				$event['player'] = $row->params;
			}

			/**
			 * Filters event data prepared for edit form
			 *
			 * @param array  $event
			 * @param int    $post_id
			 * @param object $row
			 *
			 * @since 0.5.15
			 */
			$event = apply_filters( 'sports-leagues/event/prepared_event_data_to_edit', $event, $post_id, $row );

			$events[] = $event;
		}

		return $events;
	}

	/**
	 * Get Game events to render
	 *
	 * @param int    $post_id
	 * @param string $type
	 *
	 * @return array
	 * @since 0.5.15
	 */
	public function get_game_events_to_render( $post_id, $type ) {

		global $wpdb;

		$events = [];

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT *
					FROM {$wpdb->prefix}sl_events
					WHERE game_id = %d
					ORDER BY sorting
					",
				$post_id
			)
		);

		foreach ( $rows as $row ) {
			if ( ! absint( $row->player_id ) && mb_strpos( $row->params, 'temp__' ) !== false ) {
				$row->player_id = $row->params;
			}
		}

		$events_config = self::get_value( 'events' );

		switch ( $type ) {

			/*
			|--------------------------------------------------------------------
			| Players Block
			|--------------------------------------------------------------------
			*/
			case 'players':
				$supported_events = wp_list_filter(
					$events_config,
					[ 'show_in_players_block' => 'no' ],
					'NOT'
				);

				$supported_event_types = wp_list_pluck( $supported_events, 'name' );

				foreach ( $rows as $row ) {
					if ( in_array( $row->type, $supported_event_types, true ) && absint( $row->player_id ) ) {
						$events[ $row->player_id ][] = $row;
					}
				}

				break;

			/*
			|--------------------------------------------------------------------
			| Header
			|--------------------------------------------------------------------
			*/
			case 'header':
				$supported_events = wp_list_filter(
					$events_config,
					[ 'show_in_game_header' => 'no' ],
					'NOT'
				);

				$supported_event_types = wp_list_pluck( $supported_events, 'name' );

				foreach ( $rows as $row ) {

					if ( in_array( $row->type, $supported_event_types, true ) && isset( $row->team_id ) && absint( $row->team_id ) ) {
						$events[ $row->team_id ][] = $row;
					}
				}

				break;
		}

		return $events;
	}

	/**
	 * Rendering Event on frontend.
	 *
	 * @param $context
	 * @param $event_object
	 * @param $side
	 * @param $temp_players
	 *
	 * @since 0.5.15
	 */
	public function render_event( $context, $event_object, $side = '', $temp_players = [] ) {

		static $events_config = null;

		if ( null === $events_config ) {
			$event_options = self::get_value( 'events' );

			foreach ( $event_options as $event_option ) {

				if ( empty( $event_option['name'] ) ) {
					continue;
				}

				$events_config[ $event_option['name'] ] = $event_option;

				if ( ! empty( $event_option['icon'] ) && $this->get_icon_url_by_slug( $event_option['icon'] ) ) {
					$events_config[ $event_option['name'] ]['icon_url'] = $this->get_icon_url_by_slug( $event_option['icon'] );
				} elseif ( ! empty( $event_option['custom_icon'] ) ) {
					$events_config[ $event_option['name'] ]['icon_url'] = $event_option['custom_icon'];
				}
			}
		}

		if ( empty( $events_config[ $event_object->type ] ) ) {
			return;
		}

		$current_event = $events_config[ $event_object->type ];

		switch ( $context ) {
			case 'players':
				if ( empty( $current_event['icon_url'] ) ) {
					return;
				}
				?>
				<div class="ml-2 text-nowrap d-flex align-items-center">
					<span class="d-inline-block game-player__event" style="background-image: url(<?php echo esc_url( $current_event['icon_url'] ); ?>);"></span>
					<?php if ( 'icon_time' === $current_event['show_in_players_block'] ) : ?>
						<span class="d-inline-block game-player__event-time ml-1"><?php echo esc_html( $event_object->time ); ?></span>
					<?php endif; ?>
				</div>
				<?php
				break;

			case 'header':
				if ( empty( $event_object->player_id ) ) {
					return;
				}

				if ( mb_strpos( $event_object->player_id, 'temp__' ) !== false && ! empty( $temp_players ) && ! empty( $temp_players[ $event_object->player_id ] ) ) {
					$player             = $temp_players[ $event_object->player_id ];
					$player->name_short = $player->title;
				} else {
					$player = $this->plugin->player->get_player( $event_object->player_id );
				}
				?>
				<div class="d-flex flex-wrap align-items-center game-header__event <?php echo ( 'away' === $side ) ? 'flex-row-reverse' : ''; ?> mb-2 mb-sm-0">
					<img loading="lazy" class="game-header__event-icon anwp-object-contain anwp-w-15 anwp-h-15 mx-1"
							src="<?php echo esc_url( $current_event['icon_url'] ); ?>" alt="event icon">

					<?php if ( 'player_icon_time' === $current_event['show_in_game_header'] ) : ?>
						<span class="game-header__event-time mx-sm-1 mx-2"><?php echo esc_html( $event_object->time ); ?></span>
						<span class="game-header__event-separator d-none d-sm-block anwp-opacity-50 mx-1">-</span>
					<?php endif; ?>

					<div class="d-sm-none w-100"></div>
					<div class="game-header__event-player text-nowrap mx-1"><?php echo isset( $player->name_short ) ? esc_html( $player->name_short ) : ''; ?></div>
				</div>
				<?php
				break;
		}
	}

	/**
	 * Remove game events.
	 *
	 * @param $game_id
	 *
	 * @return bool
	 * @since 0.6.1
	 */
	public function remove_game_events( $game_id ) {
		global $wpdb;

		if ( ! absint( $game_id ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'sl_events';

		return $wpdb->delete( $table, [ 'game_id' => absint( $game_id ) ] );
	}
}
