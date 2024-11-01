<?php
/**
 * Sports Leagues :: Tournament.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Tournament {

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
	 * @param  Sports_Leagues $plugin Main plugin object.
	 *
	 * @since  0.1.0
	 */
	public function __construct( $plugin ) {

		// Assign main plugin class
		$this->plugin = $plugin;

		// Register CPT
		$this->register_post_type( $plugin );

		// Bump Hooks
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
		$permalink_slug      = empty( $permalink_structure['tournament'] ) ? 'tournament' : $permalink_structure['tournament'];

		$labels = [
			'name'                  => _x( 'Tournaments', 'Post type general name', 'sports-leagues' ),
			'singular_name'         => _x( 'Tournament', 'Post type singular name', 'sports-leagues' ),
			'menu_name'             => _x( 'Tournaments', 'Admin Menu text', 'sports-leagues' ),
			'name_admin_bar'        => _x( 'Tournament', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'               => __( 'New Tournament', 'sports-leagues' ),
			'add_new_item'          => __( 'Add New Tournament', 'sports-leagues' ),
			'new_item'              => __( 'New Tournament', 'sports-leagues' ),
			'edit_item'             => __( 'Edit Tournament', 'sports-leagues' ),
			'view_item'             => __( 'View Tournament', 'sports-leagues' ),
			'all_items'             => __( 'All Tournaments', 'sports-leagues' ),
			'search_items'          => __( 'Search Tournaments', 'sports-leagues' ),
			'not_found'             => __( 'No Tournaments found.', 'sports-leagues' ),
			'not_found_in_trash'    => __( 'No Tournaments found in Trash.', 'sports-leagues' ),
			'featured_image'        => __( 'Tournament logo', 'sports-leagues' ),
			'set_featured_image'    => __( 'Set logo', 'sports-leagues' ),
			'remove_featured_image' => __( 'Remove logo', 'sports-leagues' ),
			'use_featured_image'    => __( 'Use as Tournament logo', 'sports-leagues' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_position'      => 44,
			'menu_icon'          => $plugin::SVG_CUP,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => true,
			'show_in_rest'       => true,
			'rest_base'          => 'sl_tournaments',
			'rewrite'            => [ 'slug' => $permalink_slug ],
			'supports'           => [ 'thumbnail', 'comments' ],
			'taxonomies'         => [ 'sl_league', 'sl_season' ],
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

		register_post_type( 'sl_tournament', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Init metaboxes
		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );

		// Init CMB2 metaboxes
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );
		add_action( 'cmb2_before_post_form_sl_tournament_cmb2_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_post_form_sl_tournament_cmb2_metabox', [ $this, 'cmb2_after_metabox' ] );

		add_action( 'save_post_sl_tournament', [ $this, 'save_metabox' ], 10, 2 );

		// Modifies columns in Admin tables
		add_action( 'manage_sl_tournament_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_tournament_columns', [ $this, 'columns' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		// Remove taxonomy metaboxes
		add_action( 'add_meta_boxes_sl_tournament', [ $this, 'remove_term_metaboxes' ], 10, 1 );

		// Render Custom Content below
		add_action(
			'sports-leagues/tmpl-tournament-stage/after_wrapper',
			function ( $stage_id ) {

				$content_below = get_post_meta( $stage_id, '_sl_bottom_content', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Admin Table filters
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'add_more_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );

		add_action( 'before_delete_post', [ $this, 'remove_stages' ], 10, 2 );

		// Clone Tournament
		add_filter( 'page_row_actions', [ $this, 'modify_quick_actions' ], 10, 2 );
		add_action( 'wp_ajax_sl_clone_tournament', [ $this, 'process_clone_tournament' ] );
		add_action( 'admin_footer-edit.php', [ $this, 'include_admin_clone_tournament_modaal' ], 99 );
	}

	/**
	 * Filters the array of row action links on the Pages list table.
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 * @since 0.12.4
	 */
	public function modify_quick_actions( $actions, $post ) {

		if ( 'sl_tournament' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {
			$actions['clone-tournament'] = '<a data-tournament-id="' . absint( $post->ID ) . '" class="anwp-sl-tournament-clone-action" href="#">' . esc_html__( 'Clone', 'sports-leagues' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Add modal to clone tournament.
	 *
	 * @since 0.12.4
	 */
	public function include_admin_clone_tournament_modaal() {

		// Load styles and scripts (limit to tournament page)
		$current_screen = get_current_screen();

		if ( ! empty( $current_screen->id ) && 'edit-sl_tournament' === $current_screen->id ) {
			?>
			<div id="anwp-sl-tournament-clone-modaal" style="display: none;">
				<div class="anwp-sl-shortcode-modal__header">
					<h3 style="margin: 0"><?php echo esc_html__( 'Clone Tournament', 'sports-leagues' ); ?></h3>
				</div>
				<div class="anwp-sl-shortcode-modal__content">
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="anwp-sl-clone-season-id"><?php echo esc_html__( 'Season', 'sports-leagues' ); ?></label></th>
							<td>
								<select id="anwp-sl-clone-season-id">
									<?php foreach ( sports_leagues()->season->get_season_options() as $season_id => $season_title ) : ?>
										<option value="<?php echo esc_attr( $season_id ); ?>"><?php echo esc_html( $season_title ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<div class="anwp-sl-shortcode-modal__footer">
					<button id="anwp-sl-tournament-clone-modaal__cancel" class="button"><?php echo esc_html__( 'Close', 'sports-leagues' ); ?></button>
					<button id="anwp-sl-tournament-clone-modaal__clone" class="button button-primary"><?php echo esc_html__( 'Clone', 'sports-leagues' ); ?></button>
					<span class="spinner"></span>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Handle clone tournament action.
	 *
	 * @since 0.12.4
	 */
	public function process_clone_tournament() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax_anwpsl_nonce' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		$tournament_id = isset( $_POST['tournament_id'] ) ? absint( $_POST['tournament_id'] ) : 0;
		$season_id     = isset( $_POST['season_id'] ) ? absint( $_POST['season_id'] ) : 0;

		if ( ! $tournament_id || ! $season_id || ! current_user_can( 'edit_post', $tournament_id ) ) {
			wp_send_json_error( 'Error : Invalid Data' );
		}

		$cloned_id = wp_insert_post(
			[
				'post_type'   => 'sl_tournament',
				'post_status' => 'publish',
			]
		);

		if ( $cloned_id ) {
			$tournament_obj = sports_leagues()->tournament->get_tournament( $tournament_id );

			$meta_fields_to_clone = [
				'_sl_subtitle',
				'_sl_date_from',
				'_sl_date_to',
				'_sl_logo_small_id',
				'_sl_logo_small',
				'_thumbnail_id',
			];

			/**
			 * Filter Tournament Data to clone
			 *
			 * @param array $meta_fields_to_clone Clone data
			 * @param int   $tournament_id        Tournament ID
			 * @param int   $cloned_id            New Cloned Tournament ID
			 *
			 * @since 0.12.4
			 */
			$meta_fields_to_clone = apply_filters( 'sports-leagues/tournament/fields_to_clone', $meta_fields_to_clone, $tournament_id, $cloned_id );

			foreach ( $meta_fields_to_clone as $meta_key ) {

				$meta_value = get_post_meta( $tournament_id, $meta_key, true );

				if ( '' !== $meta_value ) {
					$meta_value = maybe_unserialize( $meta_value );
					update_post_meta( $cloned_id, $meta_key, wp_slash( $meta_value ) );
				}
			}

			/*
			|--------------------------------------------------------------------
			| League
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $tournament_obj->league_id ) ) {
				wp_set_object_terms( $cloned_id, $tournament_obj->league_id, 'sl_league' );
			}

			/*
			|--------------------------------------------------------------------
			| Season
			|--------------------------------------------------------------------
			*/
			wp_set_object_terms( $cloned_id, $season_id, 'sl_season' );

			/*
			|--------------------------------------------------------------------
			| Generate Post Title
			|--------------------------------------------------------------------
			*/
			wp_update_post(
				[
					'ID'         => $cloned_id,
					'post_title' => $tournament_obj->league_text . ' ' . get_term( $season_id, 'sl_season' )->name,
				]
			);

			update_post_meta( $cloned_id, '_sl_cloned', $tournament_id );

			/*
			|--------------------------------------------------------------------
			| Clone Stages
			|--------------------------------------------------------------------
			*/
			foreach ( $tournament_obj->stages as $stage ) {
				$cloned_stage_id = wp_insert_post(
					[
						'post_type'   => 'sl_tournament',
						'post_status' => 'publish',
						'post_title'  => sanitize_text_field( $stage->title ),
						'post_parent' => $cloned_id,
						'menu_order'  => $stage->order,
					]
				);

				if ( $cloned_stage_id ) {

					$stage_meta_fields_to_clone = [
						'_sl_stage_status',
						'_sl_stage_system',
						'_sl_next_id_group',
						'_sl_next_id_round',
						'_sl_rounds',
						'_sl_groups',
					];

					/**
					 * Filter Tournament Stage Data to clone
					 *
					 * @param array $stage_meta_fields_to_clone Clone data
					 * @param int   $stage_id                   Standing ID
					 * @param int   $cloned_stage_id            New Cloned Standing ID
					 *
					 * @since 0.12.4
					 */
					$stage_meta_fields_to_clone = apply_filters( 'sports-leagues/tournament-stage/fields_to_clone', $stage_meta_fields_to_clone, $stage->id, $cloned_stage_id );

					foreach ( $stage_meta_fields_to_clone as $meta_key ) {

						$meta_value = get_post_meta( $stage->id, $meta_key, true );

						if ( '' !== $meta_value ) {
							$meta_value = maybe_unserialize( $meta_value );
							update_post_meta( $cloned_stage_id, $meta_key, wp_slash( $meta_value ) );
						}
					}

					update_post_meta( $cloned_stage_id, '_sl_cloned', $stage->id );
				}
			}
		}

		wp_send_json_success( [ 'link' => admin_url( 'post.php?post=' . intval( $cloned_id ) . '&action=edit' ) ] );
	}

	/**
	 * Fires before removing a post.
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post
	 *
	 * @since 0.1.0
	 */
	public function remove_stages( $post_ID, $post ) {
		if ( 'sl_tournament' === $post->post_type && ! $post->post_parent ) {

			$posts_to_delete = get_posts(
				[
					'post_type'      => 'sl_tournament',
					'posts_per_page' => -1,
					'post_parent'    => $post_ID,
				]
			);

			if ( ! empty( $posts_to_delete ) && is_array( $posts_to_delete ) ) {
				foreach ( $posts_to_delete as $post_to_delete ) {
					if ( $post_to_delete->ID && $post_ID === $post_to_delete->post_parent && 'sl_tournament' === $post_to_delete->post_type ) {
						wp_delete_post( $post_to_delete->ID, true );
					}
				}
			}
		}
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

		return 'sl_tournament' === $post_type ? true : $disable;
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

		if ( 'sl_tournament' === $post_type ) {

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

		if ( 'edit.php' !== $pagenow || 'sl_tournament' !== $post_type ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$trash_page = isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'];

		// Show only parents
		if ( ! $trash_page ) {
			$query->set( 'post_parent', 0 );
		}

		// Prepare Taxonomy filter
		$tax_query = [];

		/*
		|--------------------------------------------------------------------
		| Filter By Season
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_season = empty( $_GET['_sl_current_season'] ) ? '' : intval( $_GET['_sl_current_season'] );

		if ( $filter_by_season ) {
			$tax_query[] =
				[
					'taxonomy' => 'sl_season',
					'terms'    => $filter_by_season,
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
			$tax_query[] =
				[
					'taxonomy' => 'sl_league',
					'terms'    => $filter_by_league,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Join All values to main query
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $tax_query ) ) {
			$query->set(
				'tax_query',
				[
					array_merge( [ 'relation' => 'AND' ], $tax_query ),
				]
			);
		}
	}

	/**
	 * Remove term metaboxes.
	 *
	 * @since 0.1.0
	 */
	public function remove_term_metaboxes() {
		remove_meta_box( 'tagsdiv-sl_league', 'sl_tournament', 'side' );
		remove_meta_box( 'tagsdiv-sl_season', 'sl_tournament', 'side' );

		$post = get_post();

		if ( isset( $post->post_parent ) && 0 === $post->post_parent ) {
			remove_meta_box( 'submitdiv', 'sl_tournament', 'side' );
		}
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.1.0
	 */
	public function add_rest_routes() {
		// Register route to get groups
		register_rest_route(
			'sports-leagues/v1',
			'/get-tournament-groups/(?P<id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_tournament_groups' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_others_pages' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/v1',
			'/get-tournaments/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_tournaments' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_others_pages' );
				},
			]
		);
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.1.0
	 */
	public function init_metaboxes() {
		add_action( 'add_meta_boxes', [ $this, 'add_tournament_metaboxes' ], 10, 2 );
	}

	/**
	 * Add metaboxes on Tournament page
	 *
	 * @param string  $post_type
	 * @param WP_Post $wp_post
	 *
	 * @since  0.1.0
	 */
	public function add_tournament_metaboxes( $post_type, $wp_post ) {
		if ( 'sl_tournament' === $post_type ) {
			if ( 0 === $wp_post->post_parent ) {
				add_meta_box(
					'sl_tournament_metabox',
					esc_html__( 'Tournament Config', 'sports-leagues' ),
					[ $this, 'render_metabox' ],
					$post_type,
					'normal',
					'high'
				);
			} else {
				add_meta_box(
					'sl_tournament_stage_metabox',
					esc_html__( 'Tournament Stage', 'sports-leagues' ),
					[ $this, 'render_stage_metabox' ],
					$post_type,
					'normal',
					'high'
				);
			}
		}
	}

	/**
	 * Render Stage Meta Box.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.11.1
	 */
	public function render_stage_metabox( $post ) {
		?>
		<div class="anwp-b-wrap">
			<div class="anwp-bg-orange-100 anwp-border anwp-border-orange-800 p-3 my-3">
				Direct Stage editing is not allowed. Please go to the root Tournament to make any changes.
				<br>
				<a class="button button-secondary mt-2" href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $post->post_parent ) . '&action=edit' ) ); ?>">Root Tournament</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Meta Box.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.1.0
	 */
	public function render_metabox( $post ) {

		$app_id = apply_filters( 'sports-leagues/tournament/vue_app_id', 'sl-app-tournament' );

		$tournament_obj = sports_leagues()->tournament->get_tournament( $post->ID );
		$stages         = [];

		if ( ! empty( $tournament_obj->stages ) && is_array( $tournament_obj->stages ) ) {
			foreach ( $tournament_obj->stages as $stage ) {

				$stage_rounds = json_decode( get_post_meta( $stage->id, '_sl_rounds', true ) ) ? : [];
				$stage_groups = json_decode( get_post_meta( $stage->id, '_sl_groups', true ) ) ? : [];

				foreach ( $stage_rounds as $stage_round ) {
					$stage_round->groups = [];

					foreach ( $stage_groups as $stage_group ) {
						if ( absint( $stage_group->round ) === absint( $stage_round->id ) ) {
							$stage_round->groups[] = $stage_group;
						}
					}
				}

				$stage_data = [
					'stageTitle'  => $stage->title,
					'stageId'     => $stage->id,
					'stageStatus' => get_post_meta( $stage->id, '_sl_stage_status', true ),
					'stageSystem' => get_post_meta( $stage->id, '_sl_stage_system', true ),
					'nextIdGroup' => absint( get_post_meta( $stage->id, '_sl_next_id_group', true ) ),
					'nextIdRound' => absint( get_post_meta( $stage->id, '_sl_next_id_round', true ) ),
					'rounds'      => $stage_rounds,
				];

				$stages[] = apply_filters( 'sports-leagues/tournament/stage-admin-app-data', $stage_data );
			}
		}

		$teams = [];

		foreach ( $this->plugin->team->get_team_objects() as $team ) {
			$teams[ $team->id ] = [
				'id'    => $team->id,
				'title' => $team->title,
				'logo'  => $team->logo,
			];
		}

		$tournament_data = [
			'tournamentTitle' => $post->post_title,
			'subtitle'        => get_post_meta( $post->ID, '_sl_subtitle', true ),
			'dateFrom'        => get_post_meta( $post->ID, '_sl_date_from', true ),
			'dateTo'          => get_post_meta( $post->ID, '_sl_date_to', true ),
			'seasonId'        => isset( $tournament_obj->season_id ) ? $tournament_obj->season_id : '',
			'leagueId'        => isset( $tournament_obj->league_id ) ? $tournament_obj->league_id : '',
			'tournamentTypes' => $this->plugin->config->get_options( 'tournament_types' ),
			'leaguesList'     => $this->plugin->league->get_leagues_list(),
			'seasonsList'     => $this->plugin->season->get_seasons_list(),
			'teams'           => $teams,
			'l10n_datepicker' => sports_leagues()->data->get_vue_datepicker_locale(),
			'stages'          => $stages,
		];

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		?>
		<script type="text/javascript">
			var _slTournament = <?php echo wp_json_encode( $tournament_data ); ?>;
		</script>

		<div id="<?php echo esc_attr( $app_id ); ?>"></div>
		<?php
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post    Post object.
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
		if ( 'sl_tournament' !== $_POST['post_type'] ) {
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

		// prevent direct stage save
		if ( absint( $post->post_parent ) ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		$post_data = wp_unslash( $_POST );

		/** ---------------------------------------
		 * Save Tournament Parent
		 *
		 * @since 0.1.0
		 * ---------------------------------------*/

		$tournament_prev = sports_leagues()->tournament->get_tournament( $post_id );

		// Save League
		$league_id = isset( $post_data['_sl_league_id'] ) ? intval( $post_data['_sl_league_id'] ) : '';

		if ( $league_id && ( empty( $tournament_prev ) || absint( $tournament_prev->league_id ) !== $league_id ) ) {
			wp_set_object_terms( $post_id, [ $league_id ], 'sl_league' );
		}

		// Save Season
		$season_id = isset( $post_data['_sl_season_id'] ) ? intval( $post_data['_sl_season_id'] ) : '';

		if ( $season_id && ( empty( $tournament_prev ) || absint( $tournament_prev->season_id ) !== $season_id ) ) {
			wp_set_object_terms( $post_id, [ $season_id ], 'sl_season' );
		}

		// Create slug
		$this->create_tournament_slug( $post, $post_id );

		/*
		|--------------------------------------------------------------------
		| Save additional data
		|--------------------------------------------------------------------
		*/
		$data = [];

		$data['subtitle']  = isset( $post_data['_sl_subtitle'] ) ? sanitize_text_field( $post_data['_sl_subtitle'] ) : '';
		$data['date_from'] = isset( $post_data['_sl_date_from'] ) ? sanitize_text_field( $post_data['_sl_date_from'] ) : '';
		$data['date_to']   = isset( $post_data['_sl_date_to'] ) ? sanitize_text_field( $post_data['_sl_date_to'] ) : '';

		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, '_sl_' . $key, $value );
		}

		if ( isset( $post_data['add_new_stage'] ) && 'yes' === $post_data['add_new_stage'] ) {

			/** ---------------------------------------
			 * Create a New Stage (on tournament setup)
			 *
			 * @since 0.1.0
			 * ---------------------------------------*/

			remove_action( 'save_post_sl_tournament', [ $this, 'save_metabox' ] );

			wp_publish_post( $post_id );

			wp_insert_post(
				[
					'post_parent' => $post_id,
					'post_type'   => 'sl_tournament',
					'post_title'  => 'Regular Season',
					'post_status' => 'publish',
					'meta_input'  => [
						'_sl_stage_system'  => 'group',
						'_sl_stage_status'  => 'official',
						'_sl_next_id_round' => 1,
						'_sl_next_id_group' => 1,
					],
				]
			);

			add_action( 'save_post_sl_tournament', [ $this, 'save_metabox' ], 10, 2 );

		} elseif ( isset( $post_data['_sl_stages_data'] ) ) {

			/** ---------------------------------------
			 * Save Tournament Stages
			 *
			 * @since 0.1.0
			 * ---------------------------------------*/

			$stages_data = json_decode( $post_data['_sl_stages_data'] );
			$stages_add  = $post_data['_sl_stages_added'] ? explode( '|', $post_data['_sl_stages_added'] ) : [];
			$stages_del  = $post_data['_sl_stages_removed'] ? explode( '|', $post_data['_sl_stages_removed'] ) : [];

			if ( ! empty( $stages_del ) && is_array( $stages_del ) ) {
				foreach ( $stages_del as $stage_to_delete ) {
					if ( ! absint( $stage_to_delete ) ) {
						continue;
					}

					if ( ! empty( wp_list_filter( $stages_data, [ 'stageId' => $stage_to_delete ] ) ) ) {
						continue;
					}

					$post_to_delete = get_post( $stage_to_delete );

					if ( ( $post_to_delete instanceof WP_Post ) && 'sl_tournament' === $post_to_delete->post_type && $post_id === $post_to_delete->post_parent ) {
						wp_delete_post( $post_to_delete->ID, true );
					}
				}
			}

			// phpcs:disable WordPress.NamingConventions
			foreach ( $stages_data as $stage_index => $stage_data ) {

				$stage_id = '';

				if ( in_array( $stage_data->stageId, $stages_add, true ) ) {

					/*
					|--------------------------------------------------------------------
					| Add New Stage
					|--------------------------------------------------------------------
					*/
					$stage_id = wp_insert_post(
						[
							'post_parent' => $post_id,
							'post_type'   => 'sl_tournament',
							'post_title'  => sanitize_text_field( $stage_data->stageTitle ),
							'post_status' => 'publish',
							'menu_order'  => $stage_index,
						]
					);

				} elseif ( absint( $stage_data->stageId ) ) {

					/*
					|--------------------------------------------------------------------
					| Update Existing Stage
					|--------------------------------------------------------------------
					*/
					$stage_post_prev = get_post( $stage_data->stageId );

					if ( ( $stage_post_prev instanceof WP_Post ) && $stage_post_prev->post_parent ) {

						$stage_id = $stage_data->stageId;

						$post_update_data = [];

						if ( sanitize_text_field( $stage_data->stageTitle ) !== $stage_post_prev->post_title ) {
							$post_update_data['post_title'] = sanitize_text_field( $stage_data->stageTitle );
						}

						if ( $stage_index !== $stage_post_prev->menu_order ) {
							$post_update_data['menu_order'] = $stage_index;
						}

						if ( ! empty( $post_update_data ) ) {
							wp_update_post( array_merge( [ 'ID' => $stage_id ], $post_update_data ) );
						}
					}
				}

				/*
				|--------------------------------------------------------------------
				| Save meta data
				|--------------------------------------------------------------------
				*/
				if ( ! empty( $stage_id ) ) {
					$meta_data = [];

					$meta_data['_sl_stage_status'] = sanitize_key( $stage_data->stageStatus );
					$meta_data['_sl_stage_system'] = sanitize_key( $stage_data->stageSystem );

					$meta_data['_sl_next_id_group'] = (int) $stage_data->nextIdGroup;
					$meta_data['_sl_next_id_round'] = (int) $stage_data->nextIdRound;

					foreach ( $meta_data as $key => $value ) {
						update_post_meta( $stage_id, $key, $value );
					}

					// Groups and rounds
					$rounds = [];
					$groups = [];

					foreach ( $stage_data->rounds as $round_data ) {
						$rounds[] = [
							'id'    => $round_data->id,
							'title' => $round_data->title,
						];

						$groups = array_merge( $groups, $round_data->groups );
					}

					update_post_meta( $stage_id, '_sl_rounds', wp_slash( wp_json_encode( $rounds ) ) );
					update_post_meta( $stage_id, '_sl_groups', wp_slash( wp_json_encode( $groups ) ) );

					/**
					 * Fires after save stage.
					 *
					 * @param WP_Post $post
					 * @param array   $data
					 * @param array   $post_data
					 *
					 * @since 0.11.1
					 */
					do_action( 'sports-leagues/stage/after_save', $stage_data, $post_data );
				}
			}

			/**
			 * Fires after save stages.
			 *
			 * @param WP_Post $post
			 * @param array   $data
			 * @param array   $post_data
			 *
			 * @since 0.11.1
			 */
			do_action( 'sports-leagues/stages/after_save', $post, $data, $post_data );

			// phpcs:enable WordPress.NamingConventions
		}

		return $post_id;
	}

	/**
	 * Creates tournament slug.
	 *
	 * @param WP_Post $post
	 * @param int     $post_id
	 *
	 * @since 0.5.4
	 */
	public function create_tournament_slug( $post, $post_id ) {
		$slug = wp_unique_post_slug( sanitize_title_with_dashes( $post->post_title ), $post_id, $post->post_status, $post->post_type, $post->post_parent );

		if ( $post->post_name !== $slug ) {

			remove_action( 'save_post_sl_tournament', [ $this, 'save_metabox' ] );

			wp_update_post(
				[
					'ID'        => $post_id,
					'post_name' => $slug,
				]
			);

			// re-hook this function
			add_action( 'save_post_sl_tournament', [ $this, 'save_metabox' ], 10, 2 );
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
			'sl_stages'     => esc_html__( 'Stages', 'sports-leagues' ),
			'tournament_id' => esc_html__( 'ID', 'sports-leagues' ),
			'sl_games_qty'  => esc_html__( 'Games', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'sl_stages',
			'taxonomy-sl_league',
			'taxonomy-sl_season',
			'sl_games_qty',
			'tournament_id',
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
	 * @param array   $column  Column currently being rendered.
	 * @param integer $post_id ID of post to display column for.
	 *
	 *@since  0.1.0
	 *
	 */
	public function columns_display( $column, $post_id ) {

		switch ( $column ) {

			case 'tournament_id':
				echo (int) $post_id;
				break;

			case 'sl_games_qty':
				echo esc_html( wp_get_post_parent_id( $post_id ) ? $this->get_stages_games_qty( $post_id ) : $this->get_tournament_games_qty( $post_id ) );
				break;

			case 'sl_stages':
				$stages   = $this->get_tournament_stages( $post_id );
				$stage_no = 1;

				if ( ! wp_get_post_parent_id( $post_id ) ) {
					foreach ( $stages as $stage ) {
						echo '#' . absint( $stage_no ) . ' / ';
						echo esc_html( $stage->title ) . ' / ';
						echo esc_html( $stage->type ) . ' / ';
						echo 'Stage ID: ' . absint( $stage->id ) . ' / ';
						echo 'Games: ' . absint( $this->get_stages_games_qty( $stage->id ) ) . ' / ';
						echo sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( get_permalink( $stage->id ) ), esc_html__( 'view', 'sports-leagues' ) );
						echo '<br>';

						$stage_no ++;
					}
				}

				break;
		}
	}

	/**
	 * Get root tournaments.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_root_tournament_options( $output = 'id-title' ) {

		$options = [];

		$tournaments = get_posts(
			[
				'numberposts' => - 1,
				'post_type'   => 'sl_tournament',
				'post_parent' => 0,
			]
		);

		/** @var WP_Post $tournament */
		foreach ( $tournaments as $tournament ) {
			if ( 'id-title' === $output ) {
				$options[ $tournament->ID ] = $tournament->post_title;
			} elseif ( 'objects' === $output ) {
				$options[] = (object) [
					'id'    => $tournament->ID,
					'title' => $tournament->post_title,
				];
			}
		}

		return $options;
	}

	/**
	 * Get stage options.
	 *
	 * @return array
	 * @since 0.5.9
	 */
	public function get_stage_options() {
		static $options = null;

		if ( null === $options ) {

			$options      = [];
			$root_options = $this->get_root_tournament_options();

			$stages = get_posts(
				[
					'numberposts'         => - 1,
					'post_type'           => 'sl_tournament',
					'post_parent__not_in' => [ 0 ],
					'orderby'             => 'parent',
				]
			);

			/** @var WP_Post $stage */
			foreach ( $stages as $stage ) {
				$root_tournament       = isset( $root_options[ $stage->post_parent ] ) ? ( $root_options[ $stage->post_parent ] . ' - ' ) : '';
				$options[ $stage->ID ] = $root_tournament . $stage->post_title;
			}
		}

		return $options;
	}

	/**
	 * Get tournament title.
	 *
	 * @param $id
	 *
	 * @return string
	 * @since 0.5.6
	 */
	public function get_title( $id ) {
		$post = get_post( $id );

		return isset( $post->post_title ) ? $post->post_title : '';
	}

	/**
	 * Get stages with groups for selected tournament.
	 * Callback for the rest route "/get-tournament-groups/<tournament_id>".
	 *
	 * @param object $data - WP_REST_Request
	 *
	 * @return string $output_data -
	 *@since 0.1.0
	 */
	public function get_tournament_groups( $data ) {

		$output_data = '';

		// Prepare data
		$id = (int) $data['id'];

		// Check id assigned
		if ( ! $id ) {
			return $output_data;
		}

		$stages = get_posts(
			[
				'post_parent' => $id,
				'numberposts' => - 1,
				'post_type'   => 'sl_tournament',
				'meta_key'    => '_sl_stage_system',
				'meta_value'  => 'group',
			]
		);

		/** @var WP_Post $stage */
		foreach ( $stages as $stage ) {

			foreach ( $this->get_stage_groups( $stage->ID ) as $key => $title ) {
				$output_data .= '<option value="' . $key . '">' . $stage->post_title . ' - ' . $title . '</option>';
			}
		}

		return $output_data;
	}

	/**
	 * Get Stage groups.
	 *
	 * @param $stage_id
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_stage_groups( $stage_id ) {

		$options = [];

		$groups = get_post_meta( (int) $stage_id, '_sl_groups', true );

		// Check group meta data exists
		if ( empty( $groups ) ) {
			return $options;
		}

		$groups = json_decode( $groups );

		// Check groups decoded properly
		if ( null === $groups || ! is_array( $groups ) ) {
			return $options;
		}

		// Add round title to groups
		foreach ( $groups as $group_index => $group ) {

			// Add group option
			$options[ $stage_id . '_' . $group->id ] = $group->title;
		}

		return $options;
	}

	/**
	 * Get list of teams by selected stage and group.
	 *
	 * @param int   $stage_id
	 * @param mixed $group_id ID for one group or 'all'
	 *
	 * @return array    Teams IDs
	 * @since 0.1.0
	 */
	public function get_stage_teams( $stage_id, $group_id ) {

		$groups = get_post_meta( (int) $stage_id, '_sl_groups', true );

		if ( empty( $groups ) ) {
			return [];
		}

		$groups = json_decode( $groups );

		if ( ! is_array( $groups ) ) {
			return [];
		}

		// Get all stage teams
		if ( 'all' === $group_id ) {
			$teams = [];

			foreach ( $groups as $group ) {
				if ( ! empty( $group->teams ) && is_array( $group->teams ) ) {
					$teams = array_merge( $teams, $group->teams );
				}
			}

			return array_unique( $teams );
		}

		// Get teams for one group
		foreach ( $groups as $group ) {
			if ( intval( $group->id ) === intval( $group_id ) && ! empty( $group->teams ) && is_array( $group->teams ) ) {
				return $group->teams;
			}
		}

		return [];
	}

	/**
	 * Get list of teams by Tournament
	 *
	 * @param int $tournament_id
	 *
	 * @return array    Teams IDs
	 */
	public function get_tournament_teams( $tournament_id ) {

		$tournament_obj = sports_leagues()->tournament->get_tournament( $tournament_id );
		$teams_array    = [];

		if ( ! empty( $tournament_obj->stages ) ) {
			foreach ( $tournament_obj->stages as $stage ) {
				$teams_array = array_merge( $teams_array, sports_leagues()->tournament->get_stage_teams( $stage->id, 'all' ) );
			}
		}

		return array_unique( array_map( 'absint', $teams_array ) );
	}

	/**
	 * Get round title.
	 *
	 * @param int $round_id
	 * @param int $stage_id
	 *
	 * @return string
	 * @since 0.1.0
	 */
	public function get_round_title( $round_id, $stage_id ) {
		$output = '';

		$rounds = json_decode( get_post_meta( $stage_id, '_sl_rounds', true ) );

		if ( null !== $rounds && is_array( $rounds ) ) {
			foreach ( $rounds as $round ) {
				if ( intval( $round_id ) === $round->id && ! empty( $round->title ) ) {
					$output = $round->title;
				}
			}
		}

		return $output;
	}

	/**
	 * Get list of tournaments.
	 * Used on admin Standing page.
	 * Used on admin Game page.
	 *
	 * @return array $output_data -
	 * @since 0.1.0
	 */
	public function get_tournaments() {

		static $output_data = null;

		if ( null === $output_data ) {

			$output_data = [];

			$all_tournaments = get_posts(
				[
					'numberposts'      => - 1,
					'post_type'        => 'sl_tournament',
					'suppress_filters' => false,
					'orderby'          => 'name',
					'order'            => 'ASC',
				]
			);

			/*
			|--------------------------------------------------------------------
			| Prepare parent tournaments
			|--------------------------------------------------------------------
			*/
			/** @var WP_Post $tournament */
			foreach ( $all_tournaments as $tournament ) {

				if ( 0 !== $tournament->post_parent ) {
					continue;
				}

				$obj              = (object) [];
				$obj->id          = $tournament->ID;
				$obj->title       = $tournament->post_title;
				$obj->logo        = get_the_post_thumbnail_url( $tournament->ID );
				$obj->season_id   = 0;
				$obj->league_id   = 0;
				$obj->league_text = '';
				$obj->season_text = '';
				$obj->stages      = [];

				// Get Season and League
				$terms = wp_get_post_terms( $tournament->ID, [ 'sl_league', 'sl_season' ] );

				if ( is_array( $terms ) ) {
					foreach ( $terms as $term ) {

						if ( 'sl_league' === $term->taxonomy && $term->term_id ) {
							$obj->league_id   = $term->term_id;
							$obj->league_text = $term->name;
						}

						if ( 'sl_season' === $term->taxonomy ) {
							$obj->season_id   = $term->term_id;
							$obj->season_text = $term->name;
						}
					}
				}

				$output_data[ $tournament->ID ] = $obj;
			}

			/*
			|--------------------------------------------------------------------
			| Prepare child tournaments
			|--------------------------------------------------------------------
			*/
			/** @var WP_Post $tournament */
			foreach ( $all_tournaments as $tournament ) {

				if ( 0 === $tournament->post_parent ) {
					continue;
				}

				if ( ! empty( $output_data[ $tournament->post_parent ] ) ) {
					$obj         = (object) [];
					$obj->id     = $tournament->ID;
					$obj->title  = $tournament->post_title ? : esc_html__( '- no title -', 'sports-leagues' );
					$obj->groups = json_decode( get_post_meta( $tournament->ID, '_sl_groups', true ) );
					$obj->rounds = json_decode( get_post_meta( $tournament->ID, '_sl_rounds', true ) );
					$obj->type   = get_post_meta( $tournament->ID, '_sl_stage_system', true );
					$obj->order  = $tournament->menu_order;

					$output_data[ $tournament->post_parent ]->stages[] = $obj;
				}
			}

			foreach ( $output_data as $root_tournament ) {
				if ( ! empty( $root_tournament->stages ) ) {
					$root_tournament->stages = wp_list_sort( $root_tournament->stages, 'order' );
				}
			}

			$output_data = array_values( $output_data );
		}

		return $output_data;
	}

	/**
	 * Get tournament stages.
	 *
	 * @param $tournament_id
	 *
	 * @return array|mixed
	 * @since 0.7.0
	 */
	public function get_tournament_stages( $tournament_id ) {

		$stages = [];

		$tournaments = $this->get_tournaments();

		if ( ! empty( $tournaments ) ) {
			$filtered_tournaments = array_values( wp_list_filter( $tournaments, [ 'id' => $tournament_id ] ) );

			if ( isset( $filtered_tournaments[0] ) && isset( $filtered_tournaments[0]->stages ) ) {
				$stages = $filtered_tournaments[0]->stages;
			}
		}

		return $stages;
	}

	/**
	 * Get array of tournament for shortcodes by provided arguments.
	 *
	 * @param object|array $options
	 *
	 * @return array|null|object
	 * @since 0.5.17
	 */
	public function get_tournaments_extended( $options ) {

		$options = (object) wp_parse_args(
			$options,
			[
				'status'       => '',
				'sort_by_date' => '',
				'limit'        => 0,
				'exclude_ids'  => '',
				'include_ids'  => '',
				'date_from'    => '',
				'date_to'      => '',
			]
		);

		$args = [
			'post_type'   => 'sl_tournament',
			'post_parent' => 0,
		];

		$meta_query = [];

		/*
		|--------------------------------------------------------------------
		| Status
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $options->status ) && in_array( $options->status, [ 'finished', 'active', 'upcoming' ], true ) ) {
			$current_date = current_time( 'Y-m-d' );

			switch ( $options->status ) {
				case 'finished':
					$meta_query[] = [
						'key'     => '_sl_date_to',
						'value'   => $current_date,
						'compare' => '<',
						'type'    => 'DATE',
					];

					break;

				case 'upcoming':
					$meta_query[] = [
						'key'     => '_sl_date_from',
						'value'   => $current_date,
						'compare' => '>',
						'type'    => 'DATE',
					];

					break;

				case 'active':
					$meta_query[] = [
						'key'     => '_sl_date_from',
						'value'   => $current_date,
						'compare' => '<=',
						'type'    => 'DATE',
					];

					$meta_query[] = [
						'key'     => '_sl_date_to',
						'value'   => $current_date,
						'compare' => '>=',
						'type'    => 'DATE',
					];

					break;
			}
		}

		/*
		|--------------------------------------------------------------------
		| Sort By Date
		|--------------------------------------------------------------------
		*/
		if ( ! empty( trim( $options->sort_by_date ) ) ) {
			if ( in_array( mb_strtoupper( $options->sort_by_date ), [ 'ASC', 'DESC' ], true ) ) {
				$args['meta_key'] = '_sl_date_from';
				$args['orderby']  = 'meta_value_date';
				$args['order']    = mb_strtoupper( $options->sort_by_date );
			}
		}

		/*
		|--------------------------------------------------------------------
		| Exclude & Include IDs
		|--------------------------------------------------------------------
		*/
		$args['exclude'] = $options->exclude_ids;
		$args['include'] = $options->include_ids;

		/*
		|--------------------------------------------------------------------
		| Limit
		|--------------------------------------------------------------------
		*/
		$args['numberposts'] = absint( $options->limit ) ?: - 1;

		/*
		|--------------------------------------------------------------------
		| Date From
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $options->date_from ) && sports_leagues()->helper->validate_date( $options->date_from, 'Y-m-d' ) ) {
			$meta_query[] = [
				'key'     => '_sl_date_from',
				'value'   => $options->date_from,
				'compare' => '>=',
				'type'    => 'DATE',
			];
		}

		/*
		|--------------------------------------------------------------------
		| Date To
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $options->date_to ) && sports_leagues()->helper->validate_date( $options->date_to, 'Y-m-d' ) ) {
			$meta_query[] = [
				'key'     => '_sl_date_to',
				'value'   => $options->date_to,
				'compare' => '<=',
				'type'    => 'DATE',
			];
		}

		/*
		|--------------------------------------------------------------------
		| Meta Query
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		/*
		|--------------------------------------------------------------------
		| Get WP Posts
		|--------------------------------------------------------------------
		*/
		return get_posts( $args );
	}

	/**
	 * Get Standing for tournament (stage).
	 *
	 * @param $stage_id
	 * @param $group_id
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function tmpl_get_stage_standings( $stage_id, $group_id ) {
		$standings = get_posts(
			[
				'ignore_sticky_posts' => true,
				'numberposts'         => 1,
				'post_type'           => 'sl_standing',
				'meta_query'          => [
					[
						'key'   => '_sl_stage_id',
						'value' => $stage_id,
					],
					[
						'key'   => '_sl_group_id',
						'value' => $group_id,
					],
				],
			]
		);

		return $standings;
	}

	/**
	 * Get array of the games for Tournament.
	 *
	 * @param int $stage_id
	 *
	 * @return array|null|object
	 * @since 0.1.0
	 */
	public function get_tournament_games( $stage_id ) {

		global $wpdb;

		$order = ( 'desc' === Sports_Leagues_Options::get_value( 'tournament_gameday_order' ) ) ? 'DESC' : 'ASC';

		$games = $wpdb->get_results(
			$wpdb->prepare(
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"
					SELECT *
					FROM {$wpdb->prefix}sl_games
					WHERE stage_id = %d
					ORDER BY game_day {$order}, kickoff ASC
				",
				// phpcs:enable
				$stage_id
			)
		);

		// Populate Object Cache
		$ids = wp_list_pluck( $games, 'game_id' );

		// Get links
		$game_posts = [];

		$args = [
			'include'       => $ids,
			'post_type'     => 'sl_game',
			'cache_results' => false,
		];

		/** @var WP_Post $game_post */
		foreach ( get_posts( $args ) as $game_post ) {
			$game_posts[ $game_post->ID ] = $game_post;
		}

		// Add extra data to game
		foreach ( $games as $game ) {
			$game->permalink = get_permalink( isset( $game_posts[ $game->game_id ] ) ? $game_posts[ $game->game_id ] : $game->game_id );
			$game->aggtext   = sports_leagues()->game->get_game_aggtext( $game->game_id );
		}

		return $games;
	}

	/**
	 * Get number of games for selected tournament.
	 *
	 * @param $tournament_id
	 *
	 * @return mixed|string
	 * @since 0.5.9
	 */
	public function get_tournament_games_qty( $tournament_id ) {

		static $options = null;

		if ( null === $options ) {
			global $wpdb;

			$options = [];

			$games_qty = $wpdb->get_results(
				"
				SELECT COUNT(*) as qty, tournament_id
				FROM {$wpdb->prefix}sl_games
				GROUP BY tournament_id
				"
			);

			if ( ! empty( $games_qty ) && is_array( $games_qty ) ) {
				$options = wp_list_pluck( $games_qty, 'qty', 'tournament_id' );
			}
		}

		return isset( $options[ $tournament_id ] ) ? $options[ $tournament_id ] : '';
	}

	/**
	 * Get number of games for selected tournament stage.
	 *
	 * @param $stage_id
	 *
	 * @return mixed|string
	 * @since 0.5.9
	 */
	public function get_stages_games_qty( $stage_id ) {

		static $options = null;

		if ( null === $options ) {
			global $wpdb;

			$options = [];

			$games_qty = $wpdb->get_results(
				"
				SELECT COUNT(*) as qty, stage_id
				FROM {$wpdb->prefix}sl_games
				GROUP BY stage_id
				"
			);

			if ( ! empty( $games_qty ) && is_array( $games_qty ) ) {
				$options = wp_list_pluck( $games_qty, 'qty', 'stage_id' );
			}
		}

		return isset( $options[ $stage_id ] ) ? $options[ $stage_id ] : '';
	}

	/**
	 * Renders tabs for metabox. Helper HTML before.
	 *
	 * @since 0.5.12
	 */
	public function cmb2_before_metabox() {
		// @formatter:off
		ob_start();
		?>
		<div class="anwp-b-wrap">
			<div class="anwp-metabox-tabs d-sm-flex">
				<div class="anwp-metabox-tabs__controls d-flex flex-sm-column flex-wrap">
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bottom_content-tournament_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-repo-push"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bottom Content', 'sports-leagues' ); ?></span>
					</div>
					<?php
					/**
					 * Fires in the bottom of match tabs.
					 *
					 * @since 0.5.12
					 */
					do_action( 'sports-leagues/cmb2_tabs_control/tournament' );
					?>
				</div>
				<div class="anwp-metabox-tabs__content pl-4 pb-4">
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// @formatter:on
	}

	/**
	 * Renders tabs for metabox. Helper HTML after.
	 *
	 * @since 0.5.12
	 */
	public function cmb2_after_metabox() {
		// @formatter:off
		ob_start();
		?>
				</div>
			</div>
		</div>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// @formatter:on
	}

	/**
	 * Define the metabox and field configurations.
	 *
	 * @since 0.5.12
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sl_';

		$cmb_side = new_cmb2_box(
			[
				'id'              => 'sl_tournament_side_metabox',
				'title'           => esc_html__( 'Small Logo', 'sports-leagues' ),
				'object_types'    => [ 'sl_tournament' ],
				'context'         => 'side',
				'priority'        => 'low',
				'classes'         => 'anwp-b-wrap',
				'show_names'      => false,
				'remove_box_wrap' => true,
			]
		);

		$cmb_side->add_field(
			[
				'name'         => esc_html__( 'Small Logo', 'sports-leagues' ),
				'id'           => $prefix . 'logo_small',
				'type'         => 'file',
				'query_args'   => [
					'type' => 'image',
				],
				'options'      => [
					'url' => false,
				],
				'preview_size' => 'large',
			]
		);

		$cmb = new_cmb2_box(
			[
				'id'           => 'sl_tournament_cmb2_metabox',
				'title'        => esc_html__( 'Tournament Options', 'sports-leagues' ),
				'object_types' => [ 'sl_tournament' ],
				'priority'     => 'default',
				'classes'      => [ 'anwp-b-wrap', 'anwp-cmb2-metabox' ],
				'show_names'   => true,
				'show_on_cb'   => function ( $cmb ) {
					$has_parent = $cmb->object_id() && get_post_ancestors( $cmb->object_id() );

					return $has_parent;
				},
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Bottom Content
		|--------------------------------------------------------------------------
		*/
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
				'before_row' => '<div id="anwp-tabs-bottom_content-tournament_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'after_row'  => '</div>',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.12
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/tournament', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Define the metabox and field configurations.
	 *
	 * @since 0.5.17
	 */
	public function init_root_cmb2_metaboxes() {

		$tournament_types         = $this->plugin->config->get_options( 'tournament_types' );
		$tournament_types_options = [];

		if ( ! empty( $tournament_types ) && is_array( $tournament_types ) ) {
			foreach ( $tournament_types as $tournament_type ) {
				$tournament_types_options[ $tournament_type['id'] ] = $tournament_type['name'];
			}
		}

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sl_';

		$cmb = new_cmb2_box(
			[
				'id'           => 'sl_tournament_root_cmb2_metabox',
				'title'        => esc_html__( 'Main Tournament Options', 'sports-leagues' ),
				'object_types' => [ 'sl_tournament' ],
				'priority'     => 'low',
				'classes'      => [ 'anwp-b-wrap', 'anwp-cmb2-metabox' ],
				'show_names'   => true,
				'show_on_cb'   => function ( $cmb ) {
					$is_parent = $cmb->object_id() && empty( get_post_ancestors( $cmb->object_id() ) );

					return $is_parent;
				},
			]
		);

		$cmb->add_field(
			[
				'name'             => esc_html__( 'Tournament Type', 'sports-leagues' ),
				'id'               => $prefix . 'tournament_type',
				'type'             => 'select',
				'default'          => '',
				'show_option_none' => '- ' . esc_html__( 'select type', 'sports-leagues' ) . ' -',
				'options'          => $tournament_types_options,
				'column'           => [
					'position' => 4,
				],
			]
		);
	}

	/**
	 * Get competition data.
	 *
	 * @param $id
	 *
	 * @return object|bool
	 *
	 * @since 0.11.0
	 */
	public function get_tournament( $id ) {
		$tournament_obj = array_values( wp_list_filter( $this->get_tournaments(), [ 'id' => absint( $id ) ] ) );

		return empty( $tournament_obj ) ? false : $tournament_obj[0];
	}
}
