<?php
/**
 * Sports Leagues :: Standing.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Standing {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * @since  0.1.0
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save core plugin to var
		$this->plugin = $plugin;

		// Register CPT
		$this->register_post_type();

		// Run hooks
		$this->hooks();
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 0.1.0
	 */
	public function register_post_type() {

		// Register this CPT.
		$labels = [
			'name'               => _x( 'Standing', 'Post type general name', 'sports-leagues' ),
			'singular_name'      => _x( 'Standing', 'Post type singular name', 'sports-leagues' ),
			'menu_name'          => _x( 'Standings', 'Admin Menu text', 'sports-leagues' ),
			'name_admin_bar'     => _x( 'Standing', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'            => __( 'Add New', 'sports-leagues' ),
			'add_new_item'       => __( 'Add New Standing', 'sports-leagues' ),
			'new_item'           => __( 'New Standing', 'sports-leagues' ),
			'edit_item'          => __( 'Edit Standing', 'sports-leagues' ),
			'view_item'          => __( 'View Standing', 'sports-leagues' ),
			'all_items'          => __( 'Standings', 'sports-leagues' ),
			'search_items'       => __( 'Search Standings', 'sports-leagues' ),
			'not_found'          => __( 'No Standings found.', 'sports-leagues' ),
			'not_found_in_trash' => __( 'No Standings found in Trash.', 'sports-leagues' ),
		];

		$args = [
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'rewrite'            => false,
			'show_in_admin_bar'  => false,
			'show_ui'            => true,
			'supports'           => false,
			'menu_icon'          => 'dashicons-editor-ol',
			'menu_position'      => 45,
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

		register_post_type( 'sl_standing', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Init metabox
		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );

		add_action( 'save_post_sl_standing', [ $this, 'save_metabox' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Display recalculated notice
		add_action( 'admin_notices', [ $this, 'display_admin_standing_notice' ] );

		// Modifies columns in Admin tables
		add_action( 'manage_sl_standing_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_standing_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-sl_standing_sortable_columns', [ $this, 'sortable_columns' ] );

		add_filter( 'sports-leagues/tmpl-standing/columns_order', [ $this, 'change_columns_order' ], 10, 3 );

		// Create CMB2 Metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );

		add_action( 'cmb2_before_post_form_sl_cmb2_standing_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_post_form_sl_cmb2_standing_metabox', [ $this, 'cmb2_after_metabox' ] );

		// Admin Table filters
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'add_more_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );

		add_action( 'admin_menu', [ $this, 'register_settings_menu' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		add_filter(
			'list_table_primary_column',
			function ( $default, $screen_id ) {
				if ( 'edit-sl_standing' === $screen_id ) {
					return 'sl_standing_info';
				}

				return $default;
			},
			10,
			2
		);

		add_action( 'add_meta_boxes_sl_standing', [ $this, 'remove_term_metaboxes' ] );
	}

	/**
	 * Register Settings Menu
	 *
	 * @return void
	 */
	public function register_settings_menu() {
		add_submenu_page(
			'edit.php?post_type=sl_standing',
			esc_html__( 'Settings', 'sports-leagues' ),
			esc_html__( 'Settings', 'sports-leagues' ),
			'manage_options',
			'sl-standing-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Rendering Settings page
	 */
	public function render_settings_page() {

		// Check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		if ( apply_filters( 'sports-leagues/standing-settings/render_core_settings_page', true ) ) {
			Sports_Leagues::include_file( 'admin/views/standing-settings' );
		}

		do_action( 'sports-leagues/standing-settings/render_settings_page' );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.12.2
	 */
	public function add_rest_routes() {
		register_rest_route(
			'sports-leagues/v1',
			'/settings/save_standing-settings/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_settings' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Save settings.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function save_settings( WP_REST_Request $request ) {

		$params = $request->get_params();

		$saved_data = [
			'ranking_rules'      => 'sl_standing_settings__ranking',
			'columns_order'      => 'sl_standing_settings__col_order',
			'columns_mini_order' => 'sl_standing_settings__col_order_mini',
		];

		foreach ( $saved_data as $param_key => $param_option ) {
			$saved_config = isset( $params[ $param_key ] ) ? $this->plugin->helper->recursive_sanitize( $params[ $param_key ] ) : [];
			$old_settings = get_option( $param_option, [] );

			if ( $saved_config === $old_settings || maybe_serialize( $saved_config ) === maybe_serialize( $old_settings ) ) {
				continue;
			}

			if ( ! update_option( $param_option, $saved_config, true ) ) {
				return new WP_Error( 'rest_invalid', 'Update Problem', [ 'status' => 400 ] );
			}
		}

		return rest_ensure_response( [ 'result' => 'Saved Successfully' ] );
	}

	/**
	 * Handle clone standing action.
	 *
	 * @param int $standing_id
	 * @param int $clone_id
	 *
	 * @since 0.9.0
	 */
	public function clone_standing( $standing_id, $clone_id ) {

		if ( ! current_user_can( 'edit_post', $standing_id ) ) {
			return;
		}

		if ( ! absint( $standing_id ) || ! absint( $clone_id ) ) {
			return;
		}

		$meta_fields_to_clone = [
			'_sl_points_initial',
			'_sl_table_colors',
			'_sl_ranking_rules_current',
			'_sl_manual_ordering',
			'_sl_columns_order',
			'_sl_columns_mini_order',
			'_sl_table_notes',
		];

		/**
		 * Filter Standing Data to clone
		 *
		 * @param array $meta_fields_to_clone Clone data
		 * @param int   $post_id              Standing ID
		 * @param int   $standing_id          New Cloned Standing ID
		 *
		 * @since 0.9.0
		 */
		$meta_fields_to_clone = apply_filters( 'sports-leagues/standing/fields_to_clone', $meta_fields_to_clone, $clone_id, $standing_id );

		foreach ( $meta_fields_to_clone as $meta_key ) {

			$meta_value = get_post_meta( $clone_id, $meta_key, true );

			if ( '' !== $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				update_post_meta( $standing_id, $meta_key, wp_slash( $meta_value ) );
			}
		}

		update_post_meta( $standing_id, '_sl_cloned', $clone_id );
	}


	/**
	 * Filters whether to remove the 'Months' drop-down from the post list table.
	 *
	 * @param bool   $disable   Whether to disable the drop-down. Default false.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 */
	public function disable_months_dropdown( $disable, $post_type ) {

		return 'sl_standing' === $post_type ? true : $disable;
	}

	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @param string $post_type The post type slug.
	 */
	public function add_more_filters( $post_type ) {

		if ( 'sl_standing' === $post_type ) {

			ob_start();

			/*
			|--------------------------------------------------------------------
			| Filter By League
			|--------------------------------------------------------------------
			*/
			$leagues = get_terms(
				[
					'taxonomy'   => 'sl_league',
					'hide_empty' => false,
				]
			);

			if ( ! is_wp_error( $leagues ) && ! empty( $leagues ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$current_league_filter = empty( $_GET['_sl_current_league'] ) ? '' : absint( $_GET['_sl_current_league'] );
				?>

				<select name='_sl_current_league' id='anwp_league_filter' class='postform'>
					<option value=''><?php echo esc_html__( 'All Leagues', 'sports-leagues' ); ?></option>
					<?php foreach ( $leagues as $league ) : ?>
						<option value="<?php echo esc_attr( $league->term_id ); ?>" <?php selected( $league->term_id, $current_league_filter ); ?>>
							- <?php echo esc_html( $league->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			/*
			|--------------------------------------------------------------------
			| Filter By Season
			|--------------------------------------------------------------------
			*/
			$seasons = get_terms(
				[
					'taxonomy'   => 'sl_season',
					'hide_empty' => false,
				]
			);

			if ( ! is_wp_error( $seasons ) && ! empty( $seasons ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$current_season_filter = empty( $_GET['_sl_current_season'] ) ? '' : absint( $_GET['_sl_current_season'] );
				?>

				<select name='_sl_current_season' id='anwp_season_filter' class='postform'>
					<option value=''><?php echo esc_html__( 'All Seasons', 'sports-leagues' ); ?></option>
					<?php foreach ( $seasons as $season ) : ?>
						<option value="<?php echo esc_attr( $season->term_id ); ?>" <?php selected( $season->term_id, $current_season_filter ); ?>>
							- <?php echo esc_html( $season->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handle custom filter.
	 *
	 * @param WP_Query $query
	 */
	public function handle_custom_filter( $query ) {
		global $post_type, $pagenow;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'edit.php' !== $pagenow || 'sl_standing' !== $post_type ) {
			return;
		}

		$sub_query = [];

		/*
		|--------------------------------------------------------------------
		| Filter By Season
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_season = empty( $_GET['_sl_current_season'] ) ? '' : intval( $_GET['_sl_current_season'] );

		if ( $filter_by_season ) {

			$season_args_1 = [
				'numberposts' => - 1,
				'fields'      => 'ids',
				'post_type'   => 'sl_tournament',
				'post_status' => [ 'publish' ],
				'tax_query'   => [
					[
						'taxonomy' => 'sl_season',
						'field'    => 'id',
						'terms'    => [ $filter_by_season ],
					],
				],
			];

			$season_tournament_ids = get_posts( $season_args_1 );

			$season_args_2 = [
				'numberposts'     => - 1,
				'fields'          => 'ids',
				'post_type'       => 'sl_tournament',
				'post_status'     => [ 'publish' ],
				'post_parent__in' => $season_tournament_ids,
			];

			$season_stage_ids = get_posts( $season_args_2 );

			$sub_query[] =
				[
					'key'     => '_sl_stage_id',
					'value'   => $season_stage_ids,
					'compare' => 'IN',
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By League
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_league = empty( $_GET['_sl_current_league'] ) ? '' : intval( $_GET['_sl_current_league'] );

		if ( $filter_by_league ) {

			$league_args_1 = [
				'numberposts' => - 1,
				'fields'      => 'ids',
				'post_type'   => 'sl_tournament',
				'post_status' => [ 'publish' ],
				'tax_query'   => [
					[
						'taxonomy' => 'sl_league',
						'field'    => 'id',
						'terms'    => [ $filter_by_league ],
					],
				],
			];

			$league_tournament_ids = get_posts( $league_args_1 );

			$league_args_2 = [
				'numberposts'     => - 1,
				'fields'          => 'ids',
				'post_type'       => 'sl_tournament',
				'post_status'     => [ 'publish' ],
				'post_parent__in' => $league_tournament_ids,
			];

			$league_stage_ids = get_posts( $league_args_2 );

			$sub_query[] =
				[
					'key'   => '_sl_stage_id',
					'value' => $league_stage_ids,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Join All values to main query
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $sub_query ) ) {
			$query->set(
				'meta_query',
				[
					array_merge( [ 'relation' => 'AND' ], $sub_query ),
				]
			);
		}
	}

	/**
	 * Change order for standing columns.
	 *
	 * @param array  $columns
	 * @param int    $standing_id Standing ID
	 * @param string $layout      Standing ID
	 *
	 * @return array
	 * @since 0.5.2
	 */
	public function change_columns_order( $columns, $standing_id, $layout ) {

		$meta_field    = 'mini' === $layout ? '_sl_columns_mini_order' : '_sl_columns_order';
		$columns_order = json_decode( get_post_meta( $standing_id, $meta_field, true ) );

		if ( $columns_order && is_array( $columns_order ) ) {

			$columns = [];

			foreach ( $columns_order as $col ) {
				if ( Sports_Leagues::string_to_bool( $col->display ) ) {
					$columns[] = $col->slug;
				}
			}
		}

		return $columns;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'standing_id' => 'ID' ] );
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
			'sl_standing_info'   => esc_html__( 'Tournament', 'sports-leagues' ),
			'sl_standing_logo'   => esc_html__( 'Logo', 'sports-leagues' ),
			'sl_standing_stage'  => esc_html__( 'Stage', 'sports-leagues' ),
			'sl_standing_group'  => esc_html__( 'Group', 'sports-leagues' ),
			'sl_standing_season' => esc_html__( 'Season', 'sports-leagues' ),
			'sl_standing_teams'  => esc_html__( 'Teams', 'sports-leagues' ),
			'standing_id'        => esc_html__( 'ID', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'sl_standing_info',
			'sl_standing_logo',
			'sl_standing_stage',
			'sl_standing_group',
			'sl_standing_season',
			'sl_standing_teams',
			'standing_id',
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
	 * @param array   $column   Column currently being rendered.
	 * @param integer $post_id  ID of post to display column for.
	 *
	 * @since  0.1.0
	 */
	public function columns_display( $column, $post_id ) {

		$standing_info  = $this->get_standing_info( $post_id );
		$tournament_obj = sports_leagues()->tournament->get_tournament( $standing_info['tournament_id'] );

		switch ( $column ) {

			case 'sl_standing_info':
				if ( ! empty( $standing_info['tournament'] ) ) {
					echo esc_html( $standing_info['tournament'] );
				}

				$clone_link = admin_url( 'post-new.php?post_type=sl_standing&clone_id=' . intval( $post_id ) );
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="' . esc_url( $clone_link ) . '">' . esc_html__( 'Clone', 'sports-leagues' ) . '</a>';

				break;

			case 'sl_standing_logo':
				if ( ! empty( $tournament_obj->logo ) ) {
					echo get_the_post_thumbnail( $standing_info['tournament_id'], 'thumbnail' );
				}

				break;

			case 'sl_standing_stage':
				// Stage
				if ( ! empty( $standing_info['stage'] ) ) {
					echo esc_html( $standing_info['stage'] ) . ' (ID:' . absint( $standing_info['stage_id'] ) . ')';
				}

				// Round
				if ( ! empty( $standing_info['round'] ) ) {
					echo '<br>' . esc_html__( 'Round', 'sports-leagues' ) . ': ' . esc_html( $standing_info['round'] );
				}

				break;

			case 'sl_standing_group':
				// Group
				if ( ! empty( $standing_info['group_id'] ) ) {
					echo esc_html( $standing_info['group'] ) . ' (ID:' . absint( $standing_info['group_id'] ) . ')';
				} else {
					?>
					<svg class="anwp-icon anwp-icon--octi anwp-icon--s24" style="color: #cb0404; fill: currentColor;">
						<use xlink:href="#icon-alert"></use>
					</svg>
					<div style="color: #cb0404;">
						<?php echo esc_html__( "Standing Table's group not exists", 'sports-leagues' ); ?>
					</div>
					<?php
				}

				break;

			case 'sl_standing_season':
				if ( ! empty( $tournament_obj->season_text ) ) {
					echo esc_html( $tournament_obj->season_text );
				}

				break;

			case 'sl_standing_teams':
				$teams_ids = $this->plugin->tournament->get_stage_teams( $standing_info['stage_id'], $standing_info['group_id'] );

				if ( ! empty( $teams_ids ) ) {
					?>
					<div class="p-2" style="display: flex; align-items: center; flex-wrap: wrap;">
						<?php
						foreach ( $teams_ids as $team_id ) :
							$team_obj = sports_leagues()->team->get_team_by_id( $team_id );

							if ( empty( $team_obj ) ) {
								continue;
							}
							?>
							<div style="display: flex; align-items: center; border-right: 1px solid #bbb; margin-bottom: 5px; margin-right: 8px; padding-right: 8px;">
								<?php if ( $team_obj->logo ) : ?>
									<img src="<?php echo esc_html( $team_obj->logo ); ?>" alt="" width="20" height="20" style="object-fit: contain; margin-right: 5px;">
								<?php endif; ?>
								<?php echo esc_html( $team_obj->title ); ?>
							</div>
						<?php endforeach; ?>
					</div>
					<?php
				}

				break;

			case 'standing_id':
				echo (int) $post_id;

				break;

		}
	}

	/**
	 * Get list of Standings.
	 *
	 * @return array
	 * @since 0.5.11
	 */
	public function get_standing_options() {

		static $options = null;

		if ( null === $options ) {

			$options = [];

			$posts = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_standing',
				]
			);

			/** @var WP_Post $post */
			foreach ( $posts as $post ) {
				$options[ $post->ID ] = $this->get_standing_title( $post->ID );
			}

			asort( $options );
		}

		return $options;
	}

	/**
	 * Get list of Standings.
	 *
	 * @return array
	 * @since 0.5.11
	 */
	public function get_standings() {

		static $options = null;

		if ( null === $options ) {

			$options = [];

			$posts = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_standing',
				]
			);

			/** @var WP_Post $post */
			foreach ( $posts as $post ) {
				$options[] = [
					'id'       => $post->ID,
					'stage_id' => get_post_meta( $post->ID, '_sl_stage_id', true ),
					'group_id' => get_post_meta( $post->ID, '_sl_group_id', true ),
				];
			}
		}

		return $options;
	}

	/**
	 * Returns recommended standing title.
	 *
	 * @param int $standing_id
	 *
	 * @return string
	 * @since 0.1.0
	 */
	public function get_standing_title( $standing_id ) {

		$data = [];

		foreach ( $this->get_standing_info( $standing_id ) as $standing_prop => $standing_info ) {
			if ( ! empty( $standing_info ) && ! in_array( $standing_prop, [ 'stage_id', 'group_id', 'tournament_id' ], true ) ) {
				$data[] = $standing_info;
			}
		}

		return implode( ' - ', $data );
	}

	/**
	 * Returns array of standing info.
	 *
	 * @param int $standing_id
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_standing_info( $standing_id ) {
		$output = [
			'tournament' => '',
			'stage'      => '',
			'stage_id'   => '',
			'round'      => '',
			'group'      => '',
			'group_id'   => '',
		];

		$group_id = (int) get_post_meta( $standing_id, '_sl_group_id', true );
		$stage_id = (int) get_post_meta( $standing_id, '_sl_stage_id', true );

		if ( empty( $group_id ) || empty( $stage_id ) ) {
			return $output;
		}

		$stage = get_post( $stage_id );

		if ( ! $stage instanceof WP_Post ) {
			return $output;
		}

		// Set Stage
		$output['stage']    = $stage->post_title;
		$output['stage_id'] = $stage_id;

		// Set Tournament title (parent)
		$output['tournament']    = $stage->post_parent ? get_the_title( $stage->post_parent ) : '';
		$output['tournament_id'] = $stage->post_parent ?: '';

		// Parse groups & rounds
		$groups   = json_decode( get_post_meta( $stage_id, '_sl_groups', true ) );
		$round_id = '';

		if ( is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				if ( $group->id === $group_id ) {
					$output['group']    = isset( $group->title ) ? $group->title : '';
					$output['group_id'] = $group_id;
					$round_id           = isset( $group->round ) ? $group->round : '';
				}
			}
		}

		if ( intval( $round_id ) ) {
			$rounds = json_decode( get_post_meta( $stage_id, '_sl_rounds', true ) );

			if ( is_array( $rounds ) && count( $rounds ) > 1 ) {
				foreach ( $rounds as $round ) {
					if ( $round->id === $round_id ) {
						$output['round'] = isset( $round->title ) ? $round->title : '';
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.1.0
	 */
	public function init_metaboxes() {
		add_action(
			'add_meta_boxes',
			function ( $post_type ) {
				if ( 'sl_standing' === $post_type ) {
					add_meta_box(
						'sl_standing_metabox',
						__( 'Standing', 'sports-leagues' ),
						[ $this, 'render_metabox' ],
						$post_type,
						'normal',
						'high'
					);
				}
			}
		);
	}

	/**
	 * Render Meta Box content for Competition Stages.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.1.0
	 */
	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		$app_id = apply_filters( 'sports-leagues/standing/vue_app_id', 'sl-app-standing', $post->ID );

		if ( 'yes' === get_post_meta( $post->ID, '_sl_fixed', true ) ) :

			$standing_data = $this->get_standing_info( $post->ID );

			// Get Standing Teams
			$group_id         = (int) get_post_meta( $post->ID, '_sl_group_id', true );
			$stage_id         = (int) get_post_meta( $post->ID, '_sl_stage_id', true );
			$teams_ids        = $this->plugin->tournament->get_stage_teams( $stage_id, $group_id );
			$teams            = [];
			$teams_partitions = [];

			foreach ( $teams_ids as $team_id ) {
				$team_name = $this->plugin->team->get_team_title_by_id( $team_id );

				if ( $team_name ) {
					$teams[ $team_id ] = $team_name;

					$teams_partitions[ $team_id ] = [
						'd' => get_post_meta( $team_id, '_sl_division', true ),
						'c' => get_post_meta( $team_id, '_sl_conference', true ),
					];
				}
			}

			// Get available ranking rules
			$rules_available = [
				'all_win',
				'ft_win',
				'ov_win',
				'pen_win',
				'draw',
				'sf',
				'sd',
				'ratio',
				'pts',
			];

			// Config Points
			$config_points = [
				'ft_win'   => Sports_Leagues_Config::get_value( 'points_ft_win', '-' ),
				'ov_win'   => Sports_Leagues_Config::get_value( 'points_ov_win', '-' ),
				'pen_win'  => Sports_Leagues_Config::get_value( 'points_pen_win', '-' ),
				'draw'     => Sports_Leagues_Config::get_value( 'points_draw', '-' ),
				'pen_loss' => Sports_Leagues_Config::get_value( 'points_pen_loss', '-' ),
				'ov_loss'  => Sports_Leagues_Config::get_value( 'points_ov_loss', '-' ),
				'ft_loss'  => Sports_Leagues_Config::get_value( 'points_ft_loss', '-' ),
			];
			?>
			<script type="text/javascript">
				var _slStandingTeams                   = <?php echo wp_json_encode( $teams ); ?>;
				var _slStandingTeamsPartitions         = <?php echo wp_json_encode( $teams_partitions ); ?>;
				var _slStandingRulesAvailable          = <?php echo wp_json_encode( $rules_available ); ?>;
				var _slConfigPoints                    = <?php echo wp_json_encode( $config_points ); ?>;
				var _slStandingDefaultColumnsOrder     = <?php echo wp_json_encode( get_option( 'sl_standing_settings__col_order' ) ); ?>;
				var _slStandingDefaultColumnsOrderMini = <?php echo wp_json_encode( get_option( 'sl_standing_settings__col_order_mini' ) ); ?>;
				var _slStandingDefaultRules            = <?php echo wp_json_encode( get_option( 'sl_standing_settings__ranking' ) ); ?>;
			</script>
			<div class="sl-standing-metabox-wrapper anwp-b-wrap">

				<?php
				/**
				 * Before Standing edit form
				 *
				 * @param int $post WP_Post
				 *
				 * @since 0.9.3
				 */
				do_action( 'sports-leagues/standing/above_edit_form', $post );
				?>

				<div class="anwp-border anwp-border-gray-500">
					<div class="anwp-border-bottom anwp-border-gray-500 bg-white d-flex align-items-center px-3 py-2 anwp-text-gray-700 anwp-font-semibold">
						<svg class="anwp-icon anwp-icon--s16 mr-2">
							<use xlink:href="#icon-home"></use>
						</svg>
						<?php esc_html_e( 'General Structure', 'sports-leagues' ); ?>
					</div>
					<div class="bg-white p-2">

						<div class="anwp-sl-admin-metabox__row form-row p-0">
							<div class="anwp-sl-admin-metabox__col col-auto my-2 mr-4">
								<label><?php esc_html_e( 'Tournament', 'sports-leagues' ); ?></label>
								<div class="input-group h5"><?php echo esc_html( $standing_data['tournament'] ); ?></div>
							</div>

							<div class="anwp-sl-admin-metabox__col col-auto my-2 mr-4">
								<label><?php esc_html_e( 'Stage', 'sports-leagues' ); ?></label>
								<div class="input-group h5"><?php echo esc_html( $standing_data['stage'] ); ?></div>
							</div>

							<?php if ( $standing_data['round'] ) : ?>
								<div class="anwp-sl-admin-metabox__col col-auto my-2 mr-4">
									<label><?php esc_html_e( 'Round', 'sports-leagues' ); ?></label>
									<div class="input-group h5"><?php echo esc_html( $standing_data['round'] ); ?></div>
								</div>
							<?php endif; ?>

							<div class="anwp-sl-admin-metabox__col col-auto my-2">
								<label><?php esc_html_e( 'Group', 'sports-leagues' ); ?></label>
								<div class="input-group h5"><?php echo esc_html( $standing_data['group'] ); ?></div>
							</div>
						</div>

					</div>
				</div>

				<div class="anwp-sl-admin-metabox mt-4">
					<div class="anwp-border-bottom anwp-border-gray-500 bg-white d-flex align-items-center px-3 py-2 anwp-text-gray-700 anwp-font-semibold">
						<svg class="anwp-icon anwp-icon--s16 mr-2">
							<use xlink:href="#icon-database"></use>
						</svg>
						<?php esc_html_e( 'Teams', 'sports-leagues' ); ?>
					</div>
					<div class="bg-white p-2 d-flex align-items-center flex-wrap">
						<?php
						foreach ( $teams_ids as $team_id ) :
							$team_obj = sports_leagues()->team->get_team_by_id( $team_id );
							?>
							<div class="d-inline-block px-2 mb-1 border-right d-flex align-items-center">
								<?php if ( $team_obj->logo ) : ?>
									<img src="<?php echo esc_html( $team_obj->logo ); ?>" alt="" class="anwp-w-30 anwp-h-30 anwp-object-contain mr-2">
								<?php endif; ?>
								<?php echo esc_html( $team_obj->title ); ?>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="anwp-sl-admin-metabox__footer py-2 px-3 d-flex align-items-center">
						<svg class="anwp-icon mr-1"><use xlink:href="#icon-info"></use></svg>
						<a href="<?php echo esc_url( get_edit_post_link( get_post_parent( $stage_id ) ) ); ?>" target="_blank"><?php esc_html_e( 'Add or remove teams on Tournament page', 'sports-leagues' ); ?></a>
					</div>
				</div>

				<div id="<?php echo esc_attr( $app_id ); ?>"></div>

				<div class="anwp-b-wrap">
					<input class="button button-primary button-large mt-0 px-5" type="submit" value="<?php esc_html_e( 'Save', 'sports-leagues' ); ?>">
				</div>

				<input type="hidden" value="yes" name="_sl_fixed">
			</div>
		<?php else : ?>

			<script type="text/javascript">
				window._slGroupsAvailable = <?php echo wp_json_encode( $this->get_tournament_groups_without_standing() ); ?>;
			</script>

			<?php
			/*
			|--------------------------------------------------------------------
			| Clone Standing
			|--------------------------------------------------------------------
			*/
			$clone_id = empty( $_GET['clone_id'] ) ? 0 : absint( $_GET['clone_id'] ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( $clone_id ) :
				?>
				<div class="anwp-b-wrap">
					<div class="alert alert-info mb-4">
						<b><?php echo esc_html__( 'Cloning Standing Table', 'sports-leagues' ); ?>:</b> <?php echo esc_html( $this->get_standing_title( $clone_id ) ); ?> (ID: <?php echo absint( $clone_id ); ?>)
					</div>
				</div>
				<input type="hidden" name="_clone_id" value="<?php echo absint( $clone_id ); ?>">
			<?php endif; ?>

			<div id="sl-app-standing-setup"></div>
			<?php
		endif;
	}

	/**
	 * Display successful recalculated text.
	 *
	 * @since 0.1.0
	 */
	public function display_admin_standing_notice() {

		if ( get_transient( 'sl-admin-standing-recalculated' ) ) :
			?>
			<div class="notice notice-success is-dismissible anwp-visible-after-header">
				<p><?php echo esc_html( get_transient( 'sl-admin-standing-recalculated' ) ); ?></p>
			</div>
			<?php
			delete_transient( 'sl-admin-standing-recalculated' );
		endif;
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.1.0
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( 'post.php' === $hook_suffix && 'sl_standing' === $current_screen->id ) {

			$post_id = get_the_ID();

			if ( 'yes' === get_post_meta( $post_id, '_sl_fixed', true ) ) {

				$data = [
					'manualOrdering'      => get_post_meta( $post_id, '_sl_manual_ordering', true ),
					'rankingRulesCurrent' => get_post_meta( $post_id, '_sl_ranking_rules_current', true ) ? : '',
					'pointsInitial'       => get_post_meta( $post_id, '_sl_points_initial', true ),
					'tableColors'         => get_post_meta( $post_id, '_sl_table_colors', true ),
					'tableMain'           => get_post_meta( $post_id, '_sl_table_main', true ),
					'standingConfig'      => $this->plugin->config->get_standing_config(),
					'hidePoints'          => Sports_Leagues_Config::get_value( 'standing_points_hide' ),
					'columnsMiniOrder'    => get_post_meta( $post_id, '_sl_columns_mini_order', true ),
					'columnsOrder'        => get_post_meta( $post_id, '_sl_columns_order', true ),
				];

				/**
				 * Filter Standing Data.
				 *
				 * @param array $data    Standing data
				 * @param int   $post_id Standing ID
				 *
				 * @since 0.1.0
				 */
				$data = apply_filters( 'sports-leagues/standing/data_to_admin_vue', $data, $post_id );

				wp_localize_script( 'sl_admin', 'slStanding', $data );
			}
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post
	 *
	 * @return bool|int
	 *@since  0.1.0
	 */
	public function save_metabox( $post_id, $post ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['anwp_metabox_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['anwp_metabox_nonce'], 'anwp_save_metabox_' . $post_id ) ) {
			return $post_id;
		}

		// Check post type
		if ( 'sl_standing' !== $_POST['post_type'] ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// check if there was a multisite switch before
		if ( is_multisite() && ms_is_switched() ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		/** ---------------------------------------
		 * Save Standing Data
		 *
		 * @since 0.1.0
		 * ---------------------------------------*/

		$fixed = empty( $_POST['_sl_fixed'] ) ? '' : sanitize_key( $_POST['_sl_fixed'] );

		if ( 'yes' === $fixed ) {

			/**
			 * Trigger on before save standing data.
			 *
			 * @param array $_POST   Post data
			 * @param int   $post_id Standing ID
			 *
			 * @since 0.9.3
			 */
			do_action( 'sports-leagues/standing/before_save', $_POST, $post_id );

			update_post_meta( $post_id, '_sl_fixed', 'yes' );

			// Prepare data & Encode with some WP sanitization
			$point_initial = isset( $_POST['_sl_points_initial'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_sl_points_initial'] ) ) ) : wp_json_encode( [] );
			$table_main    = isset( $_POST['_sl_table_main'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_sl_table_main'] ) ) ) : wp_json_encode( [] );
			$table_colors  = isset( $_POST['_sl_table_colors'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_sl_table_colors'] ) ) ) : wp_json_encode( [] );

			// phpcs:disable WordPress.NamingConventions
			if ( $table_main && apply_filters( 'sports-leagues/standing/save_core_standing', true, $post_id ) && isset( $_POST['_sl_table_main'] ) ) {
				update_post_meta( $post_id, '_sl_table_main', wp_slash( $table_main ) );
			}

			if ( $point_initial ) {
				update_post_meta( $post_id, '_sl_points_initial', wp_slash( $point_initial ) );
			}

			if ( $table_colors ) {
				update_post_meta( $post_id, '_sl_table_colors', wp_slash( $table_colors ) );
			}

			// General Data
			$data = [];

			$data['_sl_ranking_rules_current'] = isset( $_POST['_sl_ranking_rules_current'] ) ? sanitize_text_field( $_POST['_sl_ranking_rules_current'] ) : '';
			$data['_sl_manual_ordering']       = Sports_Leagues::string_to_bool( sanitize_key( $_POST['_sl_manual_ordering'] ) ) ? 'yes' : '';

			/*
			|--------------------------------------------------------------------
			| Standing Columns Order
			|--------------------------------------------------------------------
			*/
			$columns_order      = isset( $_POST['_sl_columns_order'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_sl_columns_order'] ) ) ) : wp_json_encode( [] );
			$columns_mini_order = isset( $_POST['_sl_columns_mini_order'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_sl_columns_mini_order'] ) ) ) : wp_json_encode( [] );

			if ( $columns_order ) {
				$data['_sl_columns_order']      = wp_slash( $columns_order );
				$data['_sl_columns_mini_order'] = wp_slash( $columns_mini_order );
			}

			/**
			 * Filter Standing Data before save
			 *
			 * @param array $data    Match data
			 * @param int   $post_id Standing ID
			 *
			 * @since 0.1.0
			 */
			$data = apply_filters( 'sports-leagues/standing/data_to_save', $data, $post_id );

			foreach ( $data as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}

			/**
			 * Trigger on save standing data.
			 *
			 * @param array $data    Standing data
			 * @param int   $post_id Standing ID
			 *
			 * @since 0.1.0
			 */
			do_action( 'sports-leagues/standing/on_save', $data, $post_id );

			$this->calculate_standing_prepare( $post_id );

			// phpcs:enable WordPress.NamingConventions
		} else {

			if ( isset( $_POST['select-group'] ) && 'yes' === $_POST['select-group'] ) {

				update_post_meta( $post_id, '_sl_fixed', 'yes' );

				$update_data = [
					'ID'         => $post_id,
					'post_title' => $this->get_standing_title( $post_id ),
				];

				if ( 'publish' !== $post->post_status ) {
					$update_data['post_status'] = 'publish';
				}

				remove_action( 'save_post_sl_standing', [ $this, 'save_metabox' ] );

				wp_update_post( $update_data );

				// re-hook this function
				add_action( 'save_post_sl_standing', [ $this, 'save_metabox' ] );

				if ( ! empty( $_POST['_clone_id'] ) && absint( $_POST['_clone_id'] ) ) {
					$this->clone_standing( $post_id, absint( $_POST['_clone_id'] ) );
				}
			}

			// Save initial stage options
			$stage_group_id = trim( sanitize_key( $_POST['stage_group_id'] ) );

			if ( $stage_group_id ) {
				$group_data = explode( '_', $stage_group_id );

				if ( 2 === count( $group_data ) ) {
					update_post_meta( $post_id, '_sl_stage_id', $group_data[0] );
					update_post_meta( $post_id, '_sl_group_id', $group_data[1] );
				}
			}
		}

		return $post_id;
	}

	/**
	 * Prepares data for Recalculate Standing Table.
	 *
	 * @param int $game_id
	 *
	 * @return void
	 *@since  0.1.0
	 */
	public function calculate_standing_prepare( $game_id ) {

		if ( ! intval( $game_id ) ) {
			return;
		}

		// Get competition & competitionGroup
		$stage_id = (int) get_post_meta( $game_id, '_sl_stage_id', true );
		$group_id = (int) get_post_meta( $game_id, '_sl_group_id', true );

		// Recheck
		if ( ! $stage_id || ! $group_id ) {
			return;
		}

		// Get standing
		$standing_obj = get_posts(
			[
				'post_type'      => 'sl_standing',
				'posts_per_page' => 1,
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'     => '_sl_stage_id',
						'value'   => $stage_id,
						'compare' => '=',
					],
					[
						'key'     => '_sl_group_id',
						'value'   => $group_id,
						'compare' => '=',
					],
				],
			]
		);

		if ( empty( $standing_obj[0]->ID ) || 'yes' !== get_post_meta( $standing_obj[0]->ID, '_sl_fixed', true ) || ! $standing_obj[0] instanceof WP_Post ) {
			return;
		}

		$this->calculate_standing( $standing_obj[0] );
	}

	/**
	 * Recalculate Standing Table
	 *
	 * @param WP_Post $standing_post
	 *
	 * @since 0.1.0
	 */
	public function calculate_standing( $standing_post ) {

		global $wpdb;

		$standing_data = [
			'stage_id'        => get_post_meta( $standing_post->ID, '_sl_stage_id', true ),
			'group_id'        => get_post_meta( $standing_post->ID, '_sl_group_id', true ),
			'manual_ordering' => get_post_meta( $standing_post->ID, '_sl_manual_ordering', true ),
			'points_initial'  => get_post_meta( $standing_post->ID, '_sl_points_initial', true ),
			'ranking_rules'   => get_post_meta( $standing_post->ID, '_sl_ranking_rules_current', true ),
		];

		/*
		|--------------------------------------------------------------------
		| Prepare Empty Table
		|--------------------------------------------------------------------
		*/
		$table = [];

		foreach ( $this->plugin->tournament->get_stage_teams( $standing_data['stage_id'], $standing_data['group_id'] ) as $team ) {

			if ( (int) $team ) {
				$table[ $team ] = [
					'team_id'  => $team,
					'place'    => 0,
					'played'   => 0,
					'all_win'  => 0,
					'ft_win'   => 0,
					'ov_win'   => 0,
					'pen_win'  => 0,
					'draw'     => 0,
					'all_loss' => 0,
					'ft_loss'  => 0,
					'ov_loss'  => 0,
					'pen_loss' => 0,
					'sf'       => 0,
					'sa'       => 0,
					'sd'       => 0,
					'ratio'    => 0,
					'bpts'     => 0,
					'pts'      => 0,
					'series'   => '',
				];
			}
		}

		if ( empty( $table ) ) {
			return;
		}

		// Get finished games
		$games = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT *
				FROM {$wpdb->prefix}sl_games
				WHERE stage_id = %d
					AND group_id = %d
					AND finished = 1
				ORDER BY kickoff ASC
				",
				$standing_data['stage_id'],
				$standing_data['group_id']
			)
		);

		// Populate stats
		foreach ( $games as $game ) {

			// Played
			$table[ $game->home_team ]['played'] ++;
			$table[ $game->away_team ]['played'] ++;

			// Bonus Points
			$table[ $game->home_team ]['bpts'] += $game->home_bonus_points;
			$table[ $game->away_team ]['bpts'] += $game->away_bonus_points;

			// Points
			$table[ $game->home_team ]['pts'] += $game->home_points + $game->home_bonus_points;
			$table[ $game->away_team ]['pts'] += $game->away_points + $game->away_bonus_points;

			// Scores
			if ( '' !== $game->home_scores && '' !== $game->away_scores && is_numeric( $game->home_scores ) && is_numeric( $game->away_scores ) ) {
				$table[ $game->home_team ]['sf'] += $game->home_scores;
				$table[ $game->away_team ]['sf'] += $game->away_scores;

				$table[ $game->home_team ]['sa'] += $game->away_scores;
				$table[ $game->away_team ]['sa'] += $game->home_scores;
			}

			// Outcome
			if ( isset( $table[ $game->home_team ][ $game->home_outcome ] ) ) {
				$table[ $game->home_team ][ $game->home_outcome ] ++;
			}

			if ( isset( $table[ $game->away_team ][ $game->away_outcome ] ) ) {
				$table[ $game->away_team ][ $game->away_outcome ] ++;
			}
		}

		// Calculate complex fields
		foreach ( $table as $team_id => $team ) {
			$table[ $team_id ]['all_win']  = $team['ft_win'] + $team['ov_win'] + $team['pen_win'];
			$table[ $team_id ]['all_loss'] = $team['ft_loss'] + $team['ov_loss'] + $team['pen_loss'];

			$table[ $team_id ]['sd']    = $team['sf'] - $team['sa'];
			$table[ $team_id ]['ratio'] = $team['played'] ? ( round( $table[ $team_id ]['all_win'] / $team['played'], 3 ) ) : 0;
		}

		/*
		|--------------------------------------------------------------------
		| Initial Points
		|--------------------------------------------------------------------
		*/
		if ( $standing_data['points_initial'] ) {
			$initial = json_decode( wp_unslash( $standing_data['points_initial'] ) );

			if ( ! empty( $initial ) && is_object( $initial ) ) {
				foreach ( $initial as $team_id => $points_to_add ) {
					$table[ $team_id ]['pts'] = $table[ $team_id ]['pts'] + (int) $points_to_add;
				}
			}
		}

		$places_are_set = false;

		/*
		|--------------------------------------------------------------------
		| Ordering
		|--------------------------------------------------------------------
		*/
		if ( count( $table ) && 'yes' !== $standing_data['manual_ordering'] ) {

			/**
			 * Filter: sports-leagues/standing/custom_position_calculation
			 *
			 * @param bool
			 * @param array $table
			 * @param array $standing_data
			 *
			 *@since 0.1.0
			 *
			 */
			if ( apply_filters( 'sports-leagues/standing/custom_position_calculation', false, $table, $standing_data ) ) {

				/**
				 * Filter: sports-leagues/standing/custom_position_calculation_table
				 *
				 * @param array $table
				 * @param array $data
				 * @param array $matches
				 *
				 *@since 0.1.0
				 *
				 */
				$table = apply_filters( 'sports-leagues/standing/custom_position_calculation_table', $table, $standing_data, $games );
			} else {

				// Sorting rules
				$rules = explode( ',', $standing_data['ranking_rules'] );

				// Prepare Sorting Order
				$sorting_order = [];
				foreach ( $rules as $rule ) {
					$sorting_order[ $rule ] = 'DESC';
				}

				// Prepare Sorting Data
				$sorting_data = [];

				foreach ( $table as $row ) {
					if ( ! empty( $row['team_id'] ) ) {
						$sorting_data[ $row['team_id'] ] = [
							'team_id' => $row['team_id'],
							'place'   => 0,
							'pts'     => $row['pts'],
							'all_win' => $row['all_win'],
							'ft_win'  => $row['ft_win'],
							'ov_win'  => $row['ov_win'],
							'pen_win' => $row['pen_win'],
							'draw'    => $row['draw'],
							'sf'      => $row['sf'],
							'sd'      => $row['sd'],
							'ratio'   => $row['ratio'],
						];
					}
				}

				// Sort
				$sorting_data = wp_list_sort( $sorting_data, $sorting_order );

				foreach ( $sorting_data as $index => $row ) {
					$table[ $row['team_id'] ]['place'] = $index + 1;
				}

				$table = array_values( $table );

				// Sort by place
				$table = wp_list_sort( $table, 'place' );

				// Prevent future position calculation
				$places_are_set = true;
			}
		} else {

			$table_old = json_decode( get_post_meta( $standing_post->ID, '_sl_table_main', true ) );

			if ( is_array( $table_old ) && count( $table_old ) ) {

				$places = [];

				foreach ( $table_old as $row ) {
					if ( ! empty( $table[ $row->team_id ] ) ) {
						$table[ $row->team_id ]['place'] = $row->place;
					}
				}

				foreach ( $table as $key => $row ) {
					$places[ $key ] = $row['place'];
				}

				array_multisort( $places, SORT_ASC, $table );

			} else {
				$table = array_values( $table );
			}
		}

		// Set Place field
		if ( ! $places_are_set ) {
			$place_counter = 1;
			foreach ( $table as $index => $row ) {
				$table[ $index ]['place'] = $place_counter ++;
			}
		}

		// Save to DB
		update_post_meta( $standing_post->ID, '_sl_table_main', wp_slash( wp_json_encode( $table ) ) );
		update_post_meta( $standing_post->ID, '_sl_last_recalc', current_time( 'mysql', true ) );

		// Table recalculated notice
		$notice_text = sprintf( 'Standing Table (ID %d) has been successfully recalculated.', $standing_post->ID );
		set_transient( 'sl-admin-standing-recalculated', $notice_text, 10 );

		/**
		 * Trigger on save standing data.
		 *
		 * @param array $data    Standing data
		 * @param int   $post_id Standing ID
		 *
		 * @since 0.5.14
		 */
		do_action( 'sports-leagues/standing/standing_calculation_alternative', $standing_post, $standing_data );
	}

	/**
	 * Create CMB2 metaboxes
	 *
	 * @since 0.5.14
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sl_';

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			[
				'id'           => 'sl_cmb2_standing_metabox',
				'title'        => esc_html__( 'Standing Extra Options', 'sports-leagues' ),
				'object_types' => [ 'sl_standing' ],
				'context'      => 'normal',
				'priority'     => 'low',
				'classes'      => 'anwp-b-wrap',
				'show_names'   => true,
				'show_on_cb'   => function ( $cmb ) {
					return $cmb->object_id() && 'yes' === get_post_meta( $cmb->object_id, '_sl_fixed', true );
				},
			]
		);

		$table_notes_placeholders = '
			<div class="mt-2 small"><b class="d-block">Available placeholders: </b>
				<div class="d-flex align-items-center flex-wrap mb-1">
					<span class="border mr-2 py-2 px-3 d-inline-block table-primary"></span> %primary%
					<span class="ml-4 border mr-2 px-3 py-2 d-inline-block table-secondary"></span> %secondary%
					<span class="ml-4 border mr-2 px-3 py-2 d-inline-block table-success"></span> %success%
					<span class="ml-4 border mr-2 px-3 py-2 d-inline-block table-warning"></span> %warning%
					<span class="ml-4 border mr-2 px-3 py-2 d-inline-block table-danger"></span> %danger%
					<span class="ml-4 border mr-2 px-3 py-2 d-inline-block table-info"></span> %info%
				</div>
			</div>';

		$cmb->add_field(
			[
				'name'        => esc_html__( 'Table Notes', 'sports-leagues' ),
				'id'          => $prefix . 'table_notes',
				'type'        => 'wysiwyg',
				'options'     => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_standing_notes_input',
					'textarea_rows' => 8,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names'  => false,
				'before_row'  => '<div id="anwp-tabs-notes-sl_standing_metabox" class="anwp-metabox-tabs__content-item">',
				'after_field' => $table_notes_placeholders,
				'after_row'   => '</div>',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.14
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/standing', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Renders tabs for metabox. Helper HTML before.
	 *
	 * @since 0.5.14
	 */
	public function cmb2_before_metabox() {
		// @formatter:off
		ob_start();
		?>
		<div class="anwp-b-wrap">
			<div class="anwp-metabox-tabs d-sm-flex">
				<div class="anwp-metabox-tabs__controls d-flex flex-sm-column">
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-notes-sl_standing_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-note"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Table Notes', 'sports-leagues' ); ?></span>
					</div>
					<?php
					/**
					 * Fires in the bottom of standing tabs.
					 *
					 * @since 0.5.14
					 */
					do_action( 'sports-leagues/cmb2_tabs_control/standing' );
					?>
				</div>
				<div class="anwp-metabox-tabs__content pl-4 pb-4">
		<?php
		echo ob_get_clean(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// @formatter:on
	}

	/**
	 * Renders tabs for metabox. Helper HTML after.
	 *
	 * @since 0.5.14
	 */
	public function cmb2_after_metabox() {
		// @formatter:off
		ob_start();
		?>
				</div>
			</div>
		</div>
		<?php
		echo ob_get_clean(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// @formatter:on
	}

	/**
	 * Parse and prepare table notes to output.
	 *
	 * @param string $notes - Raw notes string
	 *
	 * @return string
	 * @since 0.6.1
	 */
	public function prepare_table_notes( $notes ) {

		$replace_data = [ 'info', 'warning', 'danger', 'primary', 'secondary', 'success' ];

		foreach ( $replace_data as $replace ) {
			$notes = str_ireplace( '%' . $replace . '%', '<span class="border mr-1 px-3 table-' . $replace . '"></span>', $notes );
		}

		$notes = nl2br( $notes );

		return $notes;
	}

	/**
	 * Filter standing table partition data.
	 *
	 * @param array  $table
	 * @param string $partial
	 *
	 * @return array
	 * @since 0.8.0
	 */
	public function get_standing_partial_data( $table, $partial ) {

		$first = 0;
		$last  = 0;

		if ( ! mb_strpos( $partial, '-', 1 ) && absint( $partial ) ) {
			$team_id = absint( $partial );

			foreach ( $table as $table_row ) {
				if ( $team_id === $table_row->team_id ) {

					$first = ( $table_row->place - 2 ) < 1 ? 1 : $table_row->place - 2;
					$last  = $table_row->place + 2;

					break;
				}
			}
		} elseif ( mb_strpos( $partial, '-', 1 ) ) {
			$partial_arr = explode( '-', $partial );

			$first = absint( trim( $partial_arr[0] ) );

			if ( ! empty( $partial_arr[1] ) ) {
				$last = absint( trim( $partial_arr[1] ) );
			}
		}

		if ( empty( $first ) || empty( $last ) ) {
			return $table;
		}

		foreach ( $table as $row_index => $row_item ) {
			if ( $row_item->place < $first || $row_item->place > $last ) {
				unset( $table[ $row_index ] );
			}
		}

		return $table;
	}

	/**
	 * Get list of tournament groups without assigned standings.
	 *
	 * @return array $output_data
	 * @since 0.12.4
	 */
	public function get_tournament_groups_without_standing() {

		$tournaments = sports_leagues()->tournament->get_tournaments();

		if ( empty( $tournaments ) || ! is_array( $tournaments ) ) {
			return [];
		}

		global $wpdb;

		$query = "
		SELECT p.ID, pm2.meta_value group_id, pm1.meta_value stage_id
		FROM $wpdb->posts p
		LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key = '_sl_stage_id' )
		LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = p.ID AND pm2.meta_key = '_sl_group_id' )
		LEFT JOIN $wpdb->postmeta pm3 ON ( pm3.post_id = p.ID AND pm3.meta_key = '_sl_fixed' )
		WHERE p.post_type = 'sl_standing' AND p.post_status = 'publish' AND pm3.meta_value = 'yes' AND pm1.meta_value IS NOT NULL AND pm1.meta_value != '' AND pm2.meta_value IS NOT NULL AND pm2.meta_value != ''
		";

		$query .= ' GROUP BY p.ID';

		/*
		|--------------------------------------------------------------------
		| Bump Query
		|--------------------------------------------------------------------
		*/
		$standings = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		$standings_map = [];
		$output_data   = [];

		if ( ! empty( $standings ) && is_array( $standings ) ) {
			foreach ( $standings as $standing ) {
				$standings_map[ $standing->stage_id ][ $standing->group_id ] = $standing->ID;
			}
		}

		foreach ( $tournaments as $tournament ) {

			if ( ! empty( $tournament->stages ) && is_array( $tournament->stages ) ) {
				foreach ( $tournament->stages as $stage ) {

					if ( 'group' !== $stage->type ) {
						continue;
					}

					foreach ( $stage->groups as $group ) {

						if ( ! empty( $standings_map[ $stage->id ] ) && ! empty( $standings_map[ $stage->id ][ $group->id ] ) ) {
							continue;
						}

						if ( empty( $output_data[ $tournament->id ] ) ) {
							$output_data[ $tournament->id ] = [
								'id'     => $tournament->id,
								'title'  => $tournament->title,
								'logo'   => $tournament->logo,
								'league' => $tournament->league_text,
								'season' => $tournament->season_text,
								'groups' => [],
							];
						}

						$group_teams = [];

						if ( ! empty( $group->teams ) ) {
							foreach ( $group->teams as $team_id ) {
								$team_obj = sports_leagues()->team->get_team_by_id( $team_id );

								if ( $team_obj ) {
									$group_teams[] = [
										'id'    => $team_obj->id,
										'title' => $team_obj->title,
										'logo'  => $team_obj->logo,
									];
								}
							}
						}

						$output_data[ $tournament->id ]['groups'][] = [
							'id'          => $stage->id . '_' . $group->id,
							'group_title' => $group->title,
							'stage_title' => $stage->title,
							'teams'       => $group_teams ? wp_list_sort( $group_teams, 'title' ) : [],
						];
					}
				}
			}
		}

		return $output_data ? array_values( $output_data ) : [];
	}

	/**
	 * Remove publish metabox
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @since 0.12.4
	 */
	public function remove_term_metaboxes( $post ) {
		if ( 'yes' !== get_post_meta( $post->ID, '_sl_fixed', true ) ) {
			remove_meta_box( 'submitdiv', 'sl_standing', 'side' );
		}
	}
}
