<?php
/**
 * Sports Leagues :: Player.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Player {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Type.
	 *
	 * @since  0.1.0
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save core plugin to var
		$this->plugin = $plugin;

		// Register CPT
		$this->register_post_type( $plugin );

		// Run hooks
		$this->hooks();
	}

	/**
	 * Register Custom Post Type
	 *
	 * @param Sports_Leagues $plugin Main plugin object.
	 *
	 * @since 0.1.0
	 */
	public function register_post_type( $plugin ) {

		$permalink_structure = $plugin->options->get_permalink_structure();
		$permalink_slug      = empty( $permalink_structure['player'] ) ? 'player' : $permalink_structure['player'];

		// Register this CPT.
		$labels = [
			'name'                  => _x( 'Players', 'Post type general name', 'sports-leagues' ),
			'singular_name'         => _x( 'Player', 'Post type singular name', 'sports-leagues' ),
			'menu_name'             => _x( 'Persons', 'Admin Menu text', 'sports-leagues' ),
			'name_admin_bar'        => _x( 'Player', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'               => __( 'Add New Player', 'sports-leagues' ),
			'add_new_item'          => __( 'Add New Player', 'sports-leagues' ),
			'new_item'              => __( 'New Player', 'sports-leagues' ),
			'edit_item'             => __( 'Edit Player', 'sports-leagues' ),
			'view_item'             => __( 'View Player', 'sports-leagues' ),
			'all_items'             => __( 'All Players', 'sports-leagues' ),
			'search_items'          => __( 'Search Players', 'sports-leagues' ),
			'not_found'             => __( 'No players found.', 'sports-leagues' ),
			'not_found_in_trash'    => __( 'No players found in Trash.', 'sports-leagues' ),
			'featured_image'        => __( 'Person photo', 'sports-leagues' ),
			'set_featured_image'    => __( 'Set person photo', 'sports-leagues' ),
			'remove_featured_image' => __( 'Remove person photo', 'sports-leagues' ),
			'use_featured_image'    => __( 'Use as person photo', 'sports-leagues' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_position'      => 44,
			'menu_icon'          => 'dashicons-groups',
			'query_var'          => true,
			'rewrite'            => [ 'slug' => $permalink_slug ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => [ 'title', 'thumbnail', 'comments' ],
		];

		if ( apply_filters( 'sports-leagues/config/cpt_only_admin_access', true ) ) {
			$args['capabilities'] = [
				'edit_post'         => 'manage_options',
				'read_post'         => 'manage_options',
				'delete_post'       => 'manage_options',
				'edit_posts'        => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'delete_posts'      => 'manage_options',
				'publish_posts'     => 'manage_options',
			];
		}

		register_post_type( 'sl_player', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Create CMB2 metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );

		// Add custom filters in Admin table
		add_action( 'restrict_manage_posts', [ $this, 'custom_admin_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );

		// Render Custom Content below
		add_action(
			'sports-leagues/tmpl-player/after_wrapper',
			function ( $player_id ) {

				$content_below = get_post_meta( $player_id, '_sl_bottom_content', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Add tabs functionality for metabox
		add_action( 'cmb2_before_post_form_sl_player_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_post_form_sl_player_metabox', [ $this, 'cmb2_after_metabox' ] );

		// Filters the title field placeholder text.
		add_filter( 'enter_title_here', [ $this, 'title' ], 10, 2 );

		// Modifies columns in Admin tables
		add_action( 'manage_sl_player_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_player_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-sl_player_sortable_columns', [ $this, 'sortable_columns' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.1.0
	 */
	public function add_rest_routes() {
		register_rest_route(
			'sports-leagues/v1',
			'/search-persons/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_persons_search' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/v1',
			'/players-list/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_players_list_rest' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/v1',
			'/players/get_stat/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_stat_players' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	/**
	 * Handle API POST Requests
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function get_players_list_rest( WP_REST_Request $request ) {

		return rest_ensure_response( $this->get_players_list() );
	}

	/**
	 * Get team players for REST search request.
	 *
	 * @return array $output_data
	 * @since 0.1.0
	 */
	public function get_persons_search() {

		// phpcs:ignore WordPress.Security.NonceVerification
		$search = sanitize_text_field( $_GET['search'] );

		$args = [
			's'           => $search,
			'numberposts' => 50,
			'post_type'   => [ 'sl_player' ],
			'orderby'     => 'title',
		];

		return $this->get_persons( $args );
	}

	/**
	 * Get team players and staff.
	 *
	 * @return array $output_data -
	 * @since 0.1.0
	 */
	public function get_persons( $args ) {
		$output_data = [];

		$all_players = get_posts( $args );

		/** @var WP_Post $player */
		foreach ( $all_players as $player ) {

			$player_obj              = (object) [];
			$player_obj->id          = $player->ID;
			$player_obj->title       = $player->post_title;
			$player_obj->type        = 'player';
			$player_obj->position    = get_post_meta( $player->ID, '_sl_position', true );
			$player_obj->team_id     = get_post_meta( $player->ID, '_sl_current_team', true );
			$player_obj->team        = sports_leagues()->team->get_team_title_by_id( $player_obj->team_id );
			$player_obj->nationality = get_post_meta( $player->ID, '_sl_nationality', true );
			$player_obj->birthdate   = get_post_meta( $player->ID, '_sl_date_of_birth', true );

			// Format date
			if ( $player_obj->birthdate ) {
				$player_obj->birthdate = date_i18n( get_option( 'date_format' ), strtotime( $player_obj->birthdate ) );
			}

			$output_data[] = $player_obj;
		}

		return $output_data;
	}

	/**
	 * Method returns players with id and title.
	 * Used in admin Squad assigning.
	 *
	 * @return array
	 */
	public function get_player_photo_map() {

		static $output = null;

		if ( null === $output ) {

			$cache_key = 'SL-PLAYER-PHOTO-MAP';
			$output    = [];

			if ( sports_leagues()->cache->get( $cache_key ) ) {
				$output = sports_leagues()->cache->get( $cache_key );

				return $output;
			}

			global $wpdb;

			$rows = $wpdb->get_results(
				"
			SELECT p.ID, pm2.meta_value as file_url
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key = '_thumbnail_id' )
			LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = pm1.meta_value AND pm2.meta_key = '_wp_attached_file' )
			WHERE p.post_status = 'publish' AND p.post_type = 'sl_player' AND pm2.meta_value != ''
			"
			);

			if ( empty( $rows ) ) {
				return [];
			}

			$upload_dir      = wp_get_upload_dir();
			$upload_dir_path = empty( $upload_dir['baseurl'] ) ? '' : trailingslashit( $upload_dir['baseurl'] );

			if ( is_ssl() ) {
				$upload_dir_path = str_replace( 'http://', 'https://', $upload_dir_path );
			}

			foreach ( $rows as $row ) {
				$output[ $row->ID ] = $upload_dir_path . $row->file_url;
			}

			/*
			|--------------------------------------------------------------------
			| Save transient
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $output ) ) {
				sports_leagues()->cache->set( $cache_key, $output );
			}
		}

		return $output;
	}

	/**
	 * Get all players from DB.
	 *
	 * @return array
	 */
	private function get_all_players() {

		$cache_key = 'SL-PLAYERS-LIST';

		if ( sports_leagues()->cache->get( $cache_key ) ) {
			return sports_leagues()->cache->get( $cache_key );
		}

		global $wpdb;

		$all_players = $wpdb->get_results(
			"
			SELECT p.ID id, p.post_title pt,
				MAX( CASE WHEN pm.meta_key = '_sl_position' THEN pm.meta_value ELSE '' END) as pos,
				MAX( CASE WHEN pm.meta_key = '_sl_short_name' THEN pm.meta_value ELSE '' END) as sn,
				MAX( CASE WHEN pm.meta_key = '_sl_nationality' THEN pm.meta_value ELSE '' END) as nat,
				MAX( CASE WHEN pm.meta_key = '_sl_date_of_birth' THEN pm.meta_value ELSE '' END) as dob,
				MAX( CASE WHEN pm.meta_key = '_sl_current_team' THEN pm.meta_value ELSE '' END) as t_id
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = p.ID )
			WHERE p.post_status = 'publish' AND p.post_type = 'sl_player'
			GROUP BY p.ID
			ORDER BY p.post_title
			",
			OBJECT_K
		);

		if ( empty( $all_players ) ) {
			return [];
		}

		foreach ( $all_players as $player ) {
			if ( $player->nat ) {
				$countries = maybe_unserialize( $player->nat );

				if ( ! empty( $countries ) && is_array( $countries ) ) {
					$player->nat = implode( ',', $countries );
				}
			}
		}

		sports_leagues()->cache->set( $cache_key, $all_players );

		return $all_players;
	}

	/**
	 * Method returns players with id and title.
	 * Used in admin Squad assigning.
	 *
	 * @return array
	 */
	public function get_players_list( $squad_position_map = [] ) {

		$all_players = $this->get_all_players();

		if ( empty( $all_players ) ) {
			return [];
		}

		$players_prepared = [];

		// Remove array keys
		$all_players = array_values( $all_players );

		// Sort by player name
		$all_players = wp_list_sort( $all_players, 'pt' );

		// Add photos
		$player_photos = $this->get_player_photo_map();

		foreach ( $all_players as $player ) {

			$player_prepared = (object) [
				'id'        => absint( $player->id ),
				'title'     => $player->pt,
				'team_id'   => absint( $player->t_id ),
				'country'   => '',
				'country2'  => '',
				'type'      => 'player',
				'birthdate' => empty( $player->dob ) ? '' : date_i18n( 'M j, Y', strtotime( $player->dob ) ),
				'position'  => $player->pos,
				'photo'     => empty( $player_photos[ $player->id ] ) ? '' : $player_photos[ $player->id ],
			];

			if ( ! empty( $squad_position_map[ $player->id ] ) ) {
				$player_prepared->position = $squad_position_map[ $player->id ];
			}

			if ( $player->nat ) {
				$countries = explode( ',', $player->nat );

				if ( ! empty( $countries[0] ) ) {
					$player_prepared->country = mb_strtolower( $countries[0] );
				}

				if ( ! empty( $countries[1] ) ) {
					$player_prepared->country2 = mb_strtolower( $countries[1] );
				}
			}

			$players_prepared[] = $player_prepared;
		}

		return $players_prepared;
	}

	/**
	 * Get player data.
	 *
	 * @param $player_id
	 *
	 * @return object - Data object.
	 * @since 0.1.0
	 */
	public function get_player( $player_id ) {

		static $all_players = null;

		if ( null === $all_players ) {
			$all_players = $this->get_all_players();
		}

		$defaults = (object) [
			'name'        => '',
			'nationality' => [],
			'team_id'     => '',
			'photo'       => '',
			'id'          => '',
			'name_short'  => '',
			'link'        => '',
			'position'    => '',
			'birth_date'  => '',
		];

		$player_id     = absint( $player_id );
		$player_cached = empty( $all_players[ $player_id ] ) ? false : $all_players[ $player_id ];

		if ( ! $player_id || empty( $player_cached ) ) {
			return $defaults;
		}

		// Add photos
		$player_photos = $this->get_player_photo_map();

		return (object) [
			'id'          => $player_cached->id,
			'name'        => $player_cached->pt,
			'team_id'     => $player_cached->t_id,
			'name_short'  => $player_cached->sn ? : $player_cached->pt,
			'birth_date'  => $player_cached->dob,
			'position_id' => $player_cached->pos,
			'position'    => sports_leagues()->config->get_name_by_id( 'position', $player_cached->pos ),
			'photo'       => empty( $player_photos[ $player_cached->id ] ) ? '' : $player_photos[ $player_cached->id ],
			'nationality' => $player_cached->nat ? explode( ',', $player_cached->nat ) : [],
			'link'        => '', // ToDo will be removed
		];
	}

	/**
	 * Filters CPT title entry placeholder text
	 *
	 * @param string  $title Placeholder text. Default 'Enter title here'.
	 * @param WP_Post $post  Post object.
	 *
	 * @return string        Modified placeholder text
	 */
	public function title( $title, $post ) {

		if ( isset( $post->post_type ) && 'sl_player' === $post->post_type ) {
			return esc_html__( 'Player Name', 'sports-leagues' );
		}

		return $title;
	}

	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @since 0.1.0
	 */
	public function custom_admin_filters( $post_type ) {

		if ( 'sl_player' === $post_type ) {

			$teams = $this->plugin->team->get_team_options();

			// phpcs:ignore WordPress.Security.NonceVerification
			$current_team_filter = empty( $_GET['_sl_current_team'] ) ? '' : absint( $_GET['_sl_current_team'] );
			ob_start();
			?>

			<select name='_sl_current_team' id='anwp_team_filter' class='postform'>
				<option value=''><?php echo esc_html__( 'All Teams', 'sports-leagues' ); ?></option>
				<?php foreach ( $teams as $team_id => $team_title ) : ?>
					<option value="<?php echo esc_attr( $team_id ); ?>" <?php selected( $team_id, $current_team_filter ); ?>>
						- <?php echo esc_html( $team_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handle custom filter.
	 *
	 * @param WP_Query $query
	 *
	 * @since 0.1.0
	 */
	public function handle_custom_filter( $query ) {
		global $post_type, $pagenow;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( 'edit.php' === $pagenow && 'sl_player' === $post_type && ! empty( $_GET['_sl_current_team'] ) ) {
			$query->set(
				'meta_query',
				[
					[
						'key'     => '_sl_current_team',
						'value'   => absint( $_GET['_sl_current_team'] ), // phpcs:ignore WordPress.Security.NonceVerification
						'compare' => '=',
					],
				]
			);
		}
	}

	/**
	 * Renders tabs for metabox. Helper HTML before.
	 *
	 * @since 0.1.0
	 */
	public function cmb2_before_metabox() {
		// @formatter:off
		ob_start();
		?>
		<div class="anwp-b-wrap">
			<div class="anwp-metabox-tabs d-sm-flex">
				<div class="anwp-metabox-tabs__controls d-flex flex-sm-column">
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-general-sl_player_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-person"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'General', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bio-sl_player_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-note"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bio', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-gallery-sl_player_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-device-camera"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Gallery', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-social-sl_player_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-repo-forked"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Social', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-custom_fields-sl_player_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-server"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Custom Fields', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bottom_content-sl_player_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-repo-push"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bottom Content', 'sports-leagues' ); ?></span>
					</div>
					<?php
					/**
					 * Fires in the bottom of player tabs.
					 *
					 * @since 0.1.0
					 */
					do_action( 'sports-leagues/cmb2_tabs_control/player' );
					?>
				</div>
				<div class="anwp-metabox-tabs__content pl-4 pb-4">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
		// @formatter:on
	}

	/**
	 * Renders tabs for metabox. Helper HTML after.
	 *
	 * @since 0.1.0
	 */
	public function cmb2_after_metabox() {
		// @formatter:off
		ob_start();
		?>
				</div>
			</div>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
		// @formatter:on
	}

	/**
	 * Create CMB2 metaboxes
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
				'id'           => 'sl_player_metabox',
				'title'        => esc_html__( 'Player Data', 'sports-leagues' ),
				'object_types' => [ 'sl_player' ],
				'context'      => 'normal',
				'classes'      => [ 'anwp-b-wrap' ],
				'priority'     => 'high',
				'show_names'   => true,
			]
		);

		// Short Name
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Short Name', 'sports-leagues' ),
				'id'         => $prefix . 'short_name',
				'type'       => 'text',
				'before_row' => '<div id="anwp-tabs-general-sl_player_metabox" class="anwp-metabox-tabs__content-item">',
			]
		);

		// Full Name
		$cmb->add_field(
			[
				'name' => esc_html__( 'Full Name', 'sports-leagues' ),
				'id'   => $prefix . 'full_name',
				'type' => 'text',
			]
		);

		// Weight
		$cmb->add_field(
			[
				'name' => esc_html__( 'Weight', 'sports-leagues' ),
				'id'   => $prefix . 'weight',
				'type' => 'text',
			]
		);

		// Height
		$cmb->add_field(
			[
				'name' => esc_html__( 'Height', 'sports-leagues' ),
				'id'   => $prefix . 'height',
				'type' => 'text',
			]
		);

		// Position
		$cmb->add_field(
			[
				'name'             => esc_html__( 'Position', 'sports-leagues' ),
				'id'               => $prefix . 'position',
				'type'             => 'select',
				'show_option_none' => esc_html__( '- not selected -', 'sports-leagues' ),
				'options_cb'       => [ $this->plugin->config, 'get_player_positions' ],
			]
		);

		// Current Team
		$cmb->add_field(
			[
				'name'             => esc_html__( 'Current Team', 'sports-leagues' ),
				'id'               => $prefix . 'current_team',
				'type'             => 'select',
				'show_option_none' => esc_html__( '- not selected -', 'sports-leagues' ),
				'options_cb'       => [ $this->plugin->team, 'get_team_options' ],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'National Team', 'sports-leagues' ),
				'id'         => $prefix . 'national_team',
				'options_cb' => [ sports_leagues()->team, 'get_national_team_options' ],
				'type'       => 'anwp_sl_select',
			]
		);

		// Place of Birth
		$cmb->add_field(
			[
				'name' => esc_html__( 'Place of Birth', 'sports-leagues' ),
				'id'   => $prefix . 'place_of_birth',
				'type' => 'text',
			]
		);

		// Place of Birth
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Country of Birth', 'sports-leagues' ),
				'id'         => $prefix . 'country_of_birth',
				'type'       => 'anwp_sl_select',
				'options_cb' => [ $this->plugin->data, 'get_countries' ],
			]
		);

		// Nationality
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Nationality', 'sports-leagues' ),
				'id'         => $prefix . 'nationality',
				'type'       => 'anwp_sl_multiselect',
				'options_cb' => [ $this->plugin->data, 'get_countries' ],
			]
		);

		// Date of Birth
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Date of Birth', 'sports-leagues' ),
				'id'          => $prefix . 'date_of_birth',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
			]
		);

		// Date of death
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Date of death', 'sports-leagues' ),
				'id'          => $prefix . 'date_of_death',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
			]
		);

		$cmb->add_field(
			[
				'name'        => esc_html__( 'External ID', 'sports-leagues' ),
				'id'          => $prefix . 'player_external_id',
				'type'        => 'text',
				'description' => esc_html__( 'Used on Data Import', 'sports-leagues' ),
				'after_row'   => '</div>',
			]
		);

		// Player Bio
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Bio', 'sports-leagues' ),
				'id'         => $prefix . 'bio',
				'type'       => 'wysiwyg',
				'options'    => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_player_bio_input',
					'textarea_rows' => 10,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names' => false,
				'after_row'  => '</div>',
				'before_row' => '<div id="anwp-tabs-bio-sl_player_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		/*
		|--------------------------------------------------------------------
		| Gallery Tab
		|--------------------------------------------------------------------
		*/
		// Photo
		$cmb->add_field(
			[
				'name'         => esc_html__( 'Gallery', 'sports-leagues' ),
				'id'           => $prefix . 'gallery',
				'type'         => 'file_list',
				'options'      => [
					'url' => false,
				],
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'medium', // Image size to use when previewing in the admin.
				'before_row'   => '<div id="anwp-tabs-gallery-sl_player_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		// Notes
		$cmb->add_field(
			[
				'name'      => esc_html__( 'Text below gallery', 'sports-leagues' ),
				'id'        => $prefix . 'gallery_notes',
				'type'      => 'textarea_small',
				'after_row' => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------
		| Social Tab
		|--------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Twitter', 'sports-leagues' ),
				'id'         => $prefix . 'twitter',
				'before_row' => '<div id="anwp-tabs-social-sl_player_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'type'       => 'text_url',
				'protocols'  => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Facebook', 'sports-leagues' ),
				'id'        => $prefix . 'facebook',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'YouTube', 'sports-leagues' ),
				'id'        => $prefix . 'youtube',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'LinkedIn', 'sports-leagues' ),
				'id'        => $prefix . 'linkedin',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'TikTok', 'sports-leagues' ),
				'id'        => $prefix . 'tiktok',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'VKontakte', 'sports-leagues' ),
				'id'        => $prefix . 'vk',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Discord', 'sports-leagues' ),
				'id'        => $prefix . 'discord',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Twitch', 'sports-leagues' ),
				'id'        => $prefix . 'twitch',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Instagram', 'sports-leagues' ),
				'id'        => $prefix . 'instagram',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
				'after_row' => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------
		| Custom Fields
		|--------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Custom Fields', 'sports-leagues' ),
				'id'          => $prefix . 'custom_fields',
				'type'        => 'anwp_custom_fields',
				'option_slug' => 'player_custom_fields',
				'after_row'   => '</div>',
				'before_row'  => '<div id="anwp-tabs-custom_fields-sl_player_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Content', 'sports-leagues' ),
				'id'         => $prefix . 'bottom_content',
				'type'       => 'wysiwyg',
				'options'    => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_bottom_post_content',
					'textarea_rows' => 5,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names' => false,
				'after_row'  => '</div>',
				'before_row' => '<div id="anwp-tabs-bottom_content-sl_player_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.13
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/player', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'player_id' => 'ID' ] );
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {

		// Add new columns
		$new_columns = [
			'sl_player_photo'        => esc_html__( 'Photo', 'sports-leagues' ),
			'sl_player_birthdate'    => esc_html__( 'Date of Birth', 'sports-leagues' ),
			'sl_player_position'     => esc_html__( 'Position', 'sports-leagues' ),
			'sl_player_current_team' => esc_html__( 'Current Team', 'sports-leagues' ),
			'player_id'              => esc_html__( 'ID', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'sl_player_photo',
			'sl_player_birthdate',
			'sl_player_position',
			'sl_player_current_team',
			'comments',
			'date',
			'player_id',
		];

		$new_columns = [];

		foreach ( $new_columns_order as $c ) {

			if ( isset( $columns[ $c ] ) ) {
				$new_columns[ $c ] = $columns[ $c ];
			}
		}

		return $new_columns;
	}

	/**
	 * Handles admin column display.
	 *
	 * @since  0.1.0
	 *
	 * @param array   $column_name Column currently being rendered.
	 * @param integer $post_id     ID of post to display column for.
	 */
	public function columns_display( $column_name, $post_id ) {
		switch ( $column_name ) {

			case 'sl_player_position':
				echo esc_html( get_post_meta( $post_id, '_sl_position', true ) ? sports_leagues()->config->get_name_by_id( 'position', get_post_meta( $post_id, '_sl_position', true ) ) : '' );
				break;

			case 'sl_player_photo':
				echo get_the_post_thumbnail( $post_id, 'thumbnail' );
				break;

			case 'sl_player_birthdate':
				$birth_date = get_post_meta( $post_id, '_sl_date_of_birth', true );

				echo $birth_date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $birth_date ) ) ) : '';
				break;

			case 'sl_player_current_team':
				$team_id       = (int) get_post_meta( $post_id, '_sl_current_team', true );
				$teams_options = $this->plugin->team->get_team_options();

				if ( ! empty( $teams_options[ $team_id ] ) ) {
					echo esc_html( $teams_options[ $team_id ] );
				}

				break;

			case 'player_id':
				echo (int) $post_id;
				break;
		}
	}

	/**
	 * Get players and staff with upcoming birthdays.
	 *
	 * @param $options
	 *
	 * @return array
	 * @since 0.7.0
	 */
	public function get_birthdays( $options ) {
		global $wpdb;

		$cur_date = date_i18n( 'Y-m-d' );
		$options  = (object) wp_parse_args(
			$options,
			[
				'team_id'     => '',
				'type'        => 'players',
				'days_before' => 5,
				'days_after'  => 3,
			]
		);

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'SL-PLAYER_get_birthdays__' . $cur_date . '-' . md5( maybe_serialize( $options ) );

		if ( false !== sports_leagues()->cache->get( $cache_key, 'sl_player', false ) ) {
			return sports_leagues()->cache->get( $cache_key, 'sl_player' );
		}

		$query = "
		SELECT p.ID, pm2.meta_value current_team, pm1.meta_value date_of_birth, p.post_title player_name, p.post_type, DATE_FORMAT( pm1.meta_value, '%m-%d' ) meta_date_short
		FROM $wpdb->posts p
		LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key = '_sl_date_of_birth' )
		LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = p.ID AND pm2.meta_key = '_sl_current_team' )
		WHERE p.post_status = 'publish' AND pm1.meta_value IS NOT NULL AND pm1.meta_value != ''
		";

		/**==================
		 * WHERE filter by team_id
		 *================ */
		if ( absint( $options->team_id ) ) {
			$teams  = wp_parse_id_list( $options->team_id );
			$format = implode( ', ', array_fill( 0, count( $teams ), '%d' ) );

			$query .= $wpdb->prepare( " AND pm2.meta_value IN ({$format}) ", $teams ); // phpcs:ignore
		}

		/**==================
		 * WHERE filter by type
		 *================ */
		if ( 'all' === $options->type ) {
			$query .= ' AND ( p.post_type = "sl_player" OR p.post_type = "sl_staff" )';
		} elseif ( 'staff' === $options->type ) {
			$query .= ' AND p.post_type = "sl_staff"';
		} else {
			$query .= ' AND p.post_type = "sl_player"';
		}

		/**==================
		 * WHERE filter by date
		 *================ */
		$query .= $wpdb->prepare( ' AND pm1.meta_value >= DATE_SUB( DATE_SUB( CURDATE(), INTERVAL YEAR( CURDATE() ) - YEAR( pm1.meta_value ) YEAR ), INTERVAL %d DAY )', $options->days_before );
		$query .= $wpdb->prepare( ' AND pm1.meta_value <= DATE_ADD( DATE_SUB( CURDATE(), INTERVAL YEAR( CURDATE() ) - YEAR( pm1.meta_value ) YEAR ), INTERVAL %d DAY )', $options->days_after );

		$query .= ' GROUP BY p.ID';
		$query .= ' ORDER BY meta_date_short';

		/**==================
		 * Bump Query
		 *================ */
		$players = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		// Populate Object Cache
		$ids = wp_list_pluck( $players, 'ID' );

		if ( ! empty( $ids ) && is_array( $ids ) ) {
			$players_posts = [];

			$args = [
				'include'   => $ids,
				'post_type' => [ 'sl_player', 'sl_staff' ],
			];

			/** @var WP_Post $player_post */
			foreach ( get_posts( $args ) as $player_post ) {
				$players_posts[ $player_post->ID ] = $player_post;
			}

			$player_photos = $this->get_player_photo_map();
			$staff_photos  = sports_leagues()->staff->get_staff_photo_map();
			$positions_map = $this->get_positions_map();

			// Add extra data to players
			foreach ( $players as $player ) {
				$player->permalink = get_permalink( isset( $players_posts[ $player->ID ] ) ? $players_posts[ $player->ID ] : $player->ID );

				if ( 'sl_staff' === $player->post_type ) {
					$player->photo = empty( $staff_photos[ $player->ID ] ) ? '' : $staff_photos[ $player->ID ];
				} else {
					$player->photo = empty( $player_photos[ $player->ID ] ) ? '' : $player_photos[ $player->ID ];
				}

				$player->position = isset( $positions_map[ $player->ID ] ) ? $positions_map[ $player->ID ] : '';
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( is_array( $players ) ) {
			sports_leagues()->cache->set( $cache_key, $players, 'sl_player' );
		}

		return $players;
	}

	/**
	 * Get all player games for selected season.
	 *
	 * @param int $player_id
	 * @param int $season_id
	 *
	 * @return array
	 * @since 0.9.3
	 */
	public function get_player_games( $player_id, $season_id ) {

		global $wpdb;

		return $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT a.game_id
					FROM {$wpdb->prefix}sl_player_statistics a
					LEFT JOIN {$wpdb->prefix}sl_games b ON a.game_id = b.game_id
					WHERE a.c_id__0 = 1 AND a.player_id = %d AND b.season_id = %d
					ORDER BY b.kickoff DESC
				",
				$player_id,
				$season_id
			)
		);
	}

	/**
	 * Get player and staff position map.
	 *
	 * @since 0.10.2
	 * @return array $output
	 */
	public function get_positions_map() {

		static $output = null;

		if ( null === $output ) {
			$output = [];

			global $wpdb;

			$rows = $wpdb->get_results(
				"
					SELECT post_id, meta_value
					FROM $wpdb->postmeta
					WHERE ( meta_key = '_sl_job_title' OR meta_key = '_sl_position' ) AND meta_value != ''
				"
			);

			if ( empty( $rows ) ) {
				return [];
			}

			foreach ( $rows as $row ) {
				$output[ $row->post_id ] = $row->meta_value;
			}
		}

		return $output;
	}

	/**
	 * Get Post ID by External id
	 *
	 * @param $external_id
	 *
	 * @return string|null
	 * @since 0.10.2
	 */
	public function get_player_id_by_external_id( $external_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_sl_player_external_id' AND meta_value = %s
				",
				$external_id
			)
		);
	}

	/**
	 * Get serialized data for the Stats Players
	 *
	 * @param $data
	 *
	 * @return string
	 * @since 0.12.4
	 */
	public function get_serialized_stat_players_data( $data ) {

		$default_data = [
			'stats_id'          => '',
			'game_id'           => '',
			'team_id'           => '',
			'tournament_id'     => '',
			'stage_id'          => '',
			'league_id'         => '',
			'season_id'         => '',
			'group_id'          => '',
			'round_id'          => '',
			'venue_id'          => '',
			'game_day'          => '',
			'order'             => '',
			'position'          => '',
			'limit'             => 0,
			'show_position'     => 1,
			'show_team'         => 1,
			'show_nationality'  => 1,
			'show_photo'        => 1,
			'show_games_played' => 0,
			'link_to_profile'   => 0,
			'context'           => 'shortcode',
		];

		return wp_json_encode( array_intersect_key( wp_parse_args( $data, $default_data ), $default_data ) );
	}

	/**
	 * Get Players Stat (Single value)
	 *
	 * @since 0.12.4
	 */
	public function get_stat_players( WP_REST_Request $request ) {

		$args = $request->get_params();

		// Sanitize and validate
		$data = (object) [
			'stats_id'          => empty( $args['stats_id'] ) ? '' : sanitize_text_field( $args['stats_id'] ),
			'game_id'           => empty( $args['game_id'] ) ? '' : absint( $args['game_id'] ),
			'team_id'           => empty( $args['team_id'] ) ? '' : absint( $args['team_id'] ),
			'tournament_id'     => empty( $args['tournament_id'] ) ? '' : absint( $args['tournament_id'] ),
			'stage_id'          => empty( $args['stage_id'] ) ? '' : absint( $args['stage_id'] ),
			'league_id'         => empty( $args['league_id'] ) ? '' : absint( $args['league_id'] ),
			'season_id'         => empty( $args['season_id'] ) ? '' : absint( $args['season_id'] ),
			'group_id'          => empty( $args['group_id'] ) ? '' : absint( $args['group_id'] ),
			'round_id'          => empty( $args['round_id'] ) ? '' : absint( $args['round_id'] ),
			'venue_id'          => empty( $args['venue_id'] ) ? '' : absint( $args['venue_id'] ),
			'game_day'          => empty( $args['game_day'] ) ? '' : absint( $args['game_day'] ),
			'order'             => empty( $args['order'] ) ? '' : sanitize_key( $args['order'] ),
			'position'          => empty( $args['position'] ) ? '' : sanitize_text_field( $args['position'] ),
			'link_to_profile'   => empty( $args['link_to_profile'] ) ? 0 : absint( $args['link_to_profile'] ),
			'show_team'         => empty( $args['show_team'] ) ? 0 : absint( $args['show_team'] ),
			'show_nationality'  => empty( $args['show_nationality'] ) ? 0 : absint( $args['show_nationality'] ),
			'show_position'     => empty( $args['show_position'] ) ? 0 : absint( $args['show_position'] ),
			'show_photo'        => empty( $args['show_photo'] ) ? 0 : absint( $args['show_photo'] ),
			'show_games_played' => empty( $args['show_games_played'] ) ? 0 : absint( $args['show_games_played'] ),
			'games_played'      => empty( $args['games_played'] ) ? 0 : absint( $args['games_played'] ),
			'soft_limit'        => 0,
			'limit'             => 21,
			'offset'            => empty( $args['loaded'] ) ? 0 : absint( $args['loaded'] ),
		];

		// Try to get from cache
		$cache_key = 'SL-SHORTCODE_players-stats__' . md5( maybe_serialize( $data ) );

		if ( sports_leagues()->cache->get( $cache_key, 'sl_game' ) ) {
			$stat_rows = sports_leagues()->cache->get( $cache_key, 'sl_game' );
		} else {
			// Load data in default way
			$stat_rows = sports_leagues()->player_stats->get_players_aggregate_stats( $data );

			// Save transient
			if ( ! empty( $players ) ) {
				sports_leagues()->cache->set( $cache_key, $players, 'sl_game' );
			}
		}

		// Check next time "load more"
		$next_load = count( $stat_rows ) > 20;

		if ( $next_load ) {
			array_pop( $stat_rows );
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Output
		|--------------------------------------------------------------------
		*/
		$stats_column     = sports_leagues()->player_stats->get_stats_player_game_column_by_id( $data->stats_id );
		$default_photo    = sports_leagues()->helper->get_default_player_photo();
		$player_photo_map = sports_leagues()->player->get_player_photo_map();

		// Start output
		ob_start();

		foreach ( $stat_rows as $row_index => $player_stat ) {
			$index = $row_index + $data->offset;
			?>
			<div class="d-flex align-items-center py-1 px-2 <?php echo esc_attr( $index > 0 ? 'anwp-border-top anwp-border-light' : '' ); ?>">
				<div class="stat-players__place anwp-w-30 pr-2 anwp-text-center flex-shrink-0"><?php echo absint( $index + 1 ); ?></div>

				<?php
				if ( Sports_Leagues::string_to_bool( $data->show_photo ) ) :
					$player_photo = $default_photo;

					if ( $player_stat->thumbnail_id ) {
						$player_photo = isset( $player_photo_map[ $player_stat->ID ] ) ? $player_photo_map[ $player_stat->ID ] : '';
					}
					?>
					<div class="player-list__photo position-relative player-photo__wrapper anwp-text-center mr-1 anwp-flex-none">
						<img
							alt="<?php echo esc_html( $player_stat->player_name ); ?>"
							class="anwp-object-contain anwp-w-40 anwp-h-40 mb-0"
							src="<?php echo esc_url( $player_photo ); ?>">
					</div>
					<?php
				endif;

				if ( Sports_Leagues::string_to_bool( $data->show_team ) ) :
					?>
					<div class="stat-players__clubs my-n1 anwp-flex-none d-flex mr-2">
						<?php
						foreach ( explode( ',', $player_stat->teams ) as $team ) :
							$team_obj = sports_leagues()->team->get_team_by_id( $team );

							if ( $team_obj && $team_obj->logo ) :
								?>
								<img
									alt="<?php echo esc_attr( $team_obj->title ); ?>"
									class="anwp-w-30 anwp-h-30 anwp-object-contain mr-1 mb-0" src="<?php echo esc_url( $team_obj->logo ); ?>"
									data-toggle="anwp-sl-tooltip" data-tippy-content="<?php echo esc_attr( $team_obj->title ); ?>">
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="stat-players__name pr-2 mr-auto anwp-leading-1-25">
					<?php if ( Sports_Leagues::string_to_bool( $data->link_to_profile ) && ! empty( $player_stat->link ) ) : ?>
						<a class="text-decoration-none anwp-link-without-effects" href="<?php echo esc_attr( $player_stat->link ); ?>">
							<?php echo esc_html( $player_stat->player_name ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $player_stat->player_name ); ?>
					<?php endif; ?>
				</div>

				<?php if ( Sports_Leagues::string_to_bool( $data->show_games_played ) ) : ?>
					<div class="stat-players__gp mr-3 anwp-opacity-60">(<?php echo esc_html( $player_stat->gp ); ?>)</div>
				<?php endif; ?>

				<div class="stat-players__stat ml-2">
					<?php if ( 'simple' === $stats_column->type ) : ?>
						<?php echo esc_html( $player_stat->qty ); ?>
					<?php elseif ( 'time' === $stats_column->type ) : ?>
						<div class="d-flex">
							<span class="player-list__stat-minutes"><?php echo esc_html( absint( $player_stat->qty / 3600 ) ); ?></span>
							<span class="player-list__stat-seconds"><?php echo esc_html( date( ':s', mktime( 0, 0, $player_stat->qty / 60 ) ) ); ?></span>
						</div>
					<?php elseif ( 'calculated' === $stats_column->type ) : ?>

						<?php if ( ! empty( $stats_column->prefix ) ) : ?>
							<span class="player-list__stat-prefix"><?php echo esc_html( $stats_column->prefix ); ?></span>
						<?php endif; ?>

						<?php echo esc_html( number_format( $player_stat->qty, absint( $stats_column->digits ), '.', '' ) ); ?>

						<?php if ( ! empty( $stats_column->postfix ) ) : ?>
							<span class="player-list__stat-prefix"><?php echo esc_html( $stats_column->postfix ); ?></span>
						<?php endif; ?>

					<?php endif; ?>
				</div>
			</div>
			<?php
		}

		$html_output = ob_get_clean();

		return rest_ensure_response(
			[
				'html'   => $html_output,
				'next'   => $next_load,
				'offset' => $data->offset + count( $stat_rows ),
			]
		);
	}
}
