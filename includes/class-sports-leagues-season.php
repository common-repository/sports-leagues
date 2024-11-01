<?php
/**
 * Sports Leagues :: Season.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Season {

	/**
	 * Parent plugin class.
	 *
	 * @var    Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Taxonomy.
	 *
	 * @since  0.1.0
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Assign main plugin class
		$this->plugin = $plugin;

		// Register League Taxonomy
		$this->register_taxonomy();

		// Init hooks
		$this->hooks();
	}

	/**
	 * Register taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function register_taxonomy() {

		$labels = [
			'name'                       => _x( 'Seasons', 'taxonomy general name', 'sports-leagues' ),
			'singular_name'              => _x( 'Season', 'taxonomy singular name', 'sports-leagues' ),
			'search_items'               => __( 'Search Seasons', 'sports-leagues' ),
			'popular_items'              => __( 'Popular Seasons', 'sports-leagues' ),
			'all_items'                  => __( 'All Seasons', 'sports-leagues' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Season', 'sports-leagues' ),
			'update_item'                => __( 'Update Season', 'sports-leagues' ),
			'add_new_item'               => __( 'Add New Season', 'sports-leagues' ),
			'new_item_name'              => __( 'New Season Title', 'sports-leagues' ),
			'separate_items_with_commas' => __( 'Separate Seasons with commas', 'sports-leagues' ),
			'add_or_remove_items'        => __( 'Add or remove Seasons', 'sports-leagues' ),
			'choose_from_most_used'      => __( 'Choose from the most used Seasons', 'sports-leagues' ),
			'not_found'                  => __( 'No Seasons found.', 'sports-leagues' ),
			'menu_name'                  => __( 'Seasons', 'sports-leagues' ),
		];

		$args = [
			'hierarchical'      => false,
			'show_in_nav_menus' => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'season' ],
		];

		register_taxonomy( 'sl_season', [ 'sl_tournament' ], $args );
	}

	/**
	 * Helper function, returns seasons with id and title.
	 * Used in VUE squad creating.
	 *
	 * @since 0.1.0
	 * @return array $output_data - Array of seasons objects.
	 */
	public function get_seasons_list() {

		static $output_data = null;

		if ( null === $output_data ) {

			$output_data = [];

			$all_seasons = get_terms(
				[
					'taxonomy'         => 'sl_season',
					'suppress_filters' => false,
					'hide_empty'       => false,
					'orderby'          => 'name',
					'order'            => 'DESC',
				]
			);

			/** @var WP_Term $season */
			foreach ( $all_seasons as $season ) {

				$season_obj        = (object) [];
				$season_obj->id    = $season->term_id;
				$season_obj->title = $season->name;
				$season_obj->slug  = $season->slug;

				$output_data[] = $season_obj;
			}
		}

		return $output_data;
	}

	/**
	 * Helper function, returns seasons with id and title.
	 * Can be used as cmb2 callback options.
	 *
	 * @since 0.1.0
	 * @return array $output_data - Array <season_id> => <season_title>.
	 */
	public function get_season_options() {

		static $output_data = null;

		if ( null === $output_data ) {

			$output_data = [];

			$all_seasons = get_terms(
				[
					'taxonomy'   => 'sl_season',
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'DESC',
				]
			);

			/** @var WP_Term $season */
			foreach ( $all_seasons as $season ) {
				$output_data[ $season->term_id ] = $season->name;
			}
		}

		return $output_data;
	}

	/**
	 * Get Season slug by term_id
	 *
	 * @return int | null
	 * @since 0.11.0
	 */
	public function get_max_season_id() {
		static $season_id = null;

		if ( null === $season_id ) {
			$season_options = sports_leagues()->season->get_season_options();

			if ( ! empty( $season_options ) && is_array( $season_options ) ) {
				arsort( $season_options );
				$season_id = key( $season_options );
			}
		}

		return (int) $season_id;
	}

	/**
	 * Get Season slug by term_id
	 *
	 * @param int $term_id
	 *
	 * @return string
	 * @since 0.11.0
	 */
	public function get_season_slug_by_id( $term_id ) {

		$slug    = '';
		$seasons = $this->get_seasons_list();

		if ( ! empty( $seasons ) ) {
			$season_obj = array_values( wp_list_filter( $seasons, [ 'id' => $term_id ] ) );

			if ( ! empty( $season_obj[0] ) && ! empty( $season_obj[0]->slug ) ) {
				return $season_obj[0]->slug;
			}
		}

		return $slug;
	}

	/**
	 * Get Season term_id by slug
	 *
	 * @param string slug
	 *
	 * @return int|string
	 * @since 0.11.0
	 */
	public function get_season_id_by_slug( $term_slug ) {

		$id      = '';
		$seasons = $this->get_seasons_list();

		if ( ! empty( $seasons ) ) {
			$season_obj = array_values( wp_list_filter( $seasons, [ 'slug' => $term_slug ] ) );

			if ( ! empty( $season_obj[0] ) && ! empty( $season_obj[0]->id ) ) {
				return $season_obj[0]->id;
			}
		}

		return $id;
	}

	/**
	 * Helper function, returns seasons with slug and title.
	 * Used in season dropdown.
	 *
	 * @since 0.1.0
	 * @return array $output_data
	 */
	public function get_season_slug_options() {

		static $output_data = null;

		if ( null === $output_data ) {

			$output_data = [];

			$all_seasons = get_terms(
				[
					'taxonomy'   => 'sl_season',
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'DESC',
				]
			);

			if ( is_array( $all_seasons ) ) {
				/** @var WP_Term $season */
				foreach ( $all_seasons as $season ) {
					$output_data[] = [
						'slug' => $season->slug,
						'name' => $season->name,
					];
				}
			}
		}

		return $output_data;
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 0.1.0
	 */
	public function hooks() {

		add_action( 'sl_season_add_form_fields', [ $this, 'add_to_form' ] );

		add_filter( 'manage_sl_season_custom_column', [ $this, 'columns_display' ], 10, 3 );
		add_filter( 'manage_edit-sl_season_columns', [ $this, 'columns' ], 10, 1 );

		add_action( 'created_sl_season', [ $this, 'set_default_season' ] );
	}

	/**
	 * Set default season, if not set.
	 *
	 * @param $term_id
	 *
	 * @since 0.9.1
	 */
	public function set_default_season( $term_id ) {

		if ( ! Sports_Leagues_Options::get_value( 'active_season' ) && function_exists( 'cmb2_update_option' ) ) {
			cmb2_update_option( 'sports_leagues_settings', 'active_season', $term_id );
		}
	}

	/**
	 * Registers admin columns to display.
	 *
	 * @param  array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 * @since  0.1.0
	 */
	public function columns( $columns ) {

		// Add new columns
		$new_columns = [
			'sl_season_id' => esc_html__( 'ID', 'sports-leagues' ),
		];

		return array_merge( $columns, $new_columns );
	}

	/**
	 * Handles admin column display.
	 *
	 * @param string $string      Blank string.
	 * @param string $column_name Name of the column.
	 * @param int    $term_id     Term ID.
	 *
	 * @since  0.1.0
	 */
	public function columns_display( $string, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'sl_season_id':
				echo (int) $term_id;
				break;
		}
	}

	/**
	 * Add notify about naming recommendations.
	 *
	 * @since 0.1.0
	 */
	public function add_to_form() {

		ob_start();
		?>
		<div class="anwp-global-info">
			<?php echo esc_html__( 'Recommended season name is "YYYY" or "YYYY-YYYY". E.g.: "2018" or "2017-2018"', 'sports-leagues' ); ?>
		</div>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get season ID
	 *
	 * @param array $get_data
	 * @param int   $season_id
	 *
	 * @return int
	 * @since 0.12.5
	 */
	public function get_season_id_maybe( $get_data, $season_id ) {

		if ( empty( $get_data['season'] ) ) {
			return $season_id;
		}

		$maybe_season_id = $this->get_season_id_by_slug( sanitize_key( $get_data['season'] ) );

		if ( absint( $maybe_season_id ) ) {
			$season_id = absint( $maybe_season_id );
		}

		return $season_id;
	}
}
