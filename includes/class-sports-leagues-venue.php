<?php
/**
 * Sports Leagues :: Venue.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Venue {

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
		$permalink_slug      = empty( $permalink_structure['venue'] ) ? 'venue' : $permalink_structure['venue'];

		// Register this CPT.
		$labels = [
			'name'                  => _x( 'Venues', 'Post type general name', 'sports-leagues' ),
			'singular_name'         => _x( 'Venue', 'Post type singular name', 'sports-leagues' ),
			'menu_name'             => _x( 'Venues', 'Admin Menu text', 'sports-leagues' ),
			'name_admin_bar'        => _x( 'Venue', 'Add New on Toolbar', 'sports-leagues' ),
			'add_new'               => __( 'Add New', 'sports-leagues' ),
			'add_new_item'          => __( 'Add New Venue', 'sports-leagues' ),
			'new_item'              => __( 'New Venue', 'sports-leagues' ),
			'edit_item'             => __( 'Edit Venue', 'sports-leagues' ),
			'view_item'             => __( 'View Venue', 'sports-leagues' ),
			'all_items'             => __( 'Venues', 'sports-leagues' ),
			'search_items'          => __( 'Search Venues', 'sports-leagues' ),
			'not_found'             => __( 'No Venues found.', 'sports-leagues' ),
			'not_found_in_trash'    => __( 'No Venues found in Trash.', 'sports-leagues' ),
			'featured_image'        => __( 'Venue photo', 'sports-leagues' ),
			'set_featured_image'    => __( 'Set Venue photo', 'sports-leagues' ),
			'remove_featured_image' => __( 'Remove Venue photo', 'sports-leagues' ),
			'use_featured_image'    => __( 'Use as Venue photo', 'sports-leagues' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=sl_team',
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

		register_post_type( 'sl_venue', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Create CMB2 metabox
		add_action( 'cmb2_before_post_form_sl_venue_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_post_form_sl_venue_metabox', [ $this, 'cmb2_after_metabox' ] );
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );

		// Render Custom Content below
		add_action(
			'sports-leagues/tmpl-venue/after_wrapper',
			function ( $venue ) {

				$content_below = get_post_meta( $venue->ID, '_sl_bottom_content', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Filters the title field placeholder text.
		add_filter( 'enter_title_here', [ $this, 'title' ], 10, 2 );

		// Modifies columns in Admin tables
		add_action( 'manage_sl_venue_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-sl_venue_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-sl_venue_sortable_columns', [ $this, 'sortable_columns' ] );
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

		if ( isset( $post->post_type ) && 'sl_venue' === $post->post_type ) {
			return esc_html__( 'Venue Name', 'sports-leagues' );
		}

		return $title;
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
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-general-sl_venue_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-info"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'General', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-info-sl_venue_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-note"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Info', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-custom_fields-sl_venue_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-server"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Custom Fields', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-gallery-sl_venue_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-device-camera"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Gallery', 'sports-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bottom_content-sl_venue_metabox">
						<svg class="anwp-icon d-inline-block"><use xlink:href="#icon-repo-push"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bottom Content', 'sports-leagues' ); ?></span>
					</div>
					<?php
					/**
					 * Fires in the bottom of venue tabs.
					 *
					 * @since 0.1.0
					 */
					do_action( 'sports-leagues/cmb2_tabs_control/venue' );
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
		 * Initiate separate description metabox
		 */
		$cmb = new_cmb2_box(
			[
				'id'           => 'sl_venue_metabox',
				'title'        => esc_html__( 'Venue Data', 'sports-leagues' ),
				'object_types' => [ 'sl_venue' ],
				'context'      => 'normal',
				'priority'     => 'high',
				'classes'      => [ 'anwp-b-wrap' ],
				'show_names'   => true,
			]
		);

		// Teams
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Teams', 'sports-leagues' ),
				'id'         => $prefix . 'teams',
				'type'       => 'anwp_sl_multiselect',
				'options_cb' => [ $this->plugin->team, 'get_team_options' ],
				'before_row' => '<div id="anwp-tabs-general-sl_venue_metabox" class="anwp-metabox-tabs__content-item">',
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

		// City
		$cmb->add_field(
			[
				'name' => esc_html__( 'City', 'sports-leagues' ),
				'id'   => $prefix . 'city',
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

		// Capacity
		$cmb->add_field(
			[
				'name' => esc_html__( 'Capacity', 'sports-leagues' ),
				'id'   => $prefix . 'capacity',
				'type' => 'text',
			]
		);

		// Opened
		$cmb->add_field(
			[
				'name' => esc_html__( 'Opened', 'sports-leagues' ),
				'id'   => $prefix . 'opened',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name'        => esc_html__( 'External ID', 'sports-leagues' ),
				'id'          => $prefix . 'venue_external_id',
				'type'        => 'text',
				'description' => esc_html__( 'Used on Data Import', 'sports-leagues' ),
			]
		);

		// Map
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Map', 'sports-leagues' ),
				'id'         => $prefix . 'map',
				'type'       => 'anwp_map',
				'show_names' => false,
				'attributes' => [
					'readonly' => 'readonly',
				],
				'after_row'  => '</div>',
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
					'textarea_name' => 'sl_venue_description_input',
					'textarea_rows' => 10,
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				],
				'show_names' => false,
				'after_row'  => '</div>',
				'before_row' => '<div id="anwp-tabs-info-sl_venue_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		// Custom Fields
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Custom Fields', 'sports-leagues' ),
				'id'          => $prefix . 'custom_fields',
				'type'        => 'anwp_custom_fields',
				'option_slug' => 'venue_custom_fields',
				'after_row'   => '</div>',
				'classes'     => [ 'pt-2' ],
				'before_row'  => '<div id="anwp-tabs-custom_fields-sl_venue_metabox" class="anwp-metabox-tabs__content-item d-none">' . $this->custom_field_tips(),
			]
		);

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
				'before_row'   => '<div id="anwp-tabs-gallery-sl_venue_metabox" class="anwp-metabox-tabs__content-item d-none">',
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
				'before_row' => '<div id="anwp-tabs-bottom_content-sl_venue_metabox" class="anwp-metabox-tabs__content-item d-none">',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.5.12
		 */
		$extra_fields = apply_filters( 'sports-leagues/cmb2_tabs_content/venue', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	protected function custom_field_tips() {
		ob_start();
		?>

		<div class="d-flex align-items-center p-1 anwp-small anwp-subtle-info-bg mt-2">
			<svg class="anwp-icon anwp-icon--s14 mr-1"><use xlink:href="#icon-info"></use></svg>

			<span class="d-inline-block">
				Setup Custom Fields in Sports Leagues Settings
			</span>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get Venue options
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_venue_options() {

		static $options = null;

		if ( null === $options ) {

			$options = [];

			global $wpdb;

			$rows = $wpdb->get_results(
				"
					SELECT ID, post_title
					FROM $wpdb->posts
					WHERE post_status = 'publish' AND post_type = 'sl_venue'
					ORDER BY post_title
				"
			);

			foreach ( $rows as $venue ) {
				$options[ $venue->ID ] = $venue->post_title;
			}
		}

		return $options;
	}

	/**
	 * Get Venue name by ID
	 *
	 * @return string
	 * @since 0.10.0
	 */
	public function get_venue_title_by_id( $venue_id ) {

		$venue_options = $this->get_venue_options();

		return isset( $venue_options[ $venue_id ] ) ? $venue_options[ $venue_id ] : '';
	}

	/**
	 * Get Venue obj options
	 *
	 * @since 0.9.2
	 * @return array
	 */
	public function get_venue_obj_options() {

		static $options = null;

		if ( null === $options ) {

			foreach ( $this->get_venue_options() as $venue_id => $venue_title ) {
				$options[] = (object) [
					'id'    => $venue_id,
					'title' => $venue_title,
				];
			}
		}

		return $options;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'sl_venue_id' => 'ID' ] );
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
			'sl_city'     => esc_html__( 'City', 'sports-leagues' ),
			'sl_venue_id' => esc_html__( 'ID', 'sports-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [ 'cb', 'title', 'sl_city', 'comments', 'date', 'sl_venue_id' ];
		$new_columns       = [];

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
	 * @param array   $column   Column currently being rendered.
	 * @param integer $post_id  ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {
			case 'sl_city':
				echo esc_html( get_post_meta( $post_id, '_sl_city', true ) );
				break;

			case 'sl_venue_id':
				echo absint( $post_id );
				break;
		}
	}

	/**
	 * Get Post ID by External id
	 *
	 * @param $external_id
	 *
	 * @return string|null
	 * @since 0.10.3
	 */
	public function get_venue_id_by_external_id( $external_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_sl_venue_external_id' AND meta_value = %s
				",
				$external_id
			)
		);
	}
}
