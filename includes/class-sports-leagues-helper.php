<?php
/**
 * Sports Leagues :: Helper.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Helper {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param Sports_Leagues $plugin Main plugin object.
	 *
	 * @since  0.1.0
	 *
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		add_action( 'wp_ajax_anwp_sl_selector_data', [ $this, 'get_selector_data' ] );
		add_action( 'wp_ajax_anwp_sl_selector_initial', [ $this, 'get_selector_initial' ] );

		// Modify CMB2 metabox form
		add_filter( 'cmb2_get_metabox_form_format', [ $this, 'modify_cmb2_metabox_form_format' ], 10, 3 );

		add_action( 'admin_init', [ $this, 'download_csv' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.5.2
	 */
	public function add_rest_routes() {

		register_rest_route(
			'sports-leagues/v1',
			'/import/(?P<type>[a-z]+)/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_import_data' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/v1',
			'/helper/recalculate-index-tables',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'recalculate_index_tables' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/v1',
			'/helper/flush-plugin-cache',
			[
				'methods'             => 'GET',
				'callback'            => [ $this->plugin->cache, 'flush_all_cache' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/v1',
			'/dashboard/save_sport',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_sport' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Save Sport
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.11.0
	 */
	public function save_sport( WP_REST_Request $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_invalid', 'Access Denied !!!', [ 'status' => 400 ] );
		}

		$params = $request->get_params();
		$sport  = isset( $params['sport'] ) ? sanitize_text_field( $params['sport'] ) : '';

		$saved_settings = get_option( Sports_Leagues_Config::$key, [] );

		if ( ! is_array( $saved_settings ) ) {
			$saved_settings = [];
		}

		$saved_settings['sport'] = $sport;

		if ( ! update_option( Sports_Leagues_Config::$key, $saved_settings, true ) ) {
			return new WP_Error( 'rest_invalid', 'Update Problem', [ 'status' => 400 ] );
		}

		return new WP_REST_Response(
			[
				'success' => true,
			],
			200
		);
	}

	/**
	 * Handle import Rest request.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_REST_Response
	 * @since 0.5.2
	 */
	public function save_import_data( WP_REST_Request $request ) {

		$params = $request->get_params();

		$import_status = [
			'result'     => 'error',
			'post_title' => '',
			'post_url'   => '',
			'post_edit'  => '',
		];

		switch ( $params['type'] ) {
			case 'teams':
				$import_status = $this->import_teams( $params, $import_status );
				break;

			case 'players':
				$import_status = $this->import_players( $params, $import_status );
				break;

			case 'staff':
				$import_status = $this->import_staff( $params, $import_status );
				break;

			case 'officials':
				$import_status = $this->import_official( $params, $import_status );
				break;

			case 'venues':
				$import_status = $this->import_venues( $params, $import_status );
				break;
		}

		return rest_ensure_response( $import_status );
	}

	/**
	 * Import Teams.
	 *
	 * @param $params
	 * @param $import_status
	 *
	 * @return array
	 * @since 0.5.2
	 */
	protected function import_teams( $params, $import_status ) {

		$current_user_id = get_current_user_id();
		$current_time    = current_time( 'mysql' );

		$row_data    = $params['row_data'];
		$insert_mode = 'insert' === $params['mode'];

		$custom_fields_data = [];

		if ( $insert_mode ) {
			$team_data = [
				'post_title'   => '',
				'post_content' => '',
				'post_type'    => 'sl_team',
				'post_status'  => 'publish',
				'post_author'  => $current_user_id,
				'meta_input'   => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( empty( trim( $row_data['team_title'] ) ) ) {
				$import_status['post_title'] = 'Empty title not allowed';

				return $import_status;
			}
		} else {
			$team_data = [
				'ID'         => '',
				'post_type'  => 'sl_team',
				'meta_input' => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( ! empty( $row_data['team_id'] ) ) {
				if ( 'sl_team' === get_post_type( absint( $row_data['team_id'] ) ) ) {
					$team_data['ID'] = absint( $row_data['team_id'] );
				}
			} elseif ( ! empty( $row_data['team_external_id'] ) ) {
				$maybe_team_id = sports_leagues()->team->get_team_id_by_external_id( $row_data['team_external_id'] );

				if ( ! empty( $maybe_team_id ) ) {
					$team_data['ID'] = absint( $maybe_team_id );
				}
			}

			if ( empty( $team_data['ID'] ) ) {
				$import_status['post_title'] = 'Invalid Team ID or External Team ID';

				return $import_status;
			}
		}

		foreach ( $row_data as $slug => $value ) {

			if ( empty( $value ) || in_array( $slug, [ 'import_info', 'import_status' ], true ) ) {
				continue;
			}

			switch ( $slug ) {
				case 'team_title':
					$team_data['post_title'] = sanitize_text_field( $value );
					break;

				case 'abbreviation':
					$team_data['meta_input']['_sl_abbr'] = sanitize_text_field( $value );
					break;

				case 'city':
					$team_data['meta_input']['_sl_city'] = sanitize_text_field( $value );
					break;

				case 'address':
					$team_data['meta_input']['_sl_address'] = sanitize_text_field( $value );
					break;

				case 'website':
					$team_data['meta_input']['_sl_website'] = sanitize_text_field( $value );
					break;

				case 'founded':
					$team_data['meta_input']['_sl_founded'] = sanitize_text_field( $value );
					break;

				case 'country':
					$team_data['meta_input']['_sl_nationality'] = sanitize_text_field( $value );
					break;

				case 'team_external_id':
					$team_data['meta_input']['_sl_team_external_id'] = sanitize_text_field( $value );
					break;

				case 'is_national_team':
					if ( 'yes' === sanitize_text_field( $value ) ) {
						$team_data['meta_input']['_sl_is_national_team'] = 'yes';
					}
					break;

				default:
					if ( 0 === mb_strpos( $slug, 'cf__' ) ) {

						$maybe_custom_field = mb_substr( $slug, 4 );

						if ( ! empty( $maybe_custom_field ) ) {
							$custom_fields_data[ $maybe_custom_field ] = sanitize_text_field( $value );
						}
					}
			}
		}

		// Custom Fields
		if ( ! empty( $custom_fields_data ) ) {
			$custom_fields_old = get_post_meta( $team_data['ID'], '_sl_custom_fields', true );

			if ( ! empty( $custom_fields_old ) && is_array( $custom_fields_old ) ) {
				$custom_fields_data = array_merge( $custom_fields_old, $custom_fields_data );
			}
		}

		if ( ! empty( $custom_fields_data ) ) {
			$team_data['meta_input']['_sl_custom_fields'] = $custom_fields_data;
		}

		$post_id = $insert_mode ? wp_insert_post( $team_data ) : wp_update_post( $team_data );

		if ( absint( $post_id ) ) {
			$post_obj = get_post( $post_id );

			$import_status['result']     = 'success';
			$import_status['post_title'] = $post_obj->post_title;
			$import_status['post_url']   = get_permalink( $post_obj );
			$import_status['post_edit']  = get_edit_post_link( $post_obj );
		}

		return $import_status;
	}

	/**
	 * Import Venues.
	 *
	 * @param $params
	 * @param $import_status
	 *
	 * @return array
	 * @since 0.10.3
	 */
	protected function import_venues( $params, $import_status ) {

		$current_user_id = get_current_user_id();
		$current_time    = current_time( 'mysql' );

		$row_data    = $params['row_data'];
		$insert_mode = 'insert' === $params['mode'];

		$custom_fields_data = [];

		if ( $insert_mode ) {
			$venue_data = [
				'post_title'   => '',
				'post_content' => '',
				'post_type'    => 'sl_venue',
				'post_status'  => 'publish',
				'post_author'  => $current_user_id,
				'meta_input'   => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( empty( trim( $row_data['venue_title'] ) ) ) {
				$import_status['post_title'] = 'Empty title not allowed';

				return $import_status;
			}
		} else {
			$venue_data = [
				'ID'         => '',
				'post_type'  => 'sl_venue',
				'meta_input' => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( ! empty( $row_data['venue_id'] ) ) {
				if ( 'sl_venue' === get_post_type( absint( $row_data['venue_id'] ) ) ) {
					$venue_data['ID'] = absint( $row_data['venue_id'] );
				}
			} elseif ( ! empty( $row_data['venue_external_id'] ) ) {
				$maybe_venue_id = sports_leagues()->venue->get_venue_id_by_external_id( $row_data['venue_external_id'] );

				if ( ! empty( $maybe_venue_id ) ) {
					$venue_data['ID'] = absint( $maybe_venue_id );
				}
			}

			if ( empty( $venue_data['ID'] ) ) {
				$import_status['post_title'] = 'Invalid Venue ID or External Venue ID';

				return $import_status;
			}
		}

		foreach ( $row_data as $slug => $value ) {

			if ( empty( $value ) || in_array( $slug, [ 'import_info', 'import_status' ], true ) ) {
				continue;
			}

			switch ( $slug ) {
				case 'venue_title':
					$venue_data['post_title'] = sanitize_text_field( $value );
					break;

				case 'city':
					$venue_data['meta_input']['_sl_city'] = sanitize_text_field( $value );
					break;

				case 'address':
					$venue_data['meta_input']['_sl_address'] = sanitize_text_field( $value );
					break;

				case 'website':
					$venue_data['meta_input']['_sl_website'] = sanitize_text_field( $value );
					break;

				case 'capacity':
					$venue_data['meta_input']['_sl_capacity'] = sanitize_text_field( $value );
					break;

				case 'opened':
					$venue_data['meta_input']['_sl_opened'] = sanitize_text_field( $value );
					break;

				case 'description':
					$venue_data['meta_input']['_sl_description'] = sanitize_textarea_field( $value );
					break;

				case 'venue_external_id':
					$venue_data['meta_input']['_sl_venue_external_id'] = sanitize_text_field( $value );
					break;

				default:
					if ( 0 === mb_strpos( $slug, 'cf__' ) ) {

						$maybe_custom_field = mb_substr( $slug, 4 );

						if ( ! empty( $maybe_custom_field ) ) {
							$custom_fields_data[ $maybe_custom_field ] = sanitize_text_field( $value );
						}
					}
			}
		}

		// Custom Fields
		if ( ! empty( $custom_fields_data ) ) {
			$custom_fields_old = get_post_meta( $venue_data['ID'], '_sl_custom_fields', true );

			if ( ! empty( $custom_fields_old ) && is_array( $custom_fields_old ) ) {
				$custom_fields_data = array_merge( $custom_fields_old, $custom_fields_data );
			}
		}

		if ( ! empty( $custom_fields_data ) ) {
			$venue_data['meta_input']['_sl_custom_fields'] = $custom_fields_data;
		}

		$post_id = $insert_mode ? wp_insert_post( $venue_data ) : wp_update_post( $venue_data );

		if ( absint( $post_id ) ) {
			$post_obj = get_post( $post_id );

			$import_status['result']     = 'success';
			$import_status['post_title'] = $post_obj->post_title;
			$import_status['post_url']   = get_permalink( $post_obj );
			$import_status['post_edit']  = get_edit_post_link( $post_obj );
		}

		return $import_status;
	}

	/**
	 * Import Players.
	 *
	 * @param $params
	 * @param $import_status
	 *
	 * @return array
	 * @since 0.5.2
	 */
	protected function import_players( $params, $import_status ) {

		$current_user_id = get_current_user_id();
		$current_time    = current_time( 'mysql' );

		$row_data    = $params['row_data'];
		$insert_mode = 'insert' === $params['mode'];

		$custom_fields_data = [];

		if ( $insert_mode ) {
			$player_data = [
				'post_title'   => '',
				'post_content' => '',
				'post_type'    => 'sl_player',
				'post_status'  => 'publish',
				'post_author'  => $current_user_id,
				'meta_input'   => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( empty( trim( $row_data['player_name'] ) ) ) {
				$import_status['post_title'] = 'Empty Name not allowed';

				return $import_status;
			}
		} else {
			$player_data = [
				'ID'         => '',
				'post_type'  => 'sl_player',
				'meta_input' => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( ! empty( $row_data['player_id'] ) ) {
				if ( 'sl_player' === get_post_type( absint( $row_data['player_id'] ) ) ) {
					$player_data['ID'] = absint( $row_data['player_id'] );
				}
			} elseif ( ! empty( $row_data['player_external_id'] ) ) {
				$maybe_player_id = sports_leagues()->player->get_player_id_by_external_id( $row_data['player_external_id'] );

				if ( ! empty( $maybe_player_id ) ) {
					$player_data['ID'] = absint( $maybe_player_id );
				}
			}

			if ( empty( $player_data['ID'] ) ) {
				$import_status['post_title'] = 'Invalid Player ID or External Player ID';

				return $import_status;
			}
		}

		foreach ( $row_data as $slug => $value ) {

			if ( empty( $value ) || in_array( $slug, [ 'import_info', 'import_status' ], true ) ) {
				continue;
			}

			switch ( $slug ) {
				case 'player_name':
					$player_data['post_title'] = sanitize_text_field( $value );
					break;

				case 'short_name':
					$player_data['meta_input']['_sl_short_name'] = sanitize_text_field( $value );
					break;

				case 'full_name':
					$player_data['meta_input']['_sl_full_name'] = sanitize_text_field( $value );
					break;

				case 'weight':
					$player_data['meta_input']['_sl_weight'] = sanitize_text_field( $value );
					break;

				case 'height':
					$player_data['meta_input']['_sl_height'] = sanitize_text_field( $value );
					break;

				case 'position':
					$player_data['meta_input']['_sl_position'] = sanitize_text_field( $value );
					break;

				case 'current_team':
					$player_data['meta_input']['_sl_current_team'] = sanitize_text_field( $value );
					break;

				case 'national_team':
					$player_data['meta_input']['_sl_national_team'] = sanitize_text_field( $value );
					break;

				case 'place_of_birth':
					$player_data['meta_input']['_sl_place_of_birth'] = sanitize_text_field( $value );
					break;

				case 'date_of_birth':
					$player_data['meta_input']['_sl_date_of_birth'] = sanitize_text_field( $value );
					break;

				case 'date_of_death':
					$player_data['meta_input']['_sl_date_of_death'] = sanitize_text_field( $value );
					break;

				case 'nationality_1':
				case 'nationality_2':
					$player_data['meta_input']['_sl_nationality'][] = sanitize_text_field( $value );
					break;

				case 'country_of_birth':
					$player_data['meta_input']['_sl_country_of_birth'] = sanitize_text_field( $value );
					break;

				case 'bio':
					$player_data['meta_input']['_sl_bio'] = sanitize_textarea_field( $value );
					break;

				case 'player_external_id':
					$player_data['meta_input']['_sl_player_external_id'] = sanitize_text_field( $value );
					break;

				default:
					if ( 0 === mb_strpos( $slug, 'cf__' ) ) {

						$maybe_custom_field = mb_substr( $slug, 4 );

						if ( ! empty( $maybe_custom_field ) ) {
							$custom_fields_data[ $maybe_custom_field ] = sanitize_text_field( $value );
						}
					}
			}
		}

		// Custom Fields
		if ( ! empty( $custom_fields_data ) ) {
			$custom_fields_old = get_post_meta( $player_data['ID'], '_sl_custom_fields', true );

			if ( ! empty( $custom_fields_old ) && is_array( $custom_fields_old ) ) {
				$custom_fields_data = array_merge( $custom_fields_old, $custom_fields_data );
			}
		}

		if ( ! empty( $custom_fields_data ) ) {
			$player_data['meta_input']['_sl_custom_fields'] = $custom_fields_data;
		}

		$post_id = $insert_mode ? wp_insert_post( $player_data ) : wp_update_post( $player_data );

		if ( absint( $post_id ) ) {
			$post_obj = get_post( $post_id );

			$import_status['result']     = 'success';
			$import_status['post_title'] = $post_obj->post_title;
			$import_status['post_url']   = get_permalink( $post_obj );
			$import_status['post_edit']  = get_edit_post_link( $post_obj );
		}

		return $import_status;
	}

	/**
	 * Import Staff.
	 *
	 * @param $params
	 * @param $import_status
	 *
	 * @return array
	 * @since 0.10.3
	 */
	protected function import_staff( $params, $import_status ) {

		$current_user_id = get_current_user_id();
		$current_time    = current_time( 'mysql' );

		$row_data    = $params['row_data'];
		$insert_mode = 'insert' === $params['mode'];

		$custom_fields_data = [];

		if ( $insert_mode ) {
			$staff_data = [
				'post_title'   => '',
				'post_content' => '',
				'post_type'    => 'sl_staff',
				'post_status'  => 'publish',
				'post_author'  => $current_user_id,
				'meta_input'   => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( empty( trim( $row_data['staff_name'] ) ) ) {
				$import_status['staff_name'] = 'Empty Name not allowed';

				return $import_status;
			}
		} else {
			$staff_data = [
				'ID'         => '',
				'post_type'  => 'sl_staff',
				'meta_input' => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( ! empty( $row_data['staff_id'] ) ) {
				if ( 'sl_staff' === get_post_type( absint( $row_data['staff_id'] ) ) ) {
					$staff_data['ID'] = absint( $row_data['staff_id'] );
				}
			} elseif ( ! empty( $row_data['staff_external_id'] ) ) {
				$maybe_staff_id = sports_leagues()->staff->get_staff_id_by_external_id( $row_data['staff_external_id'] );

				if ( ! empty( $maybe_staff_id ) ) {
					$staff_data['ID'] = absint( $maybe_staff_id );
				}
			}

			if ( empty( $staff_data['ID'] ) ) {
				$import_status['post_title'] = 'Invalid Staff ID or External Staff ID';

				return $import_status;
			}
		}

		foreach ( $row_data as $slug => $value ) {

			if ( empty( $value ) || in_array( $slug, [ 'import_info', 'import_status' ], true ) ) {
				continue;
			}

			switch ( $slug ) {
				case 'staff_name':
					$staff_data['post_title'] = sanitize_text_field( $value );
					break;

				case 'short_name':
					$staff_data['meta_input']['_sl_short_name'] = sanitize_text_field( $value );
					break;

				case 'current_team':
					$staff_data['meta_input']['_sl_current_team'] = sanitize_text_field( $value );
					break;

				case 'job_title':
					$staff_data['meta_input']['_sl_job_title'] = sanitize_text_field( $value );
					break;

				case 'place_of_birth':
					$staff_data['meta_input']['_sl_place_of_birth'] = sanitize_text_field( $value );
					break;

				case 'date_of_birth':
					$staff_data['meta_input']['_sl_date_of_birth'] = sanitize_text_field( $value );
					break;

				case 'bio':
					$staff_data['meta_input']['_sl_bio'] = sanitize_textarea_field( $value );
					break;

				case 'position':
					$staff_data['meta_input']['_sl_position'] = sanitize_text_field( $value );
					break;

				case 'nationality_1':
				case 'nationality_2':
					$staff_data['meta_input']['_sl_nationality'][] = sanitize_text_field( $value );
					break;

				case 'staff_external_id':
					$staff_data['meta_input']['_sl_staff_external_id'] = sanitize_text_field( $value );
					break;

				default:
					if ( 0 === mb_strpos( $slug, 'cf__' ) ) {

						$maybe_custom_field = mb_substr( $slug, 4 );

						if ( ! empty( $maybe_custom_field ) ) {
							$custom_fields_data[ $maybe_custom_field ] = sanitize_text_field( $value );
						}
					}
			}
		}

		// Custom Fields
		if ( ! empty( $custom_fields_data ) ) {
			$custom_fields_old = get_post_meta( $staff_data['ID'], '_sl_custom_fields', true );

			if ( ! empty( $custom_fields_old ) && is_array( $custom_fields_old ) ) {
				$custom_fields_data = array_merge( $custom_fields_old, $custom_fields_data );
			}
		}

		if ( ! empty( $custom_fields_data ) ) {
			$staff_data['meta_input']['_sl_custom_fields'] = $custom_fields_data;
		}

		$post_id = $insert_mode ? wp_insert_post( $staff_data ) : wp_update_post( $staff_data );

		if ( absint( $post_id ) ) {
			$post_obj = get_post( $post_id );

			$import_status['result']     = 'success';
			$import_status['post_title'] = $post_obj->post_title;
			$import_status['post_url']   = get_permalink( $post_obj );
			$import_status['post_edit']  = get_edit_post_link( $post_obj );
		}

		return $import_status;
	}

	/**
	 * Import Official.
	 *
	 * @param $params
	 * @param $import_status
	 *
	 * @return array
	 * @since 0.10.3
	 */
	protected function import_official( $params, $import_status ) {

		$current_user_id = get_current_user_id();
		$current_time    = current_time( 'mysql' );

		$row_data    = $params['row_data'];
		$insert_mode = 'insert' === $params['mode'];

		$custom_fields_data = [];

		if ( $insert_mode ) {
			$official_data = [
				'post_title'   => '',
				'post_content' => '',
				'post_type'    => 'sl_official',
				'post_status'  => 'publish',
				'post_author'  => $current_user_id,
				'meta_input'   => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( empty( trim( $row_data['official_name'] ) ) ) {
				$import_status['post_title'] = 'Empty name not allowed';

				return $import_status;
			}
		} else {
			$official_data = [
				'ID'         => '',
				'post_type'  => 'sl_official',
				'meta_input' => [
					'_sl_import_time' => $current_time,
				],
			];

			if ( ! empty( $row_data['official_id'] ) ) {
				if ( 'sl_official' === get_post_type( absint( $row_data['official_id'] ) ) ) {
					$official_data['ID'] = absint( $row_data['official_id'] );
				}
			} elseif ( ! empty( $row_data['official_external_id'] ) ) {
				$maybe_official_id = sports_leagues()->official->get_official_id_by_external_id( $row_data['official_external_id'] );

				if ( ! empty( $maybe_official_id ) ) {
					$official_data['ID'] = absint( $maybe_official_id );
				}
			}

			if ( empty( $official_data['ID'] ) ) {
				$import_status['post_title'] = 'Invalid Official ID or External Official ID';

				return $import_status;
			}
		}

		foreach ( $row_data as $slug => $value ) {

			if ( empty( $value ) || in_array( $slug, [ 'import_info', 'import_status' ], true ) ) {
				continue;
			}

			switch ( $slug ) {
				case 'official_name':
					$official_data['post_title'] = sanitize_text_field( $value );
					break;

				case 'short_name':
					$official_data['meta_input']['_sl_short_name'] = sanitize_text_field( $value );
					break;

				case 'place_of_birth':
					$official_data['meta_input']['_sl_place_of_birth'] = sanitize_text_field( $value );
					break;

				case 'date_of_birth':
					$official_data['meta_input']['_sl_date_of_birth'] = sanitize_text_field( $value );
					break;

				case 'bio':
					$official_data['meta_input']['_sl_bio'] = sanitize_textarea_field( $value );
					break;

				case 'nationality_1':
				case 'nationality_2':
					$official_data['meta_input']['_sl_nationality'][] = sanitize_text_field( $value );
					break;

				case 'official_external_id':
					$official_data['meta_input']['_sl_official_external_id'] = sanitize_text_field( $value );
					break;

				default:
					if ( 0 === mb_strpos( $slug, 'cf__' ) ) {

						$maybe_custom_field = mb_substr( $slug, 4 );

						if ( ! empty( $maybe_custom_field ) ) {
							$custom_fields_data[ $maybe_custom_field ] = sanitize_text_field( $value );
						}
					}
			}
		}

		// Custom Fields
		if ( ! empty( $custom_fields_data ) ) {
			$custom_fields_old = get_post_meta( $official_data['ID'], '_sl_custom_fields', true );

			if ( ! empty( $custom_fields_old ) && is_array( $custom_fields_old ) ) {
				$custom_fields_data = array_merge( $custom_fields_old, $custom_fields_data );
			}
		}

		if ( ! empty( $custom_fields_data ) ) {
			$official_data['meta_input']['_sl_custom_fields'] = $custom_fields_data;
		}

		$post_id = $insert_mode ? wp_insert_post( $official_data ) : wp_update_post( $official_data );

		if ( absint( $post_id ) ) {
			$post_obj = get_post( $post_id );

			$import_status['result']     = 'success';
			$import_status['post_title'] = $post_obj->post_title;
			$import_status['post_url']   = get_permalink( $post_obj );
			$import_status['post_edit']  = get_edit_post_link( $post_obj );
		}

		return $import_status;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field Field to get.
	 *
	 * @return mixed         Value of the field.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @since  0.1.0
	 *
	 */
	public function __get( $field ) {

		if ( property_exists( $this, $field ) ) {
			return $this->$field;
		}

		throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
	}

	/**
	 * Validate datetime.
	 * From - https://secure.php.net/manual/en/function.checkdate.php#113205
	 *
	 * @param        $date
	 * @param string $format
	 *
	 * @return bool
	 * @since 0.1.0
	 */
	public function validate_date( $date, $format = 'Y-m-d H:i:s' ) {
		$d = DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) === $date;
	}

	/**
	 * Get filtered seasons
	 *
	 * @param string $type
	 * @param int    $id
	 *
	 * @return array
	 * @since 0.11.0
	 */
	public function get_filtered_seasons( $type, $id ) {

		static $options = [
			'player'   => [],
			'staff'    => [],
			'team'     => [],
			'venue'    => [],
			'official' => [],
		];

		// Validate data
		if ( ! in_array( $type, [ 'team', 'staff', 'official', 'venue', 'player' ], true ) ) {
			return [];
		}

		// Return cached
		if ( ! empty( $options[ $type ][ absint( $id ) ] ) ) {
			return $options[ $type ][ absint( $id ) ];
		}

		global $wpdb;

		if ( 'player' === $type ) {
			$options[ $type ][ absint( $id ) ] = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT DISTINCT t.slug
					FROM {$wpdb->prefix}sl_player_statistics a
					LEFT JOIN {$wpdb->prefix}sl_games b ON a.game_id = b.game_id
					LEFT JOIN {$wpdb->terms} t ON t.term_id = b.season_id
					WHERE a.c_id__0 = 1 AND a.player_id = %d
					",
					$id
				)
			);
		} elseif ( 'team' === $type ) {
			$options[ $type ][ absint( $id ) ] = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT DISTINCT t.slug
					FROM {$wpdb->prefix}sl_games a
					LEFT JOIN {$wpdb->terms} t ON t.term_id = a.season_id
					WHERE a.home_team = %d OR a.away_team = %d
					",
					$id,
					$id
				)
			);

			/*
			|--------------------------------------------------------------------
			| Get club squad slugs
			|--------------------------------------------------------------------
			*/
			$squad_season_ids = sports_leagues()->team->get_team_squad_season_ids( $id );

			if ( ! empty( $squad_season_ids ) ) {
				foreach ( $squad_season_ids as $squad_season_id ) {
					$squad_season_slug = sports_leagues()->season->get_season_slug_by_id( $squad_season_id );

					if ( $squad_season_slug && ! in_array( $squad_season_slug, $options[ $type ][ absint( $id ) ], true ) ) {
						$options[ $type ][ absint( $id ) ][] = $squad_season_slug;
					}
				}
			}
		} elseif ( 'venue' === $type ) {
			$options[ $type ][ absint( $id ) ] = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT DISTINCT t.slug
					FROM {$wpdb->prefix}sl_games a
					LEFT JOIN {$wpdb->terms} t ON t.term_id = a.season_id
					WHERE a.venue_id = %d
					",
					$id
				)
			);
		} elseif ( 'official' === $type ) {

			$query = "
					SELECT DISTINCT t.slug
					FROM {$wpdb->prefix}sl_games g
					LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = g.game_id AND pm.meta_key = '_sl_officials' )
					LEFT JOIN {$wpdb->terms} t ON t.term_id = g.season_id
			";

			$query .= ' WHERE ( pm.meta_value LIKE "' . absint( $id ) . '" OR pm.meta_value LIKE "' . absint( $id ) . ',%" OR pm.meta_value LIKE "%,' . absint( $id ) . '" OR pm.meta_value LIKE "%,' . absint( $id ) . ',%" ) ';

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$options[ $type ][ absint( $id ) ] = $wpdb->get_col( $query );
		} elseif ( 'staff' === $type ) {

			$query = "
					SELECT DISTINCT t.slug
					FROM {$wpdb->prefix}sl_games g
					LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = g.game_id AND pm1.meta_key = '_sl_staff_home' )
					LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = g.game_id AND pm2.meta_key = '_sl_staff_away' )
					LEFT JOIN {$wpdb->terms} t ON t.term_id = g.season_id
			";

			$query .= ' WHERE ( pm1.meta_value LIKE "' . absint( $id ) . '" OR pm1.meta_value LIKE "' . absint( $id ) . ',%" OR pm1.meta_value LIKE "%,' . absint( $id ) . '" OR pm1.meta_value LIKE "%,' . absint( $id ) . ',%" ';
			$query .= ' OR      pm2.meta_value LIKE "' . absint( $id ) . '" OR pm2.meta_value LIKE "' . absint( $id ) . ',%" OR pm2.meta_value LIKE "%,' . absint( $id ) . '" OR pm2.meta_value LIKE "%,' . absint( $id ) . ',%" ) ';

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$options[ $type ][ absint( $id ) ] = $wpdb->get_col( $query );
		}

		return empty( $options[ $type ][ absint( $id ) ] ) ? [] : $options[ $type ][ absint( $id ) ];
	}

	public function season_dropdown( $season_id, $echo = true, $class = '', $filter = [] ) {

		// Get all season options
		$season_options = sports_leagues()->season->get_season_slug_options();

		/*
		|--------------------------------------------------------------------
		| Hide dropdown when number of seasons < 2
		|--------------------------------------------------------------------
		*/
		if ( count( $season_options ) < 2 ) {
			return '';
		}

		/*
		|--------------------------------------------------------------------
		| Get filtered season options
		|--------------------------------------------------------------------
		*/
		if ( 'yes' === Sports_Leagues_Options::get_value( 'hide_not_used_seasons' ) && ! empty( $filter['context'] ) && absint( $filter['id'] ) ) {

			$season_slugs     = $this->get_filtered_seasons( $filter['context'], absint( $filter['id'] ) );
			$filtered_options = [];

			foreach ( $season_options as $option ) {
				if ( in_array( $option['slug'], $season_slugs, true ) ) {
					$filtered_options[] = $option;
				}
			}

			$season_options = $filtered_options;

			if ( in_array( $filter['context'], [ 'team', 'staff', 'official', 'venue', 'player' ], true ) ) {
				$active_season_id = sports_leagues()->get_active_instance_season( $filter['id'], $filter['context'] );
			}
		}

		if ( empty( $season_options ) ) {
			return '';
		}

		/*
		|--------------------------------------------------------------------
		| Prepare season data
		|--------------------------------------------------------------------
		*/
		if ( empty( $active_season_id ) ) {
			$active_season_id = sports_leagues()->get_active_season();
		}

		$active_season_slug = sports_leagues()->season->get_season_slug_by_id( $active_season_id );
		$selected_season    = intval( $season_id ) === $active_season_id ? $active_season_slug : sports_leagues()->season->get_season_slug_by_id( $season_id );

		ob_start();
		?>
		<select class="anwp-season-dropdown w-auto <?php echo esc_attr( $class ); ?>">
			<?php
			foreach ( $season_options as $s ) :
				$data_url = $s['slug'] === $active_season_slug ? remove_query_arg( 'season' ) : add_query_arg( 'season', $s['slug'] );
				?>
				<option <?php selected( $s['slug'], $selected_season ); ?> data-href="<?php echo esc_url( $data_url ); ?>" value="<?php echo esc_attr( $s['slug'] ); ?>"><?php echo esc_attr( $s['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php

		$output = ob_get_clean();

		/**
		 * Filter season dropdown output.
		 *
		 * @param string $output
		 * @param int    $season_id
		 *
		 * @since 0.11.0
		 */
		$output = apply_filters( 'sports-leagues/layout/season_dropdown', $output, $season_id );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $output;
		}

		return $output;
	}

	/**
	 * Get default player photo.
	 *
	 * @return string
	 * @since 0.1.0
	 */
	public function get_default_player_photo() {

		// Get photo from plugin options
		$photo = Sports_Leagues_Customizer::get_value( 'player', 'default_player_photo' );

		if ( ! $photo ) {
			$photo = Sports_Leagues::url( 'public/img/empty_player.png' );
		}

		return $photo;
	}

	/**
	 * Callback for the rest route "/helper/recalculate-index-tables/"
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 * @since 0.5.3
	 */
	public function recalculate_index_tables( WP_REST_Request $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Access Denied !!!' );
		}

		global $wpdb;

		$params = $request->get_query_params();
		$option = empty( $params['option'] ) ? 0 : intval( $params['option'] );

		$rows_affected = 0;

		if ( empty( $option ) ) {

			// Reset games statistics
			$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . 'sl_games' );

			// Get all fixed games
			$games = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_game',
					'post_status' => 'publish',
				]
			);

			$wpdb->query( 'SET autocommit = 0;' );

			foreach ( $games as $game ) {

				if ( 'yes' === get_post_meta( $game->ID, '_sl_fixed', true ) ) {

					// Prepare data
					$data = [
						'game_id'       => $game->ID,
						'tournament_id' => get_post_meta( $game->ID, '_sl_tournament_id', true ),
						'stage_id'      => get_post_meta( $game->ID, '_sl_stage_id', true ),
						'league_id'     => get_post_meta( $game->ID, '_sl_league_id', true ),
						'season_id'     => get_post_meta( $game->ID, '_sl_season_id', true ),
						'group_id'      => get_post_meta( $game->ID, '_sl_group_id', true ),
						'round_id'      => get_post_meta( $game->ID, '_sl_group_id', true ),
						'team_home'     => get_post_meta( $game->ID, '_sl_team_home', true ),
						'team_away'     => get_post_meta( $game->ID, '_sl_team_away', true ),
						'datetime'      => get_post_meta( $game->ID, '_sl_datetime', true ) ?: '0000-00-00 00:00:00',
						'venue_id'      => get_post_meta( $game->ID, '_sl_venue_id', true ),
						'gameday'       => get_post_meta( $game->ID, '_sl_gameday', true ),
						'priority'      => get_post_meta( $game->ID, '_sl_game_priority', true ),
						'status'        => get_post_meta( $game->ID, '_sl_status', true ),

					];

					$data['stage_status'] = intval( $data['stage_id'] ) ? get_post_meta( $data['stage_id'], '_sl_stage_status', true ) : '';

					/*
					|--------------------------------------------------------------------
					| Complex fields
					|--------------------------------------------------------------------
					*/
					// Get scores data
					$home_scores_json = json_decode( get_post_meta( $game->ID, '_sl_scores_home', true ) );
					$away_scores_json = json_decode( get_post_meta( $game->ID, '_sl_scores_away', true ) );

					// Validate
					$home_scores = empty( $home_scores_json ) ? (object) [] : $home_scores_json;
					$away_scores = empty( $away_scores_json ) ? (object) [] : $away_scores_json;

					// Fetch data
					$data['home_scores'] = isset( $home_scores->final ) ? sanitize_text_field( $home_scores->final ) : '';
					$data['away_scores'] = isset( $away_scores->final ) ? sanitize_text_field( $away_scores->final ) : '';

					$data['home_outcome'] = empty( $home_scores->outcome ) ? '' : sanitize_key( $home_scores->outcome );
					$data['away_outcome'] = empty( $away_scores->outcome ) ? '' : sanitize_key( $away_scores->outcome );

					$data['home_pts'] = empty( $home_scores->pts ) ? 0 : intval( $home_scores->pts );
					$data['away_pts'] = empty( $away_scores->pts ) ? 0 : intval( $away_scores->pts );

					$data['home_bpts'] = empty( $home_scores->bpts ) ? 0 : intval( $home_scores->bpts );
					$data['away_bpts'] = empty( $away_scores->bpts ) ? 0 : intval( $away_scores->bpts );

					$rows_affected += (int) $this->plugin->game->save_game_statistics( $data );
				}
			}

			$wpdb->query( 'COMMIT;' );
			$wpdb->query( 'SET autocommit = 1;' );
		} elseif ( $option < 0 ) {

			$option = absint( $option );

			$games_ids = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_game',
					'post_status' => 'publish',
					'fields'      => 'ids',
				]
			);

			$stats_ids = $wpdb->get_col(
				"
				SELECT game_id
				FROM {$wpdb->prefix}sl_games
				"
			);

			$stats_ids = array_map( 'intval', $stats_ids );

			$ids = array_diff( $games_ids, $stats_ids );
			$ids = array_slice( $ids, 0, $option );

			// Get all fixed games
			$games = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_game',
					'post_status' => 'publish',
					'include'     => $ids,
				]
			);

			foreach ( $games as $game ) {

				if ( 'yes' === get_post_meta( $game->ID, '_sl_fixed', true ) ) {
					try {

						$data = [
							'game_id'       => $game->ID,
							'tournament_id' => get_post_meta( $game->ID, '_sl_tournament_id', true ),
							'stage_id'      => get_post_meta( $game->ID, '_sl_stage_id', true ),
							'league_id'     => get_post_meta( $game->ID, '_sl_league_id', true ),
							'season_id'     => get_post_meta( $game->ID, '_sl_season_id', true ),
							'group_id'      => get_post_meta( $game->ID, '_sl_group_id', true ),
							'round_id'      => get_post_meta( $game->ID, '_sl_group_id', true ),
							'team_home'     => get_post_meta( $game->ID, '_sl_team_home', true ),
							'team_away'     => get_post_meta( $game->ID, '_sl_team_away', true ),
							'datetime'      => get_post_meta( $game->ID, '_sl_datetime', true ) ?: '0000-00-00 00:00:00',
							'venue_id'      => get_post_meta( $game->ID, '_sl_venue_id', true ),
							'gameday'       => get_post_meta( $game->ID, '_sl_gameday', true ),
							'priority'      => get_post_meta( $game->ID, '_sl_game_priority', true ),
							'status'        => get_post_meta( $game->ID, '_sl_status', true ),

						];

						$data['stage_status'] = intval( $data['stage_id'] ) ? get_post_meta( $data['stage_id'], '_sl_stage_status', true ) : '';

						/*
						|--------------------------------------------------------------------
						| Complex fields
						|--------------------------------------------------------------------
						*/
						// Get scores data
						$home_scores_json = json_decode( get_post_meta( $game->ID, '_sl_scores_home', true ) );
						$away_scores_json = json_decode( get_post_meta( $game->ID, '_sl_scores_away', true ) );

						// Validate
						$home_scores = empty( $home_scores_json ) ? (object) [] : $home_scores_json;
						$away_scores = empty( $away_scores_json ) ? (object) [] : $away_scores_json;

						// Fetch data
						$data['home_scores'] = isset( $home_scores->final ) ? sanitize_text_field( $home_scores->final ) : '';
						$data['away_scores'] = isset( $away_scores->final ) ? sanitize_text_field( $away_scores->final ) : '';

						$data['home_outcome'] = empty( $home_scores->outcome ) ? '' : sanitize_key( $home_scores->outcome );
						$data['away_outcome'] = empty( $away_scores->outcome ) ? '' : sanitize_key( $away_scores->outcome );

						$data['home_pts'] = empty( $home_scores->pts ) ? 0 : intval( $home_scores->pts );
						$data['away_pts'] = empty( $away_scores->pts ) ? 0 : intval( $away_scores->pts );

						$data['home_bpts'] = empty( $home_scores->bpts ) ? 0 : intval( $home_scores->bpts );
						$data['away_bpts'] = empty( $away_scores->bpts ) ? 0 : intval( $away_scores->bpts );

						$rows_affected += (int) $this->plugin->game->save_game_statistics( $data );

					} catch ( RuntimeException $e ) {
						continue;
					}
				}
			}
		}

		return $rows_affected;
	}

	/**
	 * Renders country flag by players nationality.
	 *
	 * @param array $codes
	 *
	 * @return string
	 * @since  0.5.3
	 */
	public function render_player_flag( $codes ) {

		$output = '';

		if ( empty( $codes ) || ! is_array( $codes ) ) {
			return $output;
		}

		ob_start();
		foreach ( $codes as $code ) :
			?>
			<div class="f16" data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( sports_leagues()->data->get_country_by_code( $code ) ); ?>">
				<span class="flag <?php echo esc_attr( $code ); ?>"></span>
			</div>
			<?php
		endforeach;

		return ob_get_clean();
	}

	/**
	 * Renders team header block.
	 *
	 * @param string $team_logo
	 * @param string $team_title
	 * @param int    $team_id
	 * @param bool   $is_home
	 *
	 * @return string
	 * @since  0.5.3
	 */
	public function render_team_header( $team_logo, $team_title, $team_id, $is_home = true ) {

		if ( empty( $team_title ) && empty( $team_logo ) ) {
			return '';
		}

		$team_color = sports_leagues()->team->get_team_color( $team_id, $is_home );

		ob_start();

		sports_leagues()->load_partial(
			[
				'team_logo'       => $team_logo,
				'team_title'      => $team_title,
				'is_home'         => $is_home,
				'bg_color'        => $this->hex2rgba( $team_color, 0.1 ),
				'team_color'      => $team_color,
				'colorize_header' => 'no' !== Sports_Leagues_Customizer::get_value( 'game', 'colorize_team_header' ),
			],
			'team/team-subheader'
		);

		return ob_get_clean();
	}

	/**
	 * Converts HEX to RGBA
	 *
	 * @param      $color
	 * @param int  $opacity
	 *
	 * @return string
	 * @since 0.5.3
	 */
	public function hex2rgba( $color, $opacity ) {

		$default = 'rgb(0,0,0)';

		if ( empty( $color ) ) {
			return $default;
		}

		if ( '#' === mb_substr( $color, 0, 1 ) ) {
			$color = mb_substr( $color, 1 );
		}

		if ( 6 === strlen( $color ) ) {
			$hex = [ $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] ];
		} elseif ( 3 === strlen( $color ) ) {
			$hex = [ $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] ];
		} else {
			return $default;
		}

		$rgb = array_map( 'hexdec', $hex );

		return $opacity < 1 ? 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')' : 'rgb(' . implode( ',', $rgb ) . ')';
	}

	/**
	 * Method returns outcome data.
	 *
	 * @return array
	 * @since 0.5.7
	 */
	public function get_outcome_options() {
		$options = [
			'ft_win'   => [
				'title'  => esc_html__( 'Full-time win', 'sports-leagues' ),
				'points' => Sports_Leagues_Config::get_value( 'points_ft_win', '' ),
				'opp'    => 'ft_loss',
			],
			'ov_win'   => [
				'title'  => esc_html__( 'Overtime win', 'sports-leagues' ),
				'points' => Sports_Leagues_Config::get_value( 'points_ov_win', '' ),
				'opp'    => 'ov_loss',
			],
			'pen_win'  => [
				'title'  => esc_html__( 'Penalty shootout win', 'sports-leagues' ),
				'points' => Sports_Leagues_Config::get_value( 'points_pen_win', '' ),
				'opp'    => 'pen_loss',
			],
			'draw'     => [
				'title'  => esc_html__( 'Draw', 'sports-leagues' ),
				'points' => Sports_Leagues_Config::get_value( 'points_draw', '' ),
				'opp'    => 'draw',
			],
			'pen_loss' => [
				'title'  => esc_html__( 'Penalty shootout loss', 'sports-leagues' ),
				'points' => Sports_Leagues_Config::get_value( 'points_pen_loss', '' ),
				'opp'    => 'pen_win',
			],
			'ov_loss'  => [
				'title'  => esc_html__( 'Overtime loss', 'sports-leagues' ),
				'points' => Sports_Leagues_Config::get_value( 'points_ov_loss', '' ),
				'opp'    => 'ov_win',
			],
			'ft_loss'  => [
				'title'  => esc_html__( 'Full-time loss', 'sports-leagues' ),
				'points' => Sports_Leagues_Config::get_value( 'points_ft_loss', '' ),
				'opp'    => 'ft_win',
			],
		];

		return $options;
	}

	/**
	 * Method returns outcome title by slug.
	 *
	 * @param string $slug
	 *
	 * @return string
	 * @since 0.5.7
	 */
	public function get_outcome_option_by_slug( $slug ) {

		$title = '';

		$options = $this->get_outcome_options();

		if ( ! empty( $options[ $slug ] ) && ! empty( ! empty( $options[ $slug ]['title'] ) ) ) {
			$title = $options[ $slug ]['title'];
		}

		return $title;
	}

	/**
	 * Rendering team form.
	 *
	 * @param array $args
	 *
	 * @return string
	 * @since 0.5.12
	 */
	public function get_team_form( $args ) {

		$args = (object) wp_parse_args(
			$args,
			[
				'team_id'        => '',
				'standing_id'    => '',
				'kickoff_before' => '',
				'date_to'        => '',
				'echo'           => true,
			]
		);

		if ( empty( $args->team_id ) ) {
			return '';
		}

		$series_map = [
			'w' => Sports_Leagues_Config::get_value( 'team_series_w', 'w' ),
			'd' => Sports_Leagues_Config::get_value( 'team_series_d', 'd' ),
			'l' => Sports_Leagues_Config::get_value( 'team_series_l', 'l' ),
		];

		$games_options = [
			'filter_by_team' => $args->team_id,
			'kickoff_before' => $args->kickoff_before,
			'finished'       => 1,
			'limit'          => 5,
			'sort_by_date'   => 'desc',
			'class'          => '',
		];

		if ( ! empty( $args->date_to ) ) {
			$games_options['date_to'] = $args->date_to;
		}

		if ( ! empty( $args->standing_id ) && absint( $args->standing_id ) ) {
			$games_options['group_id'] = get_post_meta( absint( $args->standing_id ), '_sl_group_id', true );
			$games_options['stage_id'] = get_post_meta( absint( $args->standing_id ), '_sl_stage_id', true );
		}

		// Get latest games
		$games = sports_leagues()->game->get_games_extended( $games_options );

		if ( ! empty( $games ) && is_array( $games ) ) {
			$games = array_reverse( $games );
		}

		ob_start();
		?>
		<div class="club-form">
			<?php
			foreach ( $games as $game ) :

				$outcome = absint( $args->team_id ) === absint( $game->home_team ) ? $game->home_outcome : $game->away_outcome;

				if ( empty( $outcome ) ) {
					continue;
				}

				switch ( $outcome ) {
					case 'draw':
						$outcome_label = $series_map['d'];
						$outcome_class = 'anwp-bg-warning';
						break;

					case 'pen_loss':
					case 'ov_loss':
					case 'ft_loss':
						$outcome_label = $series_map['l'];
						$outcome_class = 'anwp-bg-danger';
						break;

					default:
						$outcome_label = $series_map['w'];
						$outcome_class = 'anwp-bg-success';
				}
				?>
				<span data-anwp-sl-game-card-tooltip data-game-id="<?php echo absint( $game->game_id ); ?>"
						class="my-1 d-inline-block team-form__item px-2 text-white text-uppercase anwp-cursor-pointer text-monospace <?php echo esc_attr( $outcome_class ); ?>">
					<?php echo esc_html( $outcome_label ); ?>
				</span>
			<?php endforeach; ?>
		</div>
		<?php
		$output = ob_get_clean();

		if ( $args->echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $output;
		}

		return $output;
	}

	/**
	 * Get teams form. Used for Standing table.
	 *
	 * @param array $ids
	 * @param int   $standing_id
	 *
	 * @return array
	 * @since 0.5.13
	 */
	public function get_teams_form( $ids, $standing_id ) {

		$ids = array_map( 'absint', $ids );

		if ( empty( $ids ) ) {
			return [];
		}

		$series_map = [
			'w' => Sports_Leagues_Config::get_value( 'team_series_w', 'w' ),
			'd' => Sports_Leagues_Config::get_value( 'team_series_d', 'd' ),
			'l' => Sports_Leagues_Config::get_value( 'team_series_l', 'l' ),
		];

		$games_options = [
			'filter_by_team' => $ids,
			'finished'       => 1,
			'sort_by_date'   => 'desc',
			'class'          => '',
		];

		if ( ! empty( $standing_id ) && absint( $standing_id ) ) {
			$games_options['group_id'] = get_post_meta( absint( $standing_id ), '_sl_group_id', true );
			$games_options['stage_id'] = get_post_meta( absint( $standing_id ), '_sl_stage_id', true );
		}

		// Get latest games
		$games = sports_leagues()->game->get_games_extended( $games_options );

		$team_data = array_fill_keys(
			$ids,
			[
				'games' => [],
				'html'  => '',
			]
		);

		foreach ( $games as $game ) {
			if ( isset( $team_data[ $game->home_team ] ) ) {
				$team_data[ $game->home_team ]['games'][] = $game;
			}

			if ( isset( $team_data[ $game->away_team ] ) ) {
				$team_data[ $game->away_team ]['games'][] = $game;
			}
		}

		foreach ( $team_data as $team_id => $team ) {
			$team['games'] = array_slice( $team['games'], 0, 5 );

			if ( ! empty( $team['games'] ) && is_array( $team['games'] ) ) {
				$team['games'] = array_reverse( $team['games'] );
			}

			ob_start();
			?>
			<div class="club-form">
				<?php
				foreach ( $team['games'] as $game ) :

					$outcome = absint( $team_id ) === absint( $game->home_team ) ? $game->home_outcome : $game->away_outcome;

					if ( empty( $outcome ) ) {
						continue;
					}

					switch ( $outcome ) {
						case 'draw':
							$outcome_label = $series_map['d'];
							$outcome_class = 'anwp-bg-warning';
							break;

						case 'pen_loss':
						case 'ov_loss':
						case 'ft_loss':
							$outcome_label = $series_map['l'];
							$outcome_class = 'anwp-bg-danger';
							break;

						default:
							$outcome_label = $series_map['w'];
							$outcome_class = 'anwp-bg-success';
					}
					?>
					<span data-anwp-sl-game-card-tooltip data-game-id="<?php echo absint( $game->game_id ); ?>"
							class="my-1 d-inline-block team-form__item px-2 text-white text-uppercase text-monospace <?php echo esc_attr( $outcome_class ); ?>">
					<?php echo esc_html( $outcome_label ); ?>
				</span>
				<?php endforeach; ?>
			</div>
			<?php
			$team_data[ $team_id ]['html'] = ob_get_clean();
		}

		return wp_list_pluck( $team_data, 'html' );
	}

	/**
	 * Get post thumb urls.
	 *
	 * @param $thumb_ids
	 *
	 * @return array
	 * @since 0.5.13
	 */
	public function get_thumb_urls_by_post_ids( $thumb_ids ) {
		global $wpdb;

		if ( empty( $thumb_ids ) ) {
			return [];
		}

		$thumb_ids = array_map( 'absint', $thumb_ids );

		$query_string = '"' . implode( '","', $thumb_ids ) . '"';

		$items = $wpdb->get_results(
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"
				SELECT post_id, meta_value
				FROM $wpdb->postmeta
				WHERE meta_key = '_wp_attached_file'
					AND post_id IN ($query_string)
			",
			OBJECT_K
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return $items;
	}

	/**
	 * Get Instance Selector Data
	 *
	 * @since 0.9.0
	 */
	public function get_selector_data() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax_anwpsl_nonce' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Get POST search data
		$search_data = wp_parse_args(
			isset( $_POST['search_data'] ) ? $this->recursive_sanitize( $_POST['search_data'] ) : [],
			[
				'context' => '',
				's'       => '',
				'team'    => '',
				'country' => '',
				'team_a'  => '',
				'team_b'  => '',
				'season'  => '',
				'league'  => '',
			]
		);

		if ( ! in_array( $search_data['context'], [ 'player', 'team', 'game', 'tournament', 'season', 'stage' ], true ) ) {
			wp_send_json_error();
		}

		$html_output = '';

		switch ( $search_data['context'] ) {
			case 'player':
				$html_output = $this->get_selector_player_data( $search_data );
				break;

			case 'team':
				$html_output = $this->get_selector_team_data( $search_data );
				break;

			case 'game':
				$html_output = $this->get_selector_game_data( $search_data );
				break;

			case 'tournament':
				$html_output = $this->get_selector_tournament_data( $search_data );
				break;

			case 'stage':
				$html_output = $this->get_selector_stage_data( $search_data );
				break;

			case 'season':
				$html_output = $this->get_selector_season_data( $search_data );
				break;
		}

		wp_send_json_success( [ 'html' => $html_output ] );
	}

	/**
	 * Get Instance Selector Data
	 *
	 * @since 0.9.0
	 */
	public function get_selector_initial() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax_anwpsl_nonce' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Get context
		$data_context = isset( $_POST['data_context'] ) ? sanitize_text_field( $_POST['data_context'] ) : '';

		if ( ! in_array( $data_context, [ 'player', 'team', 'game', 'tournament', 'season', 'stage' ], true ) ) {
			wp_send_json_error();
		}

		// Initial
		$data_initial = isset( $_POST['initial'] ) ? wp_parse_id_list( $_POST['initial'] ) : [];

		if ( empty( $data_initial ) ) {
			wp_send_json_error();
		}

		$output = '';

		switch ( $data_context ) {
			case 'player':
				$output = $this->get_selector_player_initial( $data_initial );
				break;

			case 'team':
				$output = $this->get_selector_team_initial( $data_initial );
				break;

			case 'game':
				$output = $this->get_selector_game_initial( $data_initial );
				break;

			case 'tournament':
				$output = $this->get_selector_tournament_initial( $data_initial );
				break;

			case 'stage':
				$output = $this->get_selector_stage_initial( $data_initial );
				break;

			case 'season':
				$output = $this->get_selector_season_initial( $data_initial );
				break;
		}

		wp_send_json_success( [ 'items' => $output ] );
	}

	/**
	 * Get selector player initial data.
	 *
	 * @param array $data_initial
	 *
	 * @return array
	 * @since 0.9.0
	 */
	private function get_selector_player_initial( $data_initial ) {

		$query_args = [
			'post_type'               => [ 'sl_player' ],
			'posts_per_page'          => 30,
			'include'                 => $data_initial,
			'cache_results'           => false,
			'update_post_meta_cache'  => false,
			'update_post_term_cache ' => false,
		];

		$results = get_posts( $query_args );

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		$output = [];

		foreach ( $results as $result_item ) {
			$output[] = [
				'id'   => $result_item->ID,
				'name' => $result_item->post_title,
			];
		}

		return $output;
	}

	/**
	 * Get selector team initial data.
	 *
	 * @param array $data_initial
	 *
	 * @return array
	 * @since 0.9.0
	 */
	private function get_selector_team_initial( $data_initial ) {

		$query_args = [
			'post_type'               => [ 'sl_team' ],
			'posts_per_page'          => 50,
			'include'                 => $data_initial,
			'cache_results'           => false,
			'update_post_meta_cache'  => false,
			'update_post_term_cache ' => false,
		];

		$results = get_posts( $query_args );

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		$output = [];

		foreach ( $results as $result_item ) {
			$output[] = [
				'id'   => $result_item->ID,
				'name' => $result_item->post_title,
			];
		}

		return $output;
	}

	/**
	 * Get selector player data.
	 *
	 * @param array $search_data
	 *
	 * @return false|string
	 * @since 0.9.0
	 */
	private function get_selector_player_data( $search_data ) {

		$query_args = [
			'post_type'      => [ 'sl_player' ],
			'posts_per_page' => 30,
			's'              => $search_data['s'],
		];

		$meta_query = [];

		if ( ! empty( $search_data['team'] ) && absint( $search_data['team'] ) ) {
			$meta_query[] = [
				'key'   => '_sl_current_team',
				'value' => absint( $search_data['team'] ),
			];
		}

		if ( ! empty( $search_data['country'] ) ) {
			$meta_query[] = [
				'key'     => '_sl_nationality',
				'value'   => '"' . sanitize_text_field( $search_data['country'] ) . '"',
				'compare' => 'LIKE',
			];
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$results = get_posts( $query_args );

		ob_start();

		if ( ! empty( $results ) ) :
			?>
			<table class="wp-list-table widefat striped table-view-list">
				<thead>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Player Name', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Date of Birth', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Team', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Country', 'sports-leagues' ); ?></td>
				</tr>
				</thead>

				<tbody>
				<?php foreach ( $results as $player ) : ?>
					<tr data-id="<?php echo absint( $player->ID ); ?>" data-name="<?php echo esc_html( $player->post_title ); ?>">
						<td>
							<button type="button" class="button button-small anwp-sl-selector-action">
								<span class="dashicons dashicons-plus"></span>
							</button>
						</td>
						<td><?php echo esc_html( $player->post_title ); ?></td>
						<td><?php echo esc_html( get_post_meta( $player->ID, '_sl_date_of_birth', true ) ); ?></td>
						<td>
							<?php
							$team_id       = (int) get_post_meta( $player->ID, '_sl_current_team', true );
							$teams_options = $this->plugin->team->get_team_options();

							if ( ! empty( $teams_options[ $team_id ] ) ) {
								echo esc_html( $teams_options[ $team_id ] );
							}
							?>
						</td>
						<td style="text-transform: uppercase;">
							<?php
							$nationality = maybe_unserialize( get_post_meta( $player->ID, '_sl_nationality', true ) );

							if ( ! empty( $nationality ) && is_array( $nationality ) ) {
								echo esc_html( implode( ', ', $nationality ) );
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>

				<tfoot>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Player Name', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Date of Birth', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Team', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Country', 'sports-leagues' ); ?></td>
				</tr>
				</tfoot>
			</table>
		<?php else : ?>
			<div class="anwp-alert-warning">- <?php echo esc_html__( 'nothing found', 'sports-leagues' ); ?> -</div>
			<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Get selector game data.
	 *
	 * @param array $search_data
	 *
	 * @return false|string
	 * @since 0.9.2
	 */
	private function get_selector_game_data( $search_data ) {

		$args = [
			'season_id'    => absint( $search_data['season'] ) ?: '',
			'team_a'       => absint( $search_data['team_a'] ),
			'team_b'       => absint( $search_data['team_b'] ),
			'sort_by_date' => 'asc',
			'limit'        => 50,
		];

		$games = sports_leagues()->game->get_games_extended( $args, 'stats' );

		ob_start();

		if ( ! empty( $games ) ) :
			?>
			<table class="wp-list-table widefat striped table-view-list">
				<thead>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Team A', 'sports-leagues' ); ?></td>
					<td class="manage-column"><?php echo esc_html__( 'Team B', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Date', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Scores', 'sports-leagues' ); ?></td>
				</tr>
				</thead>

				<tbody>
				<?php
				foreach ( $games as $game ) :

					$team_a_title = sports_leagues()->team->get_team_by_id( $game->home_team )->title;
					$team_b_title = sports_leagues()->team->get_team_by_id( $game->away_team )->title;
					$game_scores = absint( $game->finished ) ? ( $game->home_scores . ':' . $game->away_scores ) : '?:?';
					$game_date = explode( ' ', $game->kickoff )[0];
					$game_title = $team_a_title . ' - ' . $team_b_title . ' - ' . $game_date . ' - ' . $game_scores;

					?>
					<tr data-id="<?php echo absint( $game->game_id ); ?>" data-name="<?php echo esc_html( $game_title ); ?>">
						<td>
							<button type="button" class="button button-small anwp-sl-selector-action">
								<span class="dashicons dashicons-plus"></span>
							</button>
						</td>
						<td><?php echo esc_html( $team_a_title ); ?></td>
						<td><?php echo esc_html( $team_b_title ); ?></td>
						<td><?php echo esc_html( $game_date ); ?></td>
						<td><?php echo esc_html( $game_scores ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>

				<tfoot>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Team A', 'sports-leagues' ); ?></td>
					<td class="manage-column"><?php echo esc_html__( 'Team B', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Date', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Scores', 'sports-leagues' ); ?></td>
				</tr>
				</tfoot>
			</table>
		<?php else : ?>
			<div class="anwp-alert-warning">- <?php echo esc_html__( 'nothing found', 'sports-leagues' ); ?> -</div>
		<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Get selector game initial data.
	 *
	 * @param array $data_initial
	 *
	 * @return array
	 * @since 0.9.2
	 */
	private function get_selector_game_initial( $data_initial ) {

		if ( empty( $data_initial ) || ! is_array( $data_initial ) ) {
			return [];
		}

		$args = [
			'include_ids'  => implode( ',', $data_initial ),
			'sort_by_date' => 'asc',
		];

		$games = sports_leagues()->game->get_games_extended( $args, 'stats' );

		if ( empty( $games ) || ! is_array( $games ) ) {
			return [];
		}

		$output = [];

		foreach ( $games as $game ) {

			$team_a_title = sports_leagues()->team->get_team_by_id( $game->home_team )->title;
			$team_b_title = sports_leagues()->team->get_team_by_id( $game->away_team )->title;
			$game_scores  = absint( $game->finished ) ? ( $game->home_scores . ':' . $game->away_scores ) : '?:?';
			$game_date    = explode( ' ', $game->kickoff )[0];

			$output[] = [
				'id'   => $game->game_id,
				'name' => $team_a_title . ' - ' . $team_b_title . ' - ' . $game_date . ' - ' . $game_scores,
			];
		}

		return $output;
	}

	/**
	 * Get selector team data.
	 *
	 * @param array $search_data
	 *
	 * @return false|string
	 * @since 0.9.0
	 */
	private function get_selector_team_data( $search_data ) {

		$query_args = [
			'post_type'      => [ 'sl_team' ],
			'posts_per_page' => 30,
			's'              => $search_data['s'],
		];

		$meta_query = [];

		if ( ! empty( $search_data['country'] ) ) {
			$meta_query[] = [
				'key'   => '_sl_nationality',
				'value' => sanitize_text_field( $search_data['country'] ),
			];
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$results = get_posts( $query_args );

		ob_start();

		if ( ! empty( $results ) ) :
			?>
			<table class="wp-list-table widefat striped table-view-list">
				<thead>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Team Title', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'City', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Country', 'sports-leagues' ); ?></td>
				</tr>
				</thead>

				<tbody>
				<?php foreach ( $results as $team ) : ?>
					<tr data-id="<?php echo absint( $team->ID ); ?>" data-name="<?php echo esc_html( $team->post_title ); ?>">
						<td>
							<button type="button" class="button button-small anwp-sl-selector-action">
								<span class="dashicons dashicons-plus"></span>
							</button>
						</td>
						<td><?php echo esc_html( $team->post_title ); ?></td>
						<td>
							<?php echo esc_html( get_post_meta( $team->ID, '_sl_city', true ) ); ?>
						</td>
						<td style="text-transform: uppercase;">
							<?php echo esc_html( get_post_meta( $team->ID, '_sl_nationality', true ) ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>

				<tfoot>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Team Title', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'City', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Country', 'sports-leagues' ); ?></td>
				</tr>
				</tfoot>
			</table>
		<?php else : ?>
			<div class="anwp-alert-warning">- <?php echo esc_html__( 'nothing found', 'sports-leagues' ); ?> -</div>
			<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Get selector Tournament initial data.
	 *
	 * @param array $data_initial
	 *
	 * @return array
	 * @since 0.12.4
	 */
	private function get_selector_tournament_initial( $data_initial ) {

		if ( empty( $data_initial ) || ! is_array( $data_initial ) ) {
			return [];
		}

		$query_args = [
			'post_type'               => [ 'sl_tournament' ],
			'posts_per_page'          => 50,
			'include'                 => $data_initial,
			'cache_results'           => false,
			'update_post_meta_cache'  => false,
			'update_post_term_cache ' => false,
			'post_parent'             => 0,
		];

		$results = get_posts( $query_args );

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		$output = [];

		/** @var $result_item WP_Post */
		foreach ( $results as $result_item ) {
			$output[] = [
				'id'   => $result_item->ID,
				'name' => $result_item->post_title,
			];
		}

		return $output;
	}

	/**
	 * Get selector Tournament Stage initial data.
	 *
	 * @param array $data_initial
	 *
	 * @return array
	 * @since 0.12.4
	 */
	private function get_selector_stage_initial( $data_initial ) {

		if ( empty( $data_initial ) || ! is_array( $data_initial ) ) {
			return [];
		}

		$query_args = [
			'post_type'               => [ 'sl_tournament' ],
			'posts_per_page'          => 50,
			'include'                 => $data_initial,
			'cache_results'           => false,
			'update_post_meta_cache'  => false,
			'update_post_term_cache ' => false,
			'post_parent__not_in'     => [ 0 ],
		];

		$results = get_posts( $query_args );

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		$output = [];

		/** @var $result_item WP_Post */
		foreach ( $results as $result_item ) {
			$output[] = [
				'id'   => $result_item->ID,
				'name' => sports_leagues()->tournament->get_tournament( $result_item->post_parent )->title . ' - ' . $result_item->post_title,
			];
		}

		return $output;
	}

	/**
	 * Get selector tournament data.
	 *
	 * @param array $search_data
	 *
	 * @return false|string
	 * @since 0.12.4
	 */
	private function get_selector_tournament_data( $search_data ) {

		$query_args = [
			'post_type'   => [ 'sl_tournament' ],
			'numberposts' => 30,
			's'           => $search_data['s'],
			'orderby'     => 'title',
			'order'       => 'ASC',
			'fields'      => 'ids',
			'post_parent' => 0,
		];

		$tax_query = [];

		if ( ! empty( $search_data['season'] ) && absint( $search_data['season'] ) ) {
			$tax_query[] =
				[
					'taxonomy' => 'sl_season',
					'terms'    => absint( $search_data['season'] ),
				];
		}

		if ( ! empty( $search_data['league'] ) && absint( $search_data['league'] ) ) {
			$tax_query[] =
				[
					'taxonomy' => 'sl_league',
					'terms'    => absint( $search_data['league'] ),
				];
		}

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		$tournament_ids = get_posts( $query_args );
		$output_data    = [];

		foreach ( $tournament_ids as $tournament_id ) {
			$tournament_obj = sports_leagues()->tournament->get_tournament( $tournament_id );

			if ( $tournament_obj ) {
				$output_data[] = (object) [
					'id'     => $tournament_obj->id,
					'title'  => $tournament_obj->title,
					'season' => $tournament_obj->season_text,
				];
			}
		}

		ob_start();

		if ( ! empty( $output_data ) ) :
			?>
			<table class="wp-list-table widefat striped table-view-list">
				<thead>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Tournament', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'ID', 'sports-leagues' ); ?></td>
				</tr>
				</thead>

				<tbody>
				<?php foreach ( $output_data as $tournament ) : ?>
					<tr data-id="<?php echo absint( $tournament->id ); ?>" data-name="<?php echo esc_html( $tournament->title ); ?>">
						<td>
							<button type="button" class="button button-small anwp-sl-selector-action">
								<span class="dashicons dashicons-plus"></span>
							</button>
						</td>
						<td><?php echo esc_html( $tournament->title ); ?></td>
						<td><?php echo esc_html( $tournament->season ); ?></td>
						<td><?php echo esc_html( $tournament->id ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>

				<tfoot>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Tournament', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'ID', 'sports-leagues' ); ?></td>
				</tr>
				</tfoot>
			</table>
		<?php else : ?>
			<div class="anwp-alert-warning">- <?php echo esc_html__( 'nothing found', 'sports-leagues' ); ?> -</div>
			<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Get selector tournament data.
	 *
	 * @param array $search_data
	 *
	 * @return false|string
	 * @since 0.12.4
	 */
	private function get_selector_stage_data( $search_data ) {

		$query_args = [
			'post_type'   => [ 'sl_tournament' ],
			'numberposts' => 30,
			's'           => $search_data['s'],
			'orderby'     => 'title',
			'order'       => 'ASC',
			'fields'      => 'ids',
			'post_parent' => 0,
		];

		$tax_query = [];

		if ( ! empty( $search_data['season'] ) && absint( $search_data['season'] ) ) {
			$tax_query[] =
				[
					'taxonomy' => 'sl_season',
					'terms'    => absint( $search_data['season'] ),
				];
		}

		if ( ! empty( $search_data['league'] ) && absint( $search_data['league'] ) ) {
			$tax_query[] =
				[
					'taxonomy' => 'sl_league',
					'terms'    => absint( $search_data['league'] ),
				];
		}

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		$tournament_ids = get_posts( $query_args );
		$output_data    = [];

		foreach ( $tournament_ids as $tournament_id ) {
			$tournament_obj = sports_leagues()->tournament->get_tournament( $tournament_id );

			if ( $tournament_obj && ! empty( $tournament_obj->stages ) ) {
				foreach ( $tournament_obj->stages as $stage ) {
					$output_data[] = (object) [
						'id'     => $stage->id,
						'title'  => $tournament_obj->title . ' - ' . $stage->title,
						'season' => $tournament_obj->season_text,
					];
				}
			}
		}

		ob_start();

		if ( ! empty( $output_data ) ) :
			?>
			<table class="wp-list-table widefat striped table-view-list">
				<thead>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Tournament Stage', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'ID', 'sports-leagues' ); ?></td>
				</tr>
				</thead>

				<tbody>
				<?php foreach ( $output_data as $tournament ) : ?>
					<tr data-id="<?php echo absint( $tournament->id ); ?>" data-name="<?php echo esc_html( $tournament->title ); ?>">
						<td>
							<button type="button" class="button button-small anwp-sl-selector-action">
								<span class="dashicons dashicons-plus"></span>
							</button>
						</td>
						<td><?php echo esc_html( $tournament->title ); ?></td>
						<td><?php echo esc_html( $tournament->season ); ?></td>
						<td><?php echo esc_html( $tournament->id ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>

				<tfoot>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Tournament Stage', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></td>
					<td class="manage-column column-format"><?php echo esc_html__( 'ID', 'sports-leagues' ); ?></td>
				</tr>
				</tfoot>
			</table>
		<?php else : ?>
			<div class="anwp-alert-warning">- <?php echo esc_html__( 'nothing found', 'sports-leagues' ); ?> -</div>
			<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Get selector season data.
	 *
	 * @param array $search_data
	 *
	 * @return false|string
	 * @since 0.12.4
	 */
	private function get_selector_season_data( $search_data ) {
		$output_data = [];
		$all_seasons = get_terms(
			[
				'number'     => 50,
				'search'     => $search_data['s'],
				'orderby'    => 'name',
				'taxonomy'   => 'sl_season',
				'hide_empty' => false,
			]
		);

		/** @var WP_Term $season_obj */
		foreach ( $all_seasons as $season_obj ) {
			$output_data[] = (object) [
				'id'   => $season_obj->term_id,
				'name' => $season_obj->name,
			];
		}

		ob_start();

		if ( ! empty( $output_data ) ) :
			?>
			<table class="wp-list-table widefat striped table-view-list">
				<thead>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></td>
					<td class="manage-column"><?php echo esc_html__( 'ID', 'sports-leagues' ); ?></td>
				</tr>
				</thead>

				<tbody>
				<?php foreach ( $output_data as $season ) : ?>
					<tr data-id="<?php echo absint( $season->id ); ?>" data-name="<?php echo esc_html( $season->name ); ?>">
						<td>
							<button type="button" class="button button-small anwp-sl-selector-action">
								<span class="dashicons dashicons-plus"></span>
							</button>
						</td>
						<td><?php echo esc_html( $season->name ); ?></td>
						<td><?php echo esc_html( $season->id ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>

				<tfoot>
				<tr>
					<td class="manage-column check-column"></td>
					<td class="manage-column"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></td>
					<td class="manage-column"><?php echo esc_html__( 'ID', 'sports-leagues' ); ?></td>
				</tr>
				</tfoot>
			</table>
		<?php else : ?>
			<div class="anwp-alert-warning">- <?php echo esc_html__( 'nothing found', 'sports-leagues' ); ?> -</div>
			<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Get selector Season initial data.
	 *
	 * @param array $data_initial
	 *
	 * @return array
	 * @since 0.12.4
	 */
	private function get_selector_season_initial( $data_initial ) {

		$query_args = [
			'number'     => 50,
			'include'    => $data_initial,
			'orderby'    => 'name',
			'taxonomy'   => 'sl_season',
			'hide_empty' => false,
		];

		$results = get_terms( $query_args );

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		$output = [];

		foreach ( $results as $season_obj ) {
			$output[] = [
				'id'   => $season_obj->term_id,
				'name' => $season_obj->name,
			];
		}

		return $output;
	}

	/**
	 * Recursive sanitization.
	 *
	 * @param string|array
	 *
	 * @return string|array
	 */
	public function recursive_sanitize( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ $this, 'recursive_sanitize' ], $value );
		} else {
			return is_scalar( $value ) ? sanitize_text_field( $value ) : $value;
		}
	}

	/**
	 * Get options in Select2 format
	 *
	 * @param array
	 *
	 * @return array
	 */
	public function get_select2_formatted_options( $options ) {
		$output = [];

		foreach ( $options as $option_key => $option_text ) {
			$output[] = [
				'id'   => $option_key,
				'text' => $option_text,
			];
		}

		return $output;
	}

	/**
	 * Get Youtube ID from url
	 *
	 * @param $url
	 *
	 * @return string Youtube ID or empty string
	 */
	public function get_youtube_id( $url ) {

		if ( mb_strlen( $url ) <= 11 ) {
			return $url;
		}

		preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches );

		return isset( $matches[1] ) ? $matches[1] : '';
	}

	/**
	 * Create metabox navigation items
	 *
	 * @param array $nav_items
	 *
	 * @return string
	 * @since 0.10.0
	 */
	public function create_metabox_navigation( $nav_items ) {

		ob_start();

		foreach ( $nav_items as $nav_item_index => $nav_item ) :

			$nav_item = wp_parse_args(
				$nav_item,
				[
					'icon'         => '',
					'icon_classes' => 'anwp-icon--octi',
					'classes'      => '',
					'label'        => '',
					'slug'         => '',
				]
			);

			?>
			<li class="anwp-sl-metabox-page-nav__item d-block m-0 anwp-border anwp-border-gray-500 <?php echo $nav_item_index ? 'anwp-border-top-0' : ''; ?>">
				<a class="anwp-sl-smooth-scroll d-flex align-items-center text-decoration-none anwp-link-without-effects anwp-text-gray-800 py-2 px-1 <?php echo esc_attr( $nav_item['classes'] ); ?>" href="#<?php echo esc_attr( $nav_item['slug'] ); ?>">
					<svg class="anwp-icon anwp-icon--s16 d-inline-block mx-2 anwp-flex-none anwp-fill-current <?php echo esc_attr( $nav_item['icon_classes'] ); ?>">
						<use xlink:href="#icon-<?php echo esc_attr( $nav_item['icon'] ); ?>"></use>
					</svg>
					<span class="ml-1"><?php echo esc_html( $nav_item['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
		<li class="anwp-sl-metabox-page-nav__item d-block m-0 anwp-border anwp-border-gray-500 anwp-border-top-0">
			<a class="d-flex align-items-center text-decoration-none anwp-link-without-effects anwp-text-gray-800 py-2 px-1 anwp-sl-collapse-menu" href="#">
				<svg class="anwp-icon anwp-icon--s16 anwp-icon--feather d-inline-block mx-2 anwp-flex-none">
					<use xlink:href="#icon-arrow-left-circle"></use>
				</svg>
				<span class="ml-1"><?php echo esc_html__( 'Collapse menu' ); ?></span>
			</a>
		</li>
		<?php

		return ob_get_clean();
	}

	/**
	 * Modify CMB2 Default Form Output
	 * Remove form tag and submit button
	 *
	 * @param string $form_format Form output format
	 * @param string $object_id   In the case of an options page, this will be the option key
	 * @param object $cmb         CMB2 object. Can use $cmb->cmb_id to retrieve the metabox ID
	 *
	 * @return string               Possibly modified form output
	 * @since 0.10.0
	 */
	public function modify_cmb2_metabox_form_format( $form_format, $object_id, $cmb ) {

		if ( in_array( $cmb->cmb_id, [ 'sl_team_metabox', 'sl_game_cmb2_metabox' ], true ) ) {
			$form_format = '<input type="hidden" name="object_id" value="%2$s">';
		}

		return $form_format;
	}

	/**
	 * Create metabox header
	 *
	 * @param array $data
	 *
	 * @return string
	 * @since 0.10.0
	 */
	public function create_metabox_header( $data ) {

		$data = wp_parse_args(
			$data,
			[
				'icon'         => '',
				'classes'      => 'mb-4',
				'icon_classes' => 'anwp-icon--octi',
				'label'        => '',
				'slug'         => '',
			]
		);

		// put some code into echo() to fix formatting issue
		ob_start();
		echo '<div class="anwp-border anwp-border-gray-500 ' . esc_attr( $data['classes'] ) . '" id="' . esc_attr( $data['slug'] ) . '">';
		?>
		<div class="anwp-border-bottom anwp-border-gray-500 bg-white d-flex align-items-center px-1 py-2 anwp-text-gray-700 anwp-font-semibold">
			<svg class="anwp-icon anwp-icon--s16 mx-2 anwp-fill-current <?php echo esc_attr( $data['icon_classes'] ); ?>">
				<use xlink:href="#icon-<?php echo esc_attr( $data['icon'] ); ?>"></use>
			</svg>
			<span><?php echo esc_html( $data['label'] ); ?></span>
		</div>
		<?php
		echo '<div class="bg-white p-3">';

		return ob_get_clean();
	}

	/**
	 * Download CSV files.
	 *
	 * @since 0.10.2
	 */
	public function download_csv() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $_GET['sl_export'] ) ) {
			return;
		}

		// Check if we are in WP-Admin
		if ( ! is_admin() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$export_type = sanitize_key( $_GET['sl_export'] );

		switch ( $export_type ) {
			case 'players':
				$this->download_csv_players();
				break;
		}
	}

	/**
	 * Download CSV files - Players.
	 *
	 * @since 0.10.2
	 */
	private function download_csv_players() {

		/*
		|--------------------------------------------------------------------
		| Mapping Data
		|--------------------------------------------------------------------
		*/
		$map_clubs     = sports_leagues()->team->get_team_options();
		$map_countries = sports_leagues()->data->get_countries();

		$custom_fields = sports_leagues()->get_option_value( 'player_custom_fields' );

		$header_row = [
			'Player Name',
			'Short Name',
			'Full Name',
			'Weight',
			'Height',
			'Position',
			'National Team',
			'Current Team',
			'Place of Birth',
			'Country of Birth',
			'Date of Birth',
			'Date of Death',
			'Bio',
			'Nationality #1',
			'Nationality #2',
			'Player ID',
			'Player External ID',
		];

		if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
			$header_row = array_merge( $header_row, $custom_fields );
		}

		$data_rows = [];

		$posts = get_posts(
			[
				'numberposts' => - 1,
				'post_type'   => 'sl_player',
			]
		);

		/** @var  $p WP_Post */
		foreach ( $posts as $p ) {

			/*
			|--------------------------------------------------------------------
			| Prepare Nationality data
			|--------------------------------------------------------------------
			*/
			$player_nationality   = maybe_unserialize( get_post_meta( $p->ID, '_sl_nationality', true ) );
			$player_nationality_1 = '';
			$player_nationality_2 = '';

			if ( is_array( $player_nationality ) ) {
				if ( ! empty( $player_nationality[0] ) && ! empty( $map_countries[ $player_nationality[0] ] ) ) {
					$player_nationality_1 = $map_countries[ $player_nationality[0] ];
				}
				if ( ! empty( $player_nationality[1] ) && ! empty( $map_countries[ $player_nationality[1] ] ) ) {
					$player_nationality_2 = $map_countries[ $player_nationality[1] ];
				}
			}

			$country_of_birth = get_post_meta( $p->ID, '_sl_country_of_birth', true ) ?: '';

			if ( ! empty( $country_of_birth ) ) {
				$country_of_birth = isset( $map_countries[ $country_of_birth ] ) ? $map_countries[ $country_of_birth ] : '';
			}

			$single_row_data = [
				$p->post_title,
				get_post_meta( $p->ID, '_sl_short_name', true ),
				get_post_meta( $p->ID, '_sl_full_name', true ),
				get_post_meta( $p->ID, '_sl_weight', true ),
				get_post_meta( $p->ID, '_sl_height', true ),
				get_post_meta( $p->ID, '_sl_position', true ),
				isset( $map_clubs[ get_post_meta( $p->ID, '_sl_national_team', true ) ] ) ? $map_clubs[ get_post_meta( $p->ID, '_sl_national_team', true ) ] : '',
				isset( $map_clubs[ get_post_meta( $p->ID, '_sl_current_team', true ) ] ) ? $map_clubs[ get_post_meta( $p->ID, '_sl_current_team', true ) ] : '',
				get_post_meta( $p->ID, '_sl_place_of_birth', true ),
				$country_of_birth,
				get_post_meta( $p->ID, '_sl_date_of_birth', true ),
				get_post_meta( $p->ID, '_sl_date_of_death', true ),
				get_post_meta( $p->ID, '_sl_bio', true ),
				$player_nationality_1,
				$player_nationality_2,
				$p->ID,
				get_post_meta( $p->ID, '_sl_player_external_id', true ),
			];

			/*
			|--------------------------------------------------------------------
			| Custom fields
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
				$custom_fields_data = get_post_meta( $p->ID, '_sl_custom_fields', true );

				foreach ( $custom_fields as $custom_field ) {
					if ( ! empty( $custom_fields_data ) && is_array( $custom_fields_data ) && ! empty( $custom_fields_data[ $custom_field ] ) ) {
						$single_row_data[] = $custom_fields_data[ $custom_field ];
					} else {
						$single_row_data[] = '';
					}
				}
			}

			$data_rows[] = $single_row_data;
		}

		ob_start();

		$fh = @fopen( 'php://output', 'w' ); // phpcs:ignore

		fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename=players.csv' );
		header( 'Expires: 0' );
		header( 'Pragma: public' );

		fputcsv( $fh, $header_row );

		foreach ( $data_rows as $data_row ) {
			fputcsv( $fh, $data_row );
		}

		fclose( $fh ); // phpcs:ignore

		ob_end_flush();

		die();
	}
}
