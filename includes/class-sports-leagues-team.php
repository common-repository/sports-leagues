<?php
/**
 * Sports Leagues :: Team.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Team {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Teams data.
	 *
	 * @since  0.1.0
	 */
	protected static $teams_data = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * @param  Sports_Leagues $plugin Main plugin object.
	 *
	 * @since  0.1.0
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
		$permalink_slug      = empty( $permalink_structure['team'] ) ? 'team' : $permalink_structure['team'];

		// Register this CPT.
		$labels = [
			'name'                  => _x( 'Teams', 'Post type general name', 'sports-leagues' ),
			'singular_name'         => _x( 'Team', 'Post type singular name', 'sports-leagues' ),
			'menu_name'             => _x( 'Teams', 'Admin Menu text', 'sports-leagues' ),
			'name_admin_bar'        => _x( 'Team', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'               => __( 'Add New Team', 'sports-leagues' ),
			'add_new_item'          => __( 'Add New Team', 'sports-leagues' ),
			'new_item'              => __( 'New Team', 'sports-leagues' ),
			'edit_item'             => __( 'Edit Team', 'sports-leagues' ),
			'view_item'             => __( 'View Team', 'sports-leagues' ),
			'all_items'             => __( 'All Teams', 'sports-leagues' ),
			'search_items'          => __( 'Search Teams', 'sports-leagues' ),
			'not_found'             => __( 'No Teams found.', 'sports-leagues' ),
			'not_found_in_trash'    => __( 'No Teams found in Trash.', 'sports-leagues' ),
			'featured_image'        => __( 'Team logo', 'sports-leagues' ),
			'set_featured_image'    => __( 'Set Team logo', 'sports-leagues' ),
			'remove_featured_image' => __( 'Remove Team logo', 'sports-leagues' ),
			'use_featured_image'    => __( 'Use as Team logo', 'sports-leagues' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_position'      => 45,
			'menu_icon'          => 'dashicons-shield',
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

		register_post_type( 'sl_team', $args );
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {

		// Create CMB2 Metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );

		// Create & save Roaster metabox
		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );
		add_action( 'save_post', [ $this, 'save_metabox' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'load_data_to_script' ] );

		// Render Custom Content below
		add_action(
			'sports-leagues/tmpl-team/after_wrapper',
			function ( $team ) {

				$content_below = get_post_meta( $team->ID, '_sl_bottom_content', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Filters the title field placeholder text.
		add_filter( 'enter_title_here', [ $this, 'title' ], 10, 2 );

		// Modifies columns in Admin tables
		add_action( 'manage_sl_team_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_team_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-sl_team_sortable_columns', [ $this, 'sortable_columns' ] );
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

		if ( isset( $post->post_type ) && 'sl_team' === $post->post_type ) {
			return esc_html__( 'Team Name', 'sports-leagues' );
		}

		return $title;
	}

	/**
	 * Injects some data to the script.
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.1.0
	 */
	public function load_data_to_script( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && 'sl_team' === $current_screen->id ) {

			$post_id = get_the_ID();

			wp_localize_script(
				'sl_admin',
				'slRoster',
				[
					'roster'              => get_post_meta( $post_id, '_sl_roster', true ),
					'staff'               => get_post_meta( $post_id, '_sl_staff', true ),
					'team'                => $post_id,
					'players'             => $this->plugin->player->get_players_list(),
					'teams_map'           => $this->get_team_options(),
					'optionsPositionMap'  => $this->plugin->config->get_player_positions(),
					'staffs'              => $this->plugin->staff->get_staff_list(),
					'loader'              => includes_url( 'js/tinymce/skins/lightgray/img/loader.gif' ),
					'roster_groups'       => sports_leagues()->config->get_options( 'roster_groups' ),
					'roster_status'       => sports_leagues()->config->get_options( 'roster_status' ),
					'roster_staff_groups' => sports_leagues()->config->get_options( 'staff_roster_groups' ),
					'seasons_list'        => sports_leagues()->season->get_seasons_list(),
					'positions'           => sports_leagues()->config->get_options( 'position' ),
					'default_photo'       => sports_leagues()->helper->get_default_player_photo(),
				]
			);
		}
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

				if ( 'sl_team' === $post_type ) {
					add_meta_box(
						'sl_team_roster',
						esc_html__( 'Team Roster', 'sports-leagues' ),
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

		// Check Season exists
		$seasons_qty = (int) get_terms(
			[
				'taxonomy'   => 'sl_season',
				'hide_empty' => false,
				'fields'     => 'count',
			]
		);

		if ( $seasons_qty ) :

			$is_menu_collapsed = 'yes' === get_user_setting( 'anwp-sl-collapsed-menu' );

			// Add nonce for security and authentication.
			wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );
			?>
			<div class="anwp-b-wrap sl-roster-metabox-wrapper">
				<div class="d-flex mt-2" id="anwp-sl-metabox-page-nav">

					<div class="anwp-sl-menu-wrapper mr-3 d-none d-md-block sticky-top align-self-start anwp-flex-none <?php echo esc_attr( $is_menu_collapsed ? 'anwp-sl-collapsed-menu' : '' ); ?>" style="top: 50px;">

						<button id="anwp-sl-publish-click-proxy" class="w-100 button button-primary py-2 mb-4 d-flex align-items-center justify-content-center" type="submit">
							<svg class="anwp-icon anwp-icon--feather anwp-icon--s16"><use xlink:href="#icon-save"></use></svg>
							<span class="ml-2"><?php echo esc_html__( 'Save', 'sports-leagues' ); ?></span>
							<span class="spinner m-0"></span>
						</button>

						<ul class="m-0 p-0 list-unstyled">

							<?php
							$nav_items = [
								[
									'icon'  => 'gear',
									'label' => __( 'General', 'sports-leagues' ),
									'slug'  => 'anwp-sl-general-metabox',
								],
								[
									'icon'  => 'note',
									'label' => __( 'Description', 'sports-leagues' ),
									'slug'  => 'anwp-sl-desc-metabox',
								],
								[
									'icon'  => 'repo-forked',
									'label' => __( 'Social', 'sports-leagues' ),
									'slug'  => 'anwp-sl-social-metabox',
								],
								[
									'icon'  => 'database',
									'label' => __( 'Subteams', 'sports-leagues' ),
									'slug'  => 'anwp-sl-subteams-metabox',
								],
								[
									'icon'  => 'device-camera',
									'label' => __( 'Gallery', 'sports-leagues' ),
									'slug'  => 'anwp-sl-media-metabox',
								],
								[
									'icon'  => 'server',
									'label' => __( 'Custom Fields', 'sports-leagues' ),
									'slug'  => 'anwp-sl-custom_fields-metabox',
								],
								[
									'icon'  => 'repo-push',
									'label' => __( 'Bottom Content', 'sports-leagues' ),
									'slug'  => 'anwp-sl-bottom_content-metabox',
								],
								[
									'icon'  => 'jersey',
									'label' => __( 'Roster', 'sports-leagues' ),
									'slug'  => 'anwp-sl-roster-metabox',
								],
								[
									'icon'  => 'organization',
									'label' => __( 'Staff', 'sports-leagues' ),
									'slug'  => 'anwp-sl-staff-metabox',
								],
							];

							/**
							 * Modify metabox nav items
							 *
							 * @since 0.10.0
							 */
							$nav_items = apply_filters( 'sports-leagues/team/metabox_nav_items', $nav_items );

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo sports_leagues()->helper->create_metabox_navigation( $nav_items );

							/**
							 * Fires at the bottom of Metabox Nav.
							 *
							 * @since 0.10.0
							 */
							do_action( 'sports-leagues/team/metabox_nav_bottom' );
							?>
						</ul>
					</div>

					<div class="flex-grow-1 anwp-min-width-0 mb-4">

						<?php cmb2_metabox_form( 'sl_team_metabox' ); ?>

						<div id="sl-app-roster"></div>

						<?php
						/**
						 * Fires at the bottom of Metabox.
						 *
						 * @since 0.10.0
						 */
						do_action( 'sports-leagues/team/metabox_bottom' );
						?>
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="anwp-b-wrap">
				<div class="anwp-border anwp-border-gray-500">
					<div class="anwp-border-bottom anwp-border-gray-500 bg-white d-flex align-items-center px-3 py-2 anwp-text-gray-700 anwp-font-semibold">
						<svg class="anwp-icon anwp-icon--s16 mr-2 anwp-fill-current">
							<use xlink:href="#icon-jersey"></use>
						</svg>
						<?php echo esc_html__( 'Team Roster', 'sports-leagues' ); ?>
					</div>

					<div class="bg-white p-3">
						<div class="anwp-bg-orange-200 my-0 py-3 px-3 anwp-border anwp-border-orange-400" role="alert">
							<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=sl_season&post_type=sl_tournament' ) ); ?>" target="_blank"><?php echo esc_html__( 'Please, create a season first.', 'sports-leagues' ); ?></a>
						</div>
					</div>
				</div>
			</div>
			<?php
		endif;
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @since  0.1.0
	 * @return bool|int
	 */
	public function save_metabox( $post_id ) {

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
		if ( 'sl_team' !== $_POST['post_type'] ) {
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
		 * Save Roster Data
		 * ---------------------------------------*/

		// Prepare data + Encode with some WP sanitization
		$roster = wp_json_encode( json_decode( wp_unslash( $_POST['_sl_roster'] ) ) );
		$staff  = wp_json_encode( json_decode( wp_unslash( $_POST['_sl_staff'] ) ) );

		if ( $roster ) {
			update_post_meta( $post_id, '_sl_roster', wp_slash( $roster ) );
		}

		if ( $staff ) {
			update_post_meta( $post_id, '_sl_staff', wp_slash( $staff ) );
		}

		/**
		 * Trigger on save team data.
		 *
		 * @param array $post_id
		 * @param array $_POST
		 *
		 * @since 0.10.0
		 */
		do_action( 'sports-leagues/team/on_save', $post_id, $_POST );

		return $post_id;
	}

	/**
	 * Create CMB2 metaboxes
	 *
	 * @since 0.1.0
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sl_';

		$cmb_side = new_cmb2_box(
			[
				'id'              => 'sl_team_side_metabox',
				'title'           => esc_html__( 'Small Logo', 'sports-leagues' ),
				'object_types'    => [ 'sl_team' ],
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

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			[
				'id'              => 'sl_team_metabox',
				'object_types'    => [ 'sl_team' ],
				'context'         => 'advanced',
				'priority'        => 'high',
				'classes'         => 'anwp-b-wrap',
				'save_button'     => '',
				'show_names'      => true,
				'remove_box_wrap' => true,
			]
		);

		// Abbreviation
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Abbreviation/Short Name', 'sports-leagues' ),
				'id'         => $prefix . 'abbr',
				'type'       => 'text',
				'before_row' => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'gear',
						'label' => __( 'General', 'sports-leagues' ),
						'slug'  => 'anwp-sl-general-metabox',
					]
				),
			]
		);

		// Code
		$cmb->add_field(
			[
				'name' => esc_html__( 'Team Code (2-4 letters)', 'sports-leagues' ),
				'id'   => $prefix . 'code',
				'type' => 'text_small',
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

		$cmb->add_field(
			[
				'name' => esc_html__( 'City', 'sports-leagues' ),
				'id'   => $prefix . 'city',
				'type' => 'text',
			]
		);

		// Country
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Country', 'sports-leagues' ),
				'id'         => $prefix . 'nationality',
				'type'       => 'anwp_sl_select',
				'options_cb' => [ $this->plugin->data, 'get_countries' ],
			]
		);

		// Venue
		$cmb->add_field(
			[
				'name'             => esc_html__( 'Home Venue', 'sports-leagues' ),
				'id'               => $prefix . 'venue',
				'type'             => 'select',
				'show_option_none' => esc_html__( '- not selected -', 'sports-leagues' ),
				'options_cb'       => [ $this->plugin->venue, 'get_venue_options' ],
			]
		);

		// Address
		$cmb->add_field(
			[
				'name' => esc_html__( 'Address', 'sports-leagues' ),
				'id'   => $prefix . 'address',
				'type' => 'text',
			]
		);

		// Website
		$cmb->add_field(
			[
				'name' => esc_html__( 'Website', 'sports-leagues' ),
				'id'   => $prefix . 'website',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Founded', 'sports-leagues' ),
				'id'   => $prefix . 'founded',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Conference', 'sports-leagues' ),
				'id'   => $prefix . 'conference',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Division', 'sports-leagues' ),
				'id'   => $prefix . 'division',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Team Main Color', 'sports-leagues' ),
				'id'      => $prefix . 'main_color',
				'type'    => 'colorpicker',
				'default' => '',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'National Team', 'sports-leagues' ),
				'id'      => $prefix . 'is_national_team',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''    => esc_html__( 'no', 'sports-leagues' ),
					'yes' => esc_html__( 'yes', 'sports-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'        => esc_html__( 'External ID', 'sports-leagues' ),
				'id'          => $prefix . 'team_external_id',
				'type'        => 'text',
				'description' => esc_html__( 'Used on Data Import', 'sports-leagues' ),
				'after_row'   => '</div></div>',
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Description', 'sports-leagues' ),
				'id'         => $prefix . 'description',
				'type'       => 'wysiwyg',
				'options'    => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_team_description_input',
					'textarea_rows' => 8,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names' => false,
				'before_row' => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'note',
						'label' => __( 'Description', 'sports-leagues' ),
						'slug'  => 'anwp-sl-desc-metabox',
					]
				),
				'after_row'  => '</div></div>',
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
				'type'       => 'text_url',
				'protocols'  => [ 'http', 'https' ],
				'before_row' => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'repo-forked',
						'label' => __( 'Social', 'sports-leagues' ),
						'slug'  => 'anwp-sl-social-metabox',
					]
				),
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
				'after_row' => '</div></div>',
			]
		);

		/*
		|--------------------------------------------------------------------
		| Subteams
		|--------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Subteam Status', 'sports-leagues' ),
				'id'         => $prefix . 'subteams',
				'before_row' => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'repo-forked',
						'label' => __( 'Subteams', 'sports-leagues' ),
						'slug'  => 'anwp-sl-subteams-metabox',
					]
				),
				'type'       => 'select',
				'default'    => '',
				'attributes' => [
					'class'     => 'cmb2_select anwp-sl-parent-of-dependent',
					'data-name' => $prefix . 'subteams',
				],
				'options'    => [
					''        => esc_html__( 'no', 'sports-leagues' ),
					'root'    => esc_html__( 'root team / summary', 'sports-leagues' ),
					'subteam' => esc_html__( 'subteam', 'sports-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Root Team', 'sports-leagues' ),
				'id'         => $prefix . 'root_team',
				'options_cb' => [ $this->plugin->team, 'get_root_team_options' ],
				'type'       => 'anwp_sl_select',
				'before_row' => '<div class="cmb-row"><div class="anwp-sl-dependent-field" data-parent="' . $prefix . 'subteams" data-action="show" data-value="subteam">',
				'after_row'  => '</div></div>',
				'attributes' => [
					'placeholder' => esc_html__( '- not selected -', 'sports-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Root Page Type', 'sports-leagues' ),
				'id'         => $prefix . 'root_type',
				'before_row' => '<div class="cmb-row"><div class="anwp-sl-dependent-field" data-parent="' . $prefix . 'subteams" data-action="show" data-value="root">',
				'after_row'  => '</div></div>',
				'type'       => 'select',
				'default'    => 'team',
				'options'    => [
					'team'    => esc_html__( 'root team', 'sports-leagues' ),
					'summary' => esc_html__( 'summary page', 'sports-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Root Team Title', 'sports-leagues' ),
				'id'         => $prefix . 'root_team_title',
				'type'       => 'text',
				'before_row' => '<div class="cmb-row"><div class="anwp-sl-dependent-field" data-parent="' . $prefix . 'subteams" data-action="show" data-value="root">',
				'after_row'  => '</div></div>',
			]
		);

		$group_field_id = $cmb->add_field(
			[
				'id'           => $prefix . 'subteam_list',
				'type'         => 'group',
				'repeatable'   => true,
				'before_group' => '<div class="cmb-row"><div class="anwp-sl-dependent-field" data-parent="' . $prefix . 'subteams" data-action="show" data-value="root">',
				'after_group'  => '</div></div>',
				'options'      => [
					'group_title'   => __( 'Subteam {#}', 'sports-leagues' ),
					'add_button'    => __( 'Add Another Subteam', 'sports-leagues' ),
					'remove_button' => __( 'Remove Subteam', 'sports-leagues' ),
					'sortable'      => true,

				],
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name' => __( 'Subteam Title', 'sports-leagues' ),
				'id'   => 'title',
				'type' => 'text',
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'       => esc_html__( 'Subteam', 'sports-leagues' ),
				'id'         => 'subteam',
				'options_cb' => [ $this->plugin->team, 'get_team_options' ],
				'type'       => 'anwp_sl_select',
				'attributes' => [
					'placeholder' => esc_html__( '- not selected -', 'sports-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| Gallery Tab
		|--------------------------------------------------------------------
		*/
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
				'before_row'   => '</div></div>' . sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'device-camera',
						'label' => __( 'Gallery', 'sports-leagues' ),
						'slug'  => 'anwp-sl-media-metabox',
					]
				),
			]
		);

		// Notes
		$cmb->add_field(
			[
				'name'      => esc_html__( 'Text below gallery', 'sports-leagues' ),
				'id'        => $prefix . 'gallery_notes',
				'type'      => 'textarea_small',
				'after_row' => '</div></div>',
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
				'option_slug' => 'team_custom_fields',
				'after_row'   => '</div></div>',
				'before_row'  => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'server',
						'label' => __( 'Custom Fields', 'sports-leagues' ),
						'slug'  => 'anwp-sl-custom_fields-metabox',
					]
				),
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
				'after_row'  => '</div></div>',
				'before_row' => sports_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'repo-push',
						'label' => __( 'Bottom Content', 'sports-leagues' ),
						'slug'  => 'anwp-sl-bottom_content-metabox',
					]
				),
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.13
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/team', [] );

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

		return array_merge( $sortable_columns, [ 'team_id' => 'ID' ] );
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {
		// Add new columns
		$new_columns = [
			'sl_team_logo'  => esc_html__( 'Logo', 'sports-leagues' ),
			'team_id'       => esc_html__( 'ID', 'sports-leagues' ),
			'sl_team_color' => esc_html__( 'Team Color', 'sports-leagues' ),
			'sl_team_abbr'  => esc_html__( 'Abbr', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'sl_team_logo',
			'sl_team_abbr',
			'sl_team_color',
			'comments',
			'date',
			'team_id',
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
	 * Handles admin column display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @param array   $column  Column currently being rendered.
	 * @param integer $post_id ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {
			case 'sl_team_logo':
				echo get_the_post_thumbnail( $post_id, 'thumbnail' );
				break;

			case 'sl_team_abbr':
				echo esc_html( get_post_meta( $post_id, '_sl_abbr', true ) );
				echo '<br>';
				echo esc_html( get_post_meta( $post_id, '_sl_code', true ) );
				break;

			case 'sl_team_color':
				$team_color = get_post_meta( $post_id, '_sl_main_color', true );
				if ( $team_color ) {
					echo '<div style="background-color: ' . esc_attr( $team_color ) . '; width: 30px; height: 50px;">&nbsp;</div>';
				}
				break;

			case 'team_id':
				echo (int) $post_id;
				break;
		}
	}

	/**
	 * Method returns team options.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function get_team_options() {

		static $options = null;

		if ( null === $options ) {

			$options = [];

			$teams = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_team',
					'order'       => 'ASC',
					'orderby'     => 'title',
				]
			);

			/** @var WP_Post $team */
			foreach ( $teams as $team ) {
				$options[ $team->ID ] = $team->post_title;
			}
		}

		return $options;
	}

	/**
	 * Get teams as array of objects.
	 *
	 * @since 0.1.0
	 * @return array $output_data - Array of teams data (id, title)
	 */
	public function get_team_objects() {

		static $options = null;

		if ( null === $options ) {

			$options = [];

			$all_teams = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'sl_team',
					'order'       => 'ASC',
					'orderby'     => 'title',
				]
			);

			/** @var WP_Post $team */
			foreach ( $all_teams as $team ) {
				$team_obj             = new stdClass();
				$team_obj->id         = $team->ID;
				$team_obj->title      = $team->post_title;
				$team_obj->logo_id    = get_post_meta( $team->ID, '_thumbnail_id', true ) && ! is_wp_error( get_post_meta( $team->ID, '_thumbnail_id', true ) ) ? get_post_meta( $team->ID, '_thumbnail_id', true ) : '';
				$team_obj->logo       = '';
				$team_obj->logo_small = get_post_meta( $team->ID, '_sl_logo_small', true );
				$team_obj->link       = get_permalink( $team->ID );
				$team_obj->abbr       = get_post_meta( $team->ID, '_sl_abbr', true );
				$team_obj->code       = get_post_meta( $team->ID, '_sl_code', true );

				$options[] = $team_obj;
			}

			$logo_ids = wp_list_pluck( $options, 'logo_id' );
			$logo_map = $this->plugin->helper->get_thumb_urls_by_post_ids( $logo_ids );

			$upload_dir      = wp_get_upload_dir();
			$upload_dir_path = empty( $upload_dir['baseurl'] ) ? '' : trailingslashit( $upload_dir['baseurl'] );

			if ( is_ssl() ) {
				$upload_dir_path = str_replace( 'http://', 'https://', $upload_dir_path );
			}

			foreach ( $options as $option ) {

				if ( ! empty( $logo_map[ $option->logo_id ] ) && ! empty( $logo_map[ $option->logo_id ]->meta_value ) ) {
					$option->logo = $upload_dir_path . $logo_map[ $option->logo_id ]->meta_value;
				}
			}
		}

		return $options;
	}

	/**
	 * Get Team title by id
	 *
	 * @param int $team_id
	 *
	 * @since 0.1.0
	 * @return string - Team title
	 */
	public function get_team_title_by_id( $team_id ) {

		// Check and validate id
		if ( ! absint( $team_id ) ) {
			return '';
		}

		$team_options = $this->get_team_options();

		return empty( $team_options[ $team_id ] ) ? '' : $team_options[ $team_id ];
	}

	/**
	 * Get Team data by id
	 *
	 * @param int $team_id
	 *
	 * @since 0.1.0
	 * @return bool|object
	 */
	public function get_team_by_id( $team_id ) {

		// Check and validate id
		if ( ! intval( $team_id ) ) {
			return false;
		}

		// Check teams cache
		$this->populate_teams_data();

		return empty( self::$teams_data[ $team_id ] ) ? false : self::$teams_data[ $team_id ];
	}

	/**
	 * Prepare teams
	 *
	 * @since 0.1.0
	 */
	protected function populate_teams_data() {

		if ( null !== self::$teams_data ) {
			return;
		}

		$teams_data = [];

		foreach ( $this->get_team_objects() as $obj ) {
			$teams_data[ $obj->id ] = $obj;
		}

		self::$teams_data = $teams_data;
	}

	/**
	 * Helper function to prepare team roster.
	 *
	 * @param int  $team_id
	 * @param int  $season_id
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function tmpl_prepare_team_roster( $team_id, $season_id ) {

		$roster_raw  = json_decode( get_post_meta( $team_id, '_sl_roster', true ) );
		$season_slug = 's:' . (int) $season_id;

		$roster_output = [];

		if ( null === $roster_raw || ! isset( $roster_raw->{$season_slug} ) || ! is_array( $roster_raw->{$season_slug} ) ) {
			return $roster_output;
		}

		/*
		|--------------------------------------------------------------------
		| Prepare WP_Post Objects
		|--------------------------------------------------------------------
		*/
		$players_ids = array_values( wp_list_pluck( wp_list_filter( $roster_raw->{$season_slug}, [ 'type' => 'player' ] ), 'id' ) );

		$players_query = get_posts(
			[
				'post_type' => 'sl_player',
				'include'   => $players_ids,
			]
		);

		$player_objects = [];

		foreach ( $players_query as $player_query_object ) {
			$player_objects[ $player_query_object->ID ] = $player_query_object;
		}

		$player_photos = $this->plugin->player->get_player_photo_map();

		/*
		|--------------------------------------------------------------------
		| Prepare Roster Output
		|--------------------------------------------------------------------
		*/
		foreach ( $roster_raw->{$season_slug} as $roster_item ) {

			if ( empty( $roster_item->type ) ) {
				continue;
			}

			if ( 'group' === $roster_item->type ) {
				$roster_output[ $roster_item->id ] = (array) $roster_item;
			} elseif ( 'player' === $roster_item->type && ! empty( $player_objects[ $roster_item->id ] ) ) {

				$current_player = $player_objects[ $roster_item->id ];

				try {
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $current_player->_sl_date_of_birth );
					$age            = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
				} catch ( Exception $e ) {
					$age = '-';
				}

				$roster_output[ $roster_item->id ] = [
					'role'        => isset( $roster_item->role ) ? $roster_item->role : '',
					'number'      => isset( $roster_item->number ) ? $roster_item->number : '',
					'status'      => isset( $roster_item->status ) ? $roster_item->status : '',
					'type'        => isset( $roster_item->type ) ? $roster_item->type : '',
					'title'       => isset( $roster_item->title ) ? $roster_item->title : '',
					'photo'       => isset( $player_photos[ $roster_item->id ] ) ? $player_photos[ $roster_item->id ] : '',
					'name'        => $current_player->post_title,
					'age'         => $age,
					'age2'        => $current_player->_sl_date_of_birth,
					'link'        => get_permalink( $current_player ),
					'nationality' => maybe_unserialize( $current_player->_sl_nationality ),
				];
			}
		}

		return $roster_output;
	}

	/**
	 * Helper function to prepare team staff roster.
	 *
	 * @param int  $team_id
	 * @param int  $season_id
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function tmpl_prepare_team_staff_roster( $team_id, $season_id ) {

		$roster_raw  = json_decode( get_post_meta( $team_id, '_sl_staff', true ) );
		$season_slug = 's:' . (int) $season_id;

		$roster = [];

		if ( null === $roster_raw || ! isset( $roster_raw->{$season_slug} ) || ! is_array( $roster_raw->{$season_slug} ) ) {
			return $roster;
		}

		foreach ( $roster_raw->{$season_slug} as $s ) {
			$roster[ $s->id ] = [
				'job'   => isset( $s->job ) ? $s->job : '',
				'type'  => isset( $s->type ) ? $s->type : '',
				'title' => isset( $s->title ) ? $s->title : '',
			];

			$roster[ $s->id ]['name']        = '';
			$roster[ $s->id ]['photo']       = '';
			$roster[ $s->id ]['nationality'] = [];
			$roster[ $s->id ]['age']         = '';
			$roster[ $s->id ]['age2']        = '';
		}

		$players_ids = array_filter( array_map( 'intval', array_keys( $roster ) ) );

		$players_query = get_posts(
			[
				'post_type' => 'sl_staff',
				'include'   => $players_ids,
			]
		);

		foreach ( $players_query as $player ) {

			if ( isset( $roster[ $player->ID ] ) ) {

				try {
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $player->_sl_date_of_birth );
					$age            = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
				} catch ( Exception $e ) {
					$age = '-';
				}

				$roster[ $player->ID ]['name']        = $player->post_title;
				$roster[ $player->ID ]['photo']       = get_the_post_thumbnail_url( $player->ID );
				$roster[ $player->ID ]['nationality'] = maybe_unserialize( $player->_sl_nationality );
				$roster[ $player->ID ]['age']         = $age;
				$roster[ $player->ID ]['age2']        = $player->_sl_date_of_birth;
			}
		}

		return $roster;
	}

	/**
	 * Helper function to prepare team staff roster.
	 *
	 * @param int  $team_id
	 * @param int  $season_id
	 *
	 * @since 0.5.14
	 * @return array
	 */
	public function tmpl_prepare_staff_team_roster( $team_id, $season_id ) {

		$roster_raw  = json_decode( get_post_meta( $team_id, '_sl_staff', true ) );
		$season_slug = 's:' . (int) $season_id;

		$roster = [];

		if ( null === $roster_raw || ! isset( $roster_raw->{$season_slug} ) || ! is_array( $roster_raw->{$season_slug} ) ) {
			return $roster;
		}

		foreach ( $roster_raw->{$season_slug} as $s ) {
			$roster[ $s->id ] = [
				'job'   => isset( $s->job ) ? $s->job : '',
				'type'  => isset( $s->type ) ? $s->type : '',
				'title' => isset( $s->title ) ? $s->title : '',
			];

			$roster[ $s->id ]['name']        = '';
			$roster[ $s->id ]['photo']       = '';
			$roster[ $s->id ]['nationality'] = [];
			$roster[ $s->id ]['age']         = '';
			$roster[ $s->id ]['age2']        = '';
		}

		$players_ids = array_filter( array_map( 'intval', array_keys( $roster ) ) );

		$players_query = get_posts(
			[
				'post_type' => 'sl_staff',
				'include'   => $players_ids,
			]
		);

		foreach ( $players_query as $player ) {

			if ( isset( $roster[ $player->ID ] ) ) {

				try {
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $player->_sl_date_of_birth );
					$age            = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
				} catch ( Exception $e ) {
					$age = '-';
				}

				$roster[ $player->ID ]['name']        = $player->post_title;
				$roster[ $player->ID ]['photo']       = get_the_post_thumbnail_url( $player->ID );
				$roster[ $player->ID ]['nationality'] = maybe_unserialize( $player->_sl_nationality );
				$roster[ $player->ID ]['age']         = $age;
				$roster[ $player->ID ]['age2']        = $player->_sl_date_of_birth;
			}
		}

		return $roster;
	}

	/**
	 * Get teams players for selected season.
	 *
	 * @param array $args
	 *
	 * @return array $output_data
	 * @since 0.5.3
	 */
	public function get_team_season_players( $args, $output = '' ) {

		$output_data = [];

		// Prepare data
		$team_id   = (int) $args['team_id'];
		$season_id = (int) $args['season_id'];

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
			$output_data = $squad_all->{'s:' . $season_id};

			$output_data = wp_list_filter( $output_data, [ 'type' => 'player' ] );

			if ( 'short' === $output ) {
				return $output_data;
			}

			foreach ( $output_data as $player ) {

				$player_obj = $this->plugin->player->get_player( $player->id );

				$player->name        = $player_obj->name;
				$player->nationality = is_array( $player_obj->nationality ) ? implode( ',', $player_obj->nationality ) : '';
			}
		}

		return $output_data;
	}

	/**
	 * Get teams staff for selected season.
	 *
	 * @param array $args
	 *
	 * @return array $output_data
	 * @since 0.5.14
	 */
	public function get_team_season_staff( $args ) {

		$output_data = [];

		// Prepare data
		$team_id   = (int) $args['team_id'];
		$season_id = (int) $args['season_id'];

		// Check season id assigned
		if ( ! $season_id || ! $team_id ) {
			return $output_data;
		}

		// Get team squad meta (for all seasons)
		$squad_all = json_decode( get_post_meta( $team_id, '_sl_staff', true ) );

		if ( empty( $squad_all ) ) {
			return $output_data;
		}

		if ( ! empty( $squad_all->{'s:' . $season_id} ) ) {
			$output_data = $squad_all->{'s:' . $season_id};

			$output_data = wp_list_filter( $output_data, [ 'type' => 'staff' ] );

			foreach ( $output_data as $staff ) {

				$staff_post = get_post( $staff->id );

				$staff->name        = $staff_post->post_title;
				$staff->nationality = is_array( $staff_post->nationality ) ? implode( ',', $staff_post->nationality ) : '';
			}
		}

		return empty( $output_data ) ? [] : array_values( $output_data );
	}

	/**
	 * Get team color.
	 *
	 * @param int  $team_id
	 * @param bool $is_home
	 *
	 * @return string $output_data
	 * @since 0.5.3
	 */
	public function get_team_color( $team_id, $is_home = true ) {

		$color = get_post_meta( $team_id, '_sl_main_color', true );

		if ( empty( $color ) ) {
			$color = $is_home ? '#0085ba' : '#dc3545';
		}

		return $color;
	}

	/**
	 * Helper function, returns national Teams map with id and title
	 *
	 * @since 0.10.2
	 * @return array $output_data - Array of teams data (id => title)
	 */
	public function get_national_team_options() {

		static $options = null;

		if ( null === $options ) {
			global $wpdb;

			$clubs = $wpdb->get_results(
				"
				SELECT m.post_id, p.post_title
				FROM $wpdb->postmeta m
				LEFT JOIN $wpdb->posts p ON m.post_id = p.ID
				WHERE m.meta_key = '_sl_is_national_team' AND m.meta_value = 'yes'
				"
			);

			foreach ( $clubs as $club ) {
				$options[ $club->post_id ] = $club->post_title;
			}
		}

		return $options;
	}

	/**
	 * Get Post ID by External id
	 *
	 * @param $external_id
	 *
	 * @return string|null
	 * @since 0.10.2
	 */
	public function get_team_id_by_external_id( $external_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_sl_team_external_id' AND meta_value = %s
				",
				$external_id
			)
		);
	}

	/**
	 * Get squad seasons ids
	 *
	 * @param int $club_id
	 *
	 * @return array $output_data
	 * @since 0.11.0
	 */
	public function get_team_squad_season_ids( $club_id ) {

		// Get squad data
		$squad = json_decode( get_post_meta( $club_id, '_sl_roster', true ) );

		if ( empty( $squad ) ) {
			return [];
		}

		$squad_seasons = [];

		foreach ( $squad as $squad_slug => $squad_data ) {
			if ( empty( $squad_data ) ) {
				continue;
			}

			$squad_seasons[] = str_replace( 's:', '', $squad_slug );
		}

		return $squad_seasons ? : [];
	}

	/**
	 * Helper function, returns clubs map with id and title
	 *
	 * @since 0.12.1
	 * @return array $output_data - Array of clubs data (id => title)
	 */
	public function get_root_team_options() {

		global $wpdb;

		$root_ids = $wpdb->get_col(
			"
			SELECT post_id
			FROM $wpdb->postmeta
			WHERE meta_key = '_sl_subteams' AND meta_value = 'root'
			"
		);

		$options = [];

		foreach ( $this->get_team_options() as $team_id => $team_title ) {
			if ( in_array( (string) $team_id, $root_ids, true ) ) {
				$options[ $team_id ] = $team_title;
			}
		}

		return $options;
	}

	/**
	 * Get subteam ids
	 *
	 * @param int $team_id
	 * @param bool $array_output
	 *
	 * @return string|int|array
	 * @since 0.12.1
	 */
	public function get_subteam_ids( $team_id, $array_output = false ) {

		if ( 'summary' === get_post_meta( $team_id, '_sl_root_type', true ) ) {
			$subteam_list = get_post_meta( $team_id, '_sl_subteam_list', true );

			if ( ! empty( $subteam_list ) && is_array( $subteam_list ) ) {
				$team_ids = [];

				foreach ( $subteam_list as $subteam_item ) {
					$team_ids[] = $subteam_item['subteam'];
				}

				$output = $array_output ? $team_ids : implode( ',', $team_ids );
			}
		}

		return empty( $output ) ? $team_id : $output;
	}
}
