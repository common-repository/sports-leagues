<?php
/**
 * Sports Leagues :: Staff
 *
 * @since   0.5.14
 * @package Sports_Leagues
 */

class Sports_Leagues_Staff {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.5.14
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Type.
	 *
	 * @since  0.5.14
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
	 * @since 0.5.14
	 */
	public function register_post_type( $plugin ) {

		$permalink_structure = $plugin->options->get_permalink_structure();
		$permalink_slug      = empty( $permalink_structure['staff'] ) ? 'staff' : $permalink_structure['staff'];

		// Register this CPT.
		$labels = [
			'name'                  => _x( 'Staffs', 'Post type general name', 'sports-leagues' ),
			'singular_name'         => _x( 'Staff', 'Post type singular name', 'sports-leagues' ),
			'name_admin_bar'        => _x( 'Staff', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'               => __( 'Add New Staff', 'sports-leagues' ),
			'add_new_item'          => __( 'Add New Staff', 'sports-leagues' ),
			'new_item'              => __( 'New Staff', 'sports-leagues' ),
			'edit_item'             => __( 'Edit Staff', 'sports-leagues' ),
			'view_item'             => __( 'View Staff', 'sports-leagues' ),
			'all_items'             => __( 'All Staffs', 'sports-leagues' ),
			'search_items'          => __( 'Search Staffs', 'sports-leagues' ),
			'not_found'             => __( 'No Staffs found.', 'sports-leagues' ),
			'not_found_in_trash'    => __( 'No Staffs found in Trash.', 'sports-leagues' ),
			'featured_image'        => __( 'Staff photo', 'sports-leagues' ),
			'set_featured_image'    => __( 'Set Staff photo', 'sports-leagues' ),
			'remove_featured_image' => __( 'Remove Staff photo', 'sports-leagues' ),
			'use_featured_image'    => __( 'Use as Staff photo', 'sports-leagues' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=sl_player',
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

		register_post_type( 'sl_staff', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.5.14
	 */
	public function hooks() {

		// Create CMB2 metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );

		// Add tabs functionality for metabox
		add_action( 'cmb2_before_post_form_sl_staff_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_post_form_sl_staff_metabox', [ $this, 'cmb2_after_metabox' ] );

		// Filters the title field placeholder text.
		add_filter( 'enter_title_here', [ $this, 'title' ], 10, 2 );

		// Render Custom Content below
		add_action(
			'sports-leagues/tmpl-staff/after_wrapper',
			function ( $person_id ) {

				$content_below = get_post_meta( $person_id, '_sl_bottom_content', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Modifies columns in Admin tables
		add_action( 'manage_sl_staff_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_staff_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-sl_staff_sortable_columns', [ $this, 'sortable_columns' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		// Add custom filters in Admin table
		add_action( 'restrict_manage_posts', [ $this, 'custom_admin_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );
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

		if ( 'sl_staff' === $post_type ) {

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
		if ( 'edit.php' === $pagenow && 'sl_staff' === $post_type && ! empty( $_GET['_sl_current_team'] ) ) {
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
				'id'           => 'sl_staff_metabox',
				'title'        => esc_html__( 'Staff Data', 'sports-leagues' ),
				'object_types' => [ 'sl_staff' ],
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
				'before_row' => '<div id="anwp-tabs-general-sl_staff_metabox" class="anwp-metabox-tabs__content-item">',
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

		// Job Title
		$cmb->add_field(
			[
				'name' => esc_html__( 'Job Title', 'sports-leagues' ),
				'id'   => $prefix . 'job_title',
				'type' => 'text',
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

		// Date of Birth
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Date of Birth', 'sports-leagues' ),
				'id'          => $prefix . 'date_of_birth',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
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

		$cmb->add_field(
			[
				'name'        => esc_html__( 'External ID', 'sports-leagues' ),
				'id'          => $prefix . 'staff_external_id',
				'type'        => 'text',
				'description' => esc_html__( 'Used on Data Import', 'sports-leagues' ),
				'after_row'   => '</div><div id="anwp-tabs-history-sl_staff_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| History
		|--------------------------------------------------------------------------
		*/
		$group_field_id = $cmb->add_field(
			[
				'id'      => $prefix . 'staff_history_metabox_group',
				'type'    => 'group',
				'options' => [
					'group_title'   => __( 'Entry {#}', 'sports-leagues' ),
					'add_button'    => __( 'Add Another Entry', 'sports-leagues' ),
					'remove_button' => __( 'Remove Entry', 'sports-leagues' ),
					'sortable'      => true,
				],
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name' => esc_html__( 'Job Title', 'sports-leagues' ),
				'id'   => 'job',
				'type' => 'text',
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'        => esc_html__( 'From', 'sports-leagues' ),
				'id'          => 'from',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'        => esc_html__( 'To', 'sports-leagues' ),
				'id'          => 'to',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'             => esc_html__( 'Team', 'sports-leagues' ),
				'id'               => 'team',
				'type'             => 'select',
				'show_option_none' => esc_html__( '- not selected -', 'sports-leagues' ),
				'options_cb'       => [ $this->plugin->team, 'get_team_options' ],
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Staff BIO
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Bio', 'sports-leagues' ),
				'id'         => $prefix . 'bio',
				'type'       => 'wysiwyg',
				'options'    => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'sl_staff_bio_input',
					'textarea_rows' => 10,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names' => false,
				'after_row'  => '</div>',
				'before_row' => '</div><div id="anwp-tabs-bio-sl_staff_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		// Custom Fields
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Custom Fields', 'sports-leagues' ),
				'id'          => $prefix . 'custom_fields',
				'type'        => 'anwp_custom_fields',
				'option_slug' => 'staff_custom_fields',
				'after_row'   => '</div>',
				'before_row'  => '<div id="anwp-tabs-custom_fields-sl_staff_metabox" class="anwp-metabox-tabs__content-item d-none">',
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
				'before_row' => '<div id="anwp-tabs-bottom_content-sl_staff_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.14
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/staff', [] );

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
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-general-sl_staff_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-person"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'General', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-history-sl_staff_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-history"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Job History', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bio-sl_staff_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-note"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bio', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-custom_fields-sl_staff_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-server"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Custom Fields', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bottom_content-sl_staff_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-repo-push"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bottom Content', 'sports-leagues' ); ?></span>
					</div>
					<?php
					/**
					 * Fires in the bottom of staff tabs.
					 *
					 * @since 0.5.14
					 */
					do_action( 'sports-leagues/cmb2_tabs_control/staff' );
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
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
		// @formatter:on
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

		if ( isset( $post->post_type ) && 'sl_staff' === $post->post_type ) {
			return esc_html__( 'Staff Name', 'sports-leagues' );
		}

		return $title;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'staff_id' => 'ID' ] );
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.5.14
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {

		// Add new columns
		$new_columns = [
			'sl_staff_photo'        => esc_html__( 'Photo', 'sports-leagues' ),
			'sl_staff_current_team' => esc_html__( 'Current Team', 'sports-leagues' ),
			'sl_staff_job'          => esc_html__( 'Job', 'sports-leagues' ),
			'staff_id'              => esc_html__( 'ID', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'sl_staff_current_team',
			'sl_staff_photo',
			'sl_staff_job',
			'comments',
			'date',
			'staff_id',
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
	 * @since  0.5.14
	 *
	 * @param array   $column_name Column currently being rendered.
	 * @param integer $post_id     ID of post to display column for.
	 */
	public function columns_display( $column_name, $post_id ) {
		switch ( $column_name ) {

			case 'sl_staff_job':
				echo esc_html( get_post_meta( $post_id, '_sl_job', true ) );
				break;

			case 'sl_staff_photo':
				echo get_the_post_thumbnail( $post_id, 'thumbnail' );
				break;

			case 'sl_staff_current_team':
				$team_id       = (int) get_post_meta( $post_id, '_sl_current_team', true );
				$teams_options = $this->plugin->team->get_team_options();

				if ( ! empty( $teams_options[ $team_id ] ) ) {
					echo esc_html( $teams_options[ $team_id ] );
				}

				break;

			case 'staff_id':
				echo (int) $post_id;
				break;
		}
	}

	/**
	 * Get teams players for selected season.
	 *
	 * @param int $team_id
	 * @param int $season_id
	 *
	 * @return array $output_data
	 * @since 0.10.0
	 */
	public function get_team_season_staff( $team_id, $season_id ) {

		$output_data = [];

		// Prepare data
		$team_id   = (int) $team_id;
		$season_id = (int) $season_id;

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

			return array_values( wp_list_pluck( wp_list_filter( $output_data, [ 'type' => 'staff' ] ), 'id' ) );
		}

		return $output_data;
	}

	/**
	 * Method returns staff with id and photo.
	 *
	 * @return array
	 */
	public function get_staff_photo_map() {

		static $output = null;

		if ( null === $output ) {
			$cache_key = 'SL-STAFF-PHOTO-MAP';

			if ( sports_leagues()->cache->get( $cache_key ) ) {
				$output = sports_leagues()->cache->get( $cache_key );

				return $output;
			}

			global $wpdb;

			$output = [];

			$rows = $wpdb->get_results(
				"
			SELECT p.ID, pm2.meta_value as file_url
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key = '_thumbnail_id' )
			LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = pm1.meta_value AND pm2.meta_key = '_wp_attached_file' )
			WHERE p.post_status = 'publish' AND p.post_type = 'sl_staff' AND pm2.meta_value != ''
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
	 * Get all staff from DB.
	 *
	 * @return array
	 */
	private function get_all_staff() {

		$cache_key = 'SL-STAFF-LIST';

		if ( sports_leagues()->cache->get( $cache_key ) ) {
			return sports_leagues()->cache->get( $cache_key );
		}

		global $wpdb;

		$all_staff = $wpdb->get_results(
			"
			SELECT p.ID id, p.post_title pt,
				MAX( CASE WHEN pm.meta_key = '_sl_job_title' THEN pm.meta_value ELSE '' END) as job,
				MAX( CASE WHEN pm.meta_key = '_sl_short_name' THEN pm.meta_value ELSE '' END) as sn,
				MAX( CASE WHEN pm.meta_key = '_sl_nationality' THEN pm.meta_value ELSE '' END) as nat,
				MAX( CASE WHEN pm.meta_key = '_sl_date_of_birth' THEN pm.meta_value ELSE '' END) as dob,
				MAX( CASE WHEN pm.meta_key = '_sl_current_team' THEN pm.meta_value ELSE '' END) as t_id
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = p.ID )
			WHERE p.post_status = 'publish' AND p.post_type = 'sl_staff'
			GROUP BY p.ID
			ORDER BY p.post_title
			",
			OBJECT_K
		);

		if ( empty( $all_staff ) ) {
			return [];
		}

		foreach ( $all_staff as $staff ) {
			if ( $staff->nat ) {
				$countries = maybe_unserialize( $staff->nat );

				if ( ! empty( $countries ) && is_array( $countries ) ) {
					$staff->nat = implode( ',', $countries );
				}
			}
		}

		sports_leagues()->cache->set( $cache_key, $all_staff );

		return $all_staff;
	}

	/**
	 * Get staff list.
	 *
	 * ToDO load cached data
	 * @return array $output_data -
	 */
	public function get_staff_list() {

		global $wpdb;

		$all_staff = $wpdb->get_results(
			"
			SELECT p.ID id, p.post_title title,
				MAX( CASE WHEN pm.meta_key = '_sl_job_title' THEN pm.meta_value ELSE '' END) as job,
				MAX( CASE WHEN pm.meta_key = '_sl_nationality' THEN pm.meta_value ELSE '' END) as nationality,
				MAX( CASE WHEN pm.meta_key = '_sl_date_of_birth' THEN pm.meta_value ELSE '' END) as birthdate,
				MAX( CASE WHEN pm.meta_key = '_sl_current_team' THEN pm.meta_value ELSE '' END) as team_id
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = p.ID )
			WHERE p.post_status = 'publish' AND p.post_type = 'sl_staff'
			GROUP BY p.ID
			ORDER BY p.post_title
			"
		);

		$staff_photos = $this->get_staff_photo_map();

		if ( empty( $all_staff ) ) {
			return [];
		}

		foreach ( $all_staff as $staff ) {

			$staff->id       = absint( $staff->id );
			$staff->team_id  = absint( $staff->team_id );
			$staff->country  = '';
			$staff->country2 = '';
			$staff->type     = 'staff';
			$staff->photo    = '';

			if ( isset( $staff_photos[ $staff->id ] ) ) {
				$staff->photo = $staff_photos[ $staff->id ];
			}

			if ( $staff->birthdate ) {
				$staff->birthdate = date_i18n( get_option( 'date_format' ), strtotime( $staff->birthdate ) );
			}

			if ( $staff->nationality ) {
				$countries = maybe_unserialize( $staff->nationality );

				if ( ! empty( $countries ) && is_array( $countries ) && ! empty( $countries[0] ) ) {
					$staff->country = mb_strtolower( $countries[0] );
				}

				if ( ! empty( $countries ) && is_array( $countries ) && ! empty( $countries[1] ) ) {
					$staff->country2 = mb_strtolower( $countries[1] );
				}
			}

			unset( $staff->nationality );
		}

		return $all_staff;
	}

	/**
	 * Get staff data.
	 *
	 * @param $staff_id
	 *
	 * @return object - Data object.
	 * @since 0.1.0
	 */
	public function get_single_staff( $staff_id ) {

		static $all_staff = null;

		if ( null === $all_staff ) {
			$all_staff = $this->get_all_staff();
		}

		$defaults = (object) [
			'name'        => '',
			'nationality' => [],
			'team_id'     => '',
			'photo'       => '',
			'id'          => '',
			'name_short'  => '',
			'job'         => '',
			'birth_date'  => '',
		];

		$staff_id     = absint( $staff_id );
		$staff_cached = empty( $all_staff[ $staff_id ] ) ? false : $all_staff[ $staff_id ];

		if ( ! $staff_id || empty( $staff_cached ) ) {
			return $defaults;
		}

		// Add photos
		$staff_photos = $this->get_staff_photo_map();

		return (object) [
			'id'          => $staff_cached->id,
			'name'        => $staff_cached->pt,
			'team_id'     => $staff_cached->t_id,
			'name_short'  => $staff_cached->sn ?: $staff_cached->pt,
			'birth_date'  => $staff_cached->dob,
			'job'         => $staff_cached->job,
			'photo'       => empty( $staff_photos[ $staff_cached->id ] ) ? '' : $staff_photos[ $staff_cached->id ],
			'nationality' => explode( ',', $staff_cached->nat ),
		];
	}

	/**
	 * Get staff.
	 *
	 * @param array $args
	 *
	 * @return array $output_data -
	 * @since 0.5.14
	 */
	public function get_staff_by_args( $args ) {
		$output_data = [];

		$personal = get_posts( $args );

		/** @var WP_Post $person */
		foreach ( $personal as $person ) {
			$person_obj              = (object) [];
			$person_obj->id          = $person->ID;
			$person_obj->title       = $person->post_title;
			$person_obj->type        = 'staff';
			$person_obj->job         = get_post_meta( $person->ID, '_sl_job_title', true );
			$person_obj->nationality = get_post_meta( $person->ID, '_sl_nationality', true );
			$person_obj->birthdate   = get_post_meta( $person->ID, '_sl_date_of_birth', true );

			// Format date
			if ( $person_obj->birthdate ) {
				$person_obj->birthdate = date_i18n( get_option( 'date_format' ), strtotime( $person_obj->birthdate ) );
			}

			$output_data[] = $person_obj;
		}

		return $output_data;
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.5.14
	 */
	public function add_rest_routes() {
		register_rest_route(
			'sports-leagues/v1',
			'/search-personal/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_personal_search' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'sports-leagues/v1',
			'/staff-list/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_staff_list_rest' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
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
	public function get_staff_list_rest( WP_REST_Request $request ) {
		return rest_ensure_response( $this->get_staff_list() );
	}

	/**
	 * Get team players for REST search request.
	 *
	 * @return array $output_data
	 * @since 0.5.14
	 */
	public function get_personal_search() {

		// phpcs:ignore WordPress.Security.NonceVerification
		$search = sanitize_text_field( $_GET['search'] );

		$args = [
			's'           => $search,
			'numberposts' => 50,
			'post_type'   => [ 'sl_staff' ],
			'orderby'     => 'title',
		];

		return $this->get_staff_by_args( $args );
	}

	/**
	 * Get array of staff's games
	 *
	 * @param object|array $options
	 * @param string $result
	 *
	 * @since 0.10.2
	 * @return array|null|object
	 */
	public function get_staff_games( $options, $result = '' ) {

		global $wpdb;

		$options = (object) wp_parse_args(
			$options,
			[
				'staff_id'      => '',
				'tournament_id' => '',
				'season_id'     => '',
				'stage_id'      => '',
				'league_id'     => '',
				'finished'      => '',
				'sort_by_date'  => '',
			]
		);

		if ( ! absint( $options->staff_id ) ) {
			return [];
		}

		$cache_key = 'SL-STAFF_get_staff_games__' . md5( maybe_serialize( $options ) );

		if ( sports_leagues()->cache->get( $cache_key, 'sl_game' ) ) {
			return sports_leagues()->cache->get( $cache_key, 'sl_game' );
		}

		$query = "
		SELECT g.*, pm1.meta_value as staff_home, pm2.meta_value as staff_away
		FROM {$wpdb->prefix}sl_games g
		LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = g.game_id AND pm1.meta_key = '_sl_staff_home' )
		LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = g.game_id AND pm2.meta_key = '_sl_staff_away' )
		";

		//                                      "staff_id"                             OR "staff_id,%"                                                 OR "%,staff_id"                                                 OR "%,staff_id,%"
		$query .= ' WHERE ( pm1.meta_value LIKE "' . absint( $options->staff_id ) . '" OR pm1.meta_value LIKE "' . absint( $options->staff_id ) . ',%" OR pm1.meta_value LIKE "%,' . absint( $options->staff_id ) . '" OR pm1.meta_value LIKE "%,' . absint( $options->staff_id ) . ',%" ';
		$query .= ' OR      pm2.meta_value LIKE "' . absint( $options->staff_id ) . '" OR pm2.meta_value LIKE "' . absint( $options->staff_id ) . ',%" OR pm2.meta_value LIKE "%,' . absint( $options->staff_id ) . '" OR pm2.meta_value LIKE "%,' . absint( $options->staff_id ) . ',%" ) ';

		/*
		|--------------------------------------------------------------------
		| WHERE filter by tournament_id
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->tournament_id ) ) {
			$query .= $wpdb->prepare( ' AND g.tournament_id = %d ', $options->tournament_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by stage_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->stage_id ) {
			$query .= $wpdb->prepare( ' AND g.stage_id = %d ', $options->stage_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by season_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->season_id ) {
			$query .= $wpdb->prepare( ' AND g.season_id = %d ', $options->season_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by league_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->league_id ) {
			$query .= $wpdb->prepare( ' AND g.league_id = %d ', $options->league_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by finished
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options->finished ) {
			$query .= $wpdb->prepare( ' AND g.finished = %d ', $options->finished );
		}

		/*
		|--------------------------------------------------------------------
		| ORDER BY date
		|--------------------------------------------------------------------
		*/
		if ( 'asc' === $options->sort_by_date ) {
			$query .= ' ORDER BY CASE WHEN g.kickoff = "0000-00-00 00:00:00" THEN 1 ELSE 0 END, g.kickoff ASC';
		} elseif ( 'desc' === $options->sort_by_date ) {
			$query .= ' ORDER BY CASE WHEN g.kickoff = "0000-00-00 00:00:00" THEN 1 ELSE 0 END, g.kickoff DESC';
		}

		$games = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( 'stats' === $result ) {
			return $games;
		}

		$ids = wp_list_pluck( $games, 'game_id' );

		if ( 'ids' === $result ) {
			return $ids;
		}

		$game_posts = sports_leagues()->game->get_permalinks_by_ids( $ids );

		foreach ( $games as $game ) {
			$game->permalink = isset( $game_posts[ $game->game_id ] ) ? $game_posts[ $game->game_id ] : get_permalink( $game->game_id );
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $games ) ) {
			sports_leagues()->cache->set( $cache_key, $games, 'sl_game' );
		}

		return $games;
	}

	/**
	 * Get Staff games grouped by role
	 *
	 * @return array
	 * @since 0.10.2
	 */
	public function get_staff_games_grouped( $games, $staff_id ) {

		$output = [];

		foreach ( $games as $game ) {

			$last_group = '_';

			/*
			|--------------------------------------------------------------------
			| Home Staff
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $game->staff_home ) ) {
				foreach ( explode( ',', $game->staff_home ) as $staff_item ) {

					if ( '_' === mb_substr( $staff_item, 0, 1 ) ) {
						$last_group = $staff_item;
					}

					if ( absint( $staff_item ) && absint( $staff_item ) === absint( $staff_id ) ) {

						if ( ! isset( $output[ $last_group ] ) ) {
							$output[ $last_group ] = [];
						}

						if ( ! isset( $output[ $last_group ][ $game->home_team ] ) ) {
							$output[ $last_group ][ $game->home_team ] = [];
						}

						$output[ $last_group ][ $game->home_team ][] = $game;
					}
				}
			}

			$last_group = '_';

			/*
			|--------------------------------------------------------------------
			| Away Staff
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $game->staff_away ) ) {
				foreach ( explode( ',', $game->staff_away ) as $staff_item ) {

					if ( '_' === mb_substr( $staff_item, 0, 1 ) ) {
						$last_group = $staff_item;
					}

					if ( absint( $staff_item ) && absint( $staff_item ) === absint( $staff_id ) ) {

						if ( ! isset( $output[ $last_group ] ) ) {
							$output[ $last_group ] = [];
						}

						if ( ! isset( $output[ $last_group ][ $game->away_team ] ) ) {
							$output[ $last_group ][ $game->away_team ] = [];
						}

						$output[ $last_group ][ $game->away_team ][] = $game;
					}
				}
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
	 * @since 0.10.3
	 */
	public function get_staff_id_by_external_id( $external_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_sl_staff_external_id' AND meta_value = %s
				",
				$external_id
			)
		);
	}
}
