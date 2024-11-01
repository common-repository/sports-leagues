<?php
/**
 * Sports Leagues Config.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

/**
 * Sports_Leagues_Config class.
 *
 * @since 0.1.0
 */
class Sports_Leagues_Config {

	/**
	 * Parent plugin class.
	 *
	 * @var    Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Option key
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	public static $key = 'sports_leagues_config';

	/**
	 * Constructor.
	 *
	 * @param Sports_Leagues $plugin Main plugin object.
	 *
	 * @since  0.1.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
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
			'/settings/save_sport_config',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_sport_config' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $key     Options array key
	 * @param  mixed  $default Optional default value
	 * @return mixed           Option value
	 */
	public static function get_value( $key = '', $default = false ) {

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( self::$key, $default );

		$val = $default;

		if ( 'all' === $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}

	/**
	 * Get player positions.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_player_positions() {

		$options   = [];
		$positions = self::get_value( 'position' );

		if ( empty( $positions ) || ! is_array( $positions ) ) {
			return $options;
		}

		foreach ( $positions as $position ) {
			$options[ $position['id'] ] = $position['name'];
		}

		return $options;
	}

	/**
	 * Returns config options for selected value.
	 *
	 * @param string $value
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_options( $value ) {

		$options = self::get_value( $value );

		if ( ! empty( $options ) && is_array( $options ) ) {
			return $options;
		}

		return [];
	}

	/**
	 * Get Standing config data.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_standing_config() {

		$options = [];

		$columns = [
			'played'   => esc_html__( 'Played', 'sports-leagues' ),
			'all_win'  => esc_html__( 'Win - all', 'sports-leagues' ),
			'ft_win'   => esc_html__( 'Win - full time', 'sports-leagues' ),
			'ov_win'   => esc_html__( 'Win - overtime', 'sports-leagues' ),
			'pen_win'  => esc_html__( 'Win - penalty', 'sports-leagues' ),
			'draw'     => esc_html__( 'Draw', 'sports-leagues' ),
			'all_loss' => esc_html__( 'Loss - all', 'sports-leagues' ),
			'ft_loss'  => esc_html__( 'Loss - full time', 'sports-leagues' ),
			'ov_loss'  => esc_html__( 'Loss - overtime', 'sports-leagues' ),
			'pen_loss' => esc_html__( 'Loss - penalty', 'sports-leagues' ),
			'sf'       => esc_html__( 'Scores - for', 'sports-leagues' ),
			'sa'       => esc_html__( 'Scores - against', 'sports-leagues' ),
			'sd'       => esc_html__( 'Scores - difference', 'sports-leagues' ),
			'ratio'    => esc_html__( 'Ratio (win/played)', 'sports-leagues' ),
			'bpts'     => esc_html__( 'Bonus Points', 'sports-leagues' ),
			'pts'      => esc_html__( 'Points', 'sports-leagues' ),
		];

		foreach ( $columns as $key => $origin ) {

			$option_value = self::get_value( 'standing_str__' . sanitize_key( $key ) );

			$options[ $key ] = [
				'origin' => $origin,
				'key'    => $key,
				'abbr'   => empty( $option_value['abbr'] ) ? $key : $option_value['abbr'],
				'name'   => empty( $option_value['name'] ) ? $origin : $option_value['name'],
			];
		}

		return $options;
	}

	/**
	 * Save Sport Configuration
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_Error
	 * @since 0.10.0
	 */
	public function save_sport_config( WP_REST_Request $request ) {

		// Get Request params
		$params = $request->get_params();

		// Check API Method exists
		if ( empty( $params['sport_config'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect Data', [ 'status' => 400 ] );
		}

		$sport_config = $this->plugin->helper->recursive_sanitize( $params['sport_config'] );
		$old_settings = get_option( self::$key, [] );

		if ( $sport_config === $old_settings || maybe_serialize( $sport_config ) === maybe_serialize( $old_settings ) ) {
			return rest_ensure_response( [ 'result' => 'Nothing to Update' ] );
		}

		if ( ! update_option( self::$key, $sport_config, true ) ) {
			return new WP_Error( 'rest_invalid', 'Update Problem', [ 'status' => 400 ] );
		}

		return rest_ensure_response( [ 'result' => 'Saved Successfully' ] );
	}

	/**
	 * Get field name by its id
	 *
	 * @param string $field
	 * @param string $field_id
	 * @param bool   $return_empty_false
	 *
	 * @return string
	 * @since 0.10.0
	 */
	public function get_name_by_id( $field, $field_id, $return_empty_false = false ) {

		$options    = [];
		$field_name = '';

		static $cached_output = [];

		if ( ! empty( $cached_output[ $field ] ) && ! empty( $cached_output[ $field ][ $field_id ] ) ) {
			return $cached_output[ $field ][ $field_id ];
		}

		switch ( $field ) {
			case 'position':
				$options = $this->get_options( 'position' );
				break;

			case 'game_player_groups':
				$options = $this->get_options( 'game_player_groups' );
				break;

			case 'official_groups':
				$options = $this->get_options( 'official_groups' );
				break;

			case 'staff_roster_groups':
				$options = $this->get_options( 'staff_roster_groups' );
				break;

			case 'roster_groups':
				$options = $this->get_options( 'roster_groups' );
				break;
		}

		$cached_output[ $field ] = [];

		foreach ( $options as $single_option ) {

			$cached_output[ $field ][ $single_option['id'] ] = $single_option['name'];

			if ( $single_option['id'] === $field_id ) {
				$field_name = $single_option['name'];
			}
		}

		return $field_name ?: ( $return_empty_false ? false : $field_id );
	}
}
