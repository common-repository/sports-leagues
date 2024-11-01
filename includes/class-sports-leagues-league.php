<?php
/**
 * Sports Leagues :: League.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_League {

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
	 *
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
			'name'                       => _x( 'Leagues', 'taxonomy general name', 'sports-leagues' ),
			'singular_name'              => _x( 'League', 'taxonomy singular name', 'sports-leagues' ),
			'search_items'               => __( 'Search Leagues', 'sports-leagues' ),
			'popular_items'              => __( 'Popular Leagues', 'sports-leagues' ),
			'all_items'                  => __( 'All Leagues', 'sports-leagues' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit League', 'sports-leagues' ),
			'update_item'                => __( 'Update League', 'sports-leagues' ),
			'add_new_item'               => __( 'Add New League', 'sports-leagues' ),
			'new_item_name'              => __( 'New League Title', 'sports-leagues' ),
			'separate_items_with_commas' => __( 'Separate Leagues with commas', 'sports-leagues' ),
			'add_or_remove_items'        => __( 'Add or remove Leagues', 'sports-leagues' ),
			'choose_from_most_used'      => __( 'Choose from the most used Leagues', 'sports-leagues' ),
			'not_found'                  => __( 'No Leagues found.', 'sports-leagues' ),
			'menu_name'                  => __( 'Leagues', 'sports-leagues' ),
		];

		$args = [
			'hierarchical'      => false,
			'show_in_nav_menus' => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'league' ],
		];

		register_taxonomy( 'sl_league', [ 'sl_tournament' ], $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 0.1.0
	 */
	public function hooks() {

		add_filter( 'manage_sl_league_custom_column', [ $this, 'columns_display' ], 10, 3 );
		add_filter( 'manage_edit-sl_league_columns', [ $this, 'columns' ], 10, 1 );

		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );
	}

	/**
	 * Create CMB2 metabox
	 *
	 * @since 0.1.0
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sl_';

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			[
				'id'           => 'sl_league_metabox',
				'title'        => esc_html__( 'League Info', 'sports-leagues' ),
				'object_types' => [ 'term' ],
				'context'      => 'normal',
				'priority'     => 'high',
				'classes'      => 'anwp-b-wrap',
				'show_names'   => true,
				'taxonomies'   => [ 'sl_league' ],
			]
		);

		$cmb->add_field(
			[
				'name'             => esc_html__( 'Country', 'sports-leagues' ),
				'id'               => $prefix . 'country',
				'type'             => 'select',
				'show_option_none' => '- ' . esc_html__( 'select country', 'sports-leagues' ) . ' -',
				'default'          => '',
				'options_cb'       => [ $this->plugin->data, 'get_countries' ],
				'column'           => [
					'position' => 4,
				],
				'display_cb'       => [ $this, 'render_league_country_column' ],
			]
		);
	}

	/**
	 * Rendering Country Column content.
	 *
	 * @param            $field_args
	 * @param CMB2_Field $field
	 *
	 * @since 0.1.0
	 */
	public function render_league_country_column( $field_args, $field ) {

		$options = $field->options();

		if ( isset( $options[ $field->value ] ) ) {
			echo esc_html( $options[ $field->value ] );
		} else {
			echo esc_attr( $field->value );
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
			'sl_league_id' => esc_html__( 'ID', 'sports-leagues' ),
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
			case 'sl_league_id':
				echo (int) $term_id;
				break;
		}
	}

	/**
	 * Helper function, returns leagues with id and title.
	 * Can be used as callback with options.
	 *
	 * @since 0.1.0
	 * @return array $output_data [ <league_id> => <league_title> ]
	 */
	public function get_league_options() {

		static $output_data = null;

		if ( null === $output_data ) {

			$output_data = [];

			$all_leagues = get_terms(
				[
					'taxonomy'   => 'sl_league',
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'DESC',
				]
			);

			/** @var WP_Term $league */
			foreach ( $all_leagues as $league ) {
				$output_data[ $league->term_id ] = $league->name;
			}
		}

		return $output_data;
	}

	/**
	 * Helper function, returns leagues objects.
	 * Used in Vue multiselect
	 *
	 * @since 0.1.0
	 * @return array $output_data
	 */
	public function get_leagues_list() {

		static $output_data = null;

		if ( null === $output_data ) {

			$output_data = [];

			$all_leagues = get_terms(
				[
					'taxonomy'   => 'sl_league',
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				]
			);

			/** @var WP_Term $league */
			foreach ( $all_leagues as $league ) {

				$country_code = get_term_meta( $league->term_id, '_sl_country', true );

				$output_data[] = (object) [
					'id'           => $league->term_id,
					'name'         => $league->name,
					'country_code' => $country_code ?: '',
					'country'      => sports_leagues()->data->get_country_by_code( $country_code ),
				];
			}
		}

		return $output_data;
	}
}
